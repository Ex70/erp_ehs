<?php

namespace App\Services\Solvencias;

use App\Models\SolvenciaDocumento;
use App\Models\SolvenciaExpediente as Expediente;
use App\Models\SolvenciaFolio;
use App\Models\SolvenciaMovimiento as Bitacora;
use App\Models\User;
use App\Notifications\SolvenciaNotificacion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Máquina de estados de la solvencia (cadena de custodia).
 *
 * Cada acción:
 *  1. bloquea el expediente (lockForUpdate) dentro de una transacción,
 *  2. valida la regla de custodia (quién la tiene, si acusó, etapa, checklist),
 *  3. cambia el estado y agrega los movimientos a la bitácora,
 *  4. al confirmar la transacción, notifica a las personas según el flujo.
 */
class ExpedienteService
{
    /* ═════════════════ Creación y datos ═════════════════ */

    public function crear(User $user, array $datos): Expediente
    {
        $flujo = FlujoService::flujosElaborables($user)->firstWhere('id', (int) ($datos['flujo_id'] ?? 0));

        if (! $flujo) {
            throw new AccionNoPermitida('No tienes asignada la elaboración en ese flujo, o el flujo tiene etapas sin persona asignada.');
        }

        return DB::transaction(function () use ($user, $datos, $flujo) {
            $recorrido = $flujo->recorrido();

            $participantes = collect($recorrido)
                ->pluck('personas')
                ->flatten()
                ->push($user->id)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $expediente = Expediente::create([
                'folio'           => SolvenciaFolio::siguiente($flujo->prefijo_folio),
                'flujo_id'        => $flujo->id,
                'departamento_id' => $flujo->departamento_id,
                'tipo_proveedor'  => $datos['tipo_proveedor'],
                'proveedor'       => trim($datos['proveedor']),
                'monto'           => $datos['monto'],
                'descripcion'     => trim($datos['descripcion']),
                'recorrido'       => $recorrido,
                'participantes'   => $participantes,
                'etapa'           => 0,
                'estado'          => Expediente::BORRADOR,
                'holder_id'       => $user->id,
                'acuse'           => true,
                'desde'           => now(),
                'creador_id'      => $user->id,
            ]);

            Bitacora::registrar($expediente, $user, 'Expediente creado', implode("\n", [
                "Folio {$expediente->folio} asignado automáticamente ({$flujo->nombre}).",
                'Proveedor ' . ($expediente->tipo_proveedor === 'nuevo' ? 'NUEVO' : 'validado') . ": {$expediente->proveedor}",
                'Monto a pagar: ' . $this->dinero($expediente->monto),
                $expediente->descripcion,
            ]), 'crea', 0);

            return $expediente;
        });
    }

    public function corregirDatos(Expediente $expediente, User $user, array $datos): int
    {
        return DB::transaction(function () use ($expediente, $user, $datos) {
            $e = $this->bloquear($expediente);
            $this->exigirEditable($e, $user);

            $etiquetas = [
                'tipo_proveedor' => 'Tipo de proveedor',
                'proveedor'      => 'Proveedor',
                'monto'          => 'Monto a pagar',
                'descripcion'    => 'Descripción',
            ];

            $cambios = [];
            foreach ($etiquetas as $campo => $etiqueta) {
                $anterior = $e->{$campo};
                $nuevo    = $campo === 'monto' ? number_format((float) $datos[$campo], 2, '.', '') : trim((string) $datos[$campo]);
                $compara  = $campo === 'monto' ? number_format((float) $anterior, 2, '.', '') : (string) $anterior;

                if ($compara !== $nuevo) {
                    $ver = fn ($v) => match ($campo) {
                        'monto'          => $this->dinero($v),
                        'tipo_proveedor' => $v === 'nuevo' ? 'Nuevo' : 'Validado',
                        default          => $v,
                    };
                    $cambios[] = "{$etiqueta}: «{$ver($anterior)}» → «{$ver($nuevo)}»";
                    $e->{$campo} = $nuevo;
                }
            }

            if ($cambios) {
                $e->save();
                Bitacora::registrar($e, $user, 'Datos corregidos', implode("\n", $cambios), 'edit');
            }

            return count($cambios);
        });
    }

    /* ═════════════════ Documentos ═════════════════ */

    public function cargarDocumento(Expediente $expediente, User $user, string $tipo, UploadedFile $archivo): SolvenciaDocumento
    {
        $ruta = null;

        try {
            return DB::transaction(function () use ($expediente, $user, $tipo, $archivo, &$ruta) {
                $e = $this->bloquear($expediente);
                $this->exigirEditable($e, $user);

                if (! in_array($tipo, $e->requeridos(), true)) {
                    throw new AccionNoPermitida('Ese documento no forma parte del checklist de este expediente.');
                }

                $ruta = $this->guardarArchivo($e, $archivo);

                return $this->registrarDocumento($e, $user, $tipo, $archivo, $ruta);
            });
        } catch (Throwable $ex) {
            $this->borrarArchivo($ruta);
            throw $ex;
        }
    }

    public function exceptuarDocumento(Expediente $expediente, User $user, string $tipo, string $observacion): void
    {
        DB::transaction(function () use ($expediente, $user, $tipo, $observacion) {
            $e = $this->bloquear($expediente);
            $this->exigirEditable($e, $user);

            $cfg = config("solvencias.documentos.{$tipo}");

            if (! $cfg || ! in_array($tipo, $e->requeridos(), true)) {
                throw new AccionNoPermitida('Ese documento no forma parte del checklist de este expediente.');
            }
            if (empty($cfg['exceptuable'])) {
                throw new AccionNoPermitida("«{$cfg['nombre']}» es obligatorio y no puede quedar sin anexar.");
            }

            $observacion = trim($observacion);
            $minimo      = (int) config('solvencias.min_observacion', 20);

            if (mb_strlen($observacion) < $minimo) {
                throw new AccionNoPermitida("La observación debe tener al menos {$minimo} caracteres y justificar por qué no se anexa.");
            }

            $previo = $e->documentoActivo($tipo);
            if ($previo?->exento) {
                throw new AccionNoPermitida('Este documento ya está registrado sin archivo.');
            }
            $previo?->update(['reemplazado' => true]);

            SolvenciaDocumento::create([
                'expediente_id' => $e->id,
                'tipo'          => $tipo,
                'archivo'       => null,
                'nombre'        => '(sin archivo)',
                'tamano'        => 0,
                'exento'        => true,
                'observacion'   => $observacion,
                'user_id'       => $user->id,
            ]);

            $detalle = $cfg['nombre'];
            if ($previo) {
                $detalle .= " — se retira de la vista el archivo {$previo->nombre}, que queda conservado";
            }

            Bitacora::registrar($e, $user, 'Documento no anexado (con observación)', $detalle . "\nObservación: {$observacion}", 'exc');
        });
    }

    /* ═════════════════ Custodia ═════════════════ */

    public function enviar(Expediente $expediente, User $user, int $paraId, ?string $comentario = null): User
    {
        [$para, $avisos, $e] = DB::transaction(function () use ($expediente, $user, $paraId, $comentario) {
            $e = $this->bloquear($expediente);

            $this->exigirPoseedor($e, $user);

            if ($e->esUltimaEtapa()) {
                throw new AccionNoPermitida('En esta etapa corresponde autorizar, no enviar.');
            }

            $this->exigirChecklist($e, 'Envío bloqueado');

            if (! in_array($paraId, array_map('intval', $e->candidatos()), true)) {
                throw new AccionNoPermitida('La persona elegida no corresponde a la siguiente etapa del flujo.');
            }

            $para = User::where('activo', true)->find($paraId)
                ?? throw new AccionNoPermitida('La persona elegida no está activa en el ERP. Ajusta el flujo en «Personas y flujos».');

            $comentario = trim((string) $comentario);
            $destino    = $e->siguienteEtapaIdx();
            $verboDest  = $e->recorrido[$destino]['verbo'];

            if ($e->esDevuelta()) {
                Bitacora::registrar($e, $user, '✓ Corrigió y reenvió', $comentario, 'ok', 0);
            } else {
                Bitacora::registrar($e, $user, '✓ ' . $this->pasado($e->verboActual()), $comentario, 'ok', $e->etapa);
            }

            $e->fill([
                'etapa'           => $destino,
                'holder_id'       => $para->id,
                'acuse'           => false,
                'estado'          => Expediente::SIN_ACUSE,
                'enviado_por_id'  => $user->id,
                'enviado_en'      => now(),
                'desde'           => now(),
                'recordatorios'   => 0,
                'devuelta_desde'  => null,
                'devuelta_por_id' => null,
            ])->save();

            Bitacora::registrar($e, $user, 'Enviada — sin acuse',
                "Para: {$para->name}, quien " . Str::lower($verboDest) . ".\nQueda a su nombre hasta que acuse de recibido.", 'envio');

            $avisos = [[
                'enviada', [$para->id],
                "{$user->name} te entregó la solvencia {$e->folio} ({$this->dinero($e->monto)} · {$e->proveedor}). "
                . 'Te toca: ' . Str::lower($verboDest) . '. Acusa de recibido para tomarla.'
                . ($comentario !== '' ? " Comentario: {$comentario}" : ''),
            ]];

            return [$para, $avisos, $e];
        });

        $this->notificar($e, $user, $avisos);

        return $para;
    }

    public function acusar(Expediente $expediente, User $user): void
    {
        [$avisos, $e] = DB::transaction(function () use ($expediente, $user) {
            $e = $this->bloquear($expediente);

            if ((int) $e->holder_id !== (int) $user->id) {
                throw new AccionNoPermitida('Solo la persona a cuyo nombre está la solvencia puede acusar de recibido.');
            }
            if ($e->acuse) {
                throw new AccionNoPermitida('Esta solvencia ya fue acusada de recibido.');
            }
            if ($e->esFinal()) {
                throw new AccionNoPermitida('La solvencia ya está cerrada.');
            }

            $tiempo = $e->enviado_en ? ' Tiempo sin acuse: ' . Expediente::duracion($e->enviado_en) . '.' : '';
            $recs   = $e->recordatorios ? " Se enviaron {$e->recordatorios} recordatorio(s)." : '';

            $e->acuse = true;
            $e->desde = now();
            if (! $e->esDevuelta()) {
                $e->estado = Expediente::EN_PODER;
            }
            $e->save();

            Bitacora::registrar($e, $user, 'Acuse de recibido',
                'Recibida de ' . $e->nombreDe($e->enviado_por_id) . ".{$tiempo}{$recs}", 'acuse');

            return [[[
                'acuse', [$e->enviado_por_id],
                "{$user->name} acusó de recibido la solvencia {$e->folio}; ya está en su poder.",
            ]], $e];
        });

        $this->notificar($e, $user, $avisos);
    }

    public function recordar(Expediente $expediente, User $user): void
    {
        [$avisos, $e] = DB::transaction(function () use ($expediente, $user) {
            $e = $this->bloquear($expediente);

            if ((int) $e->enviado_por_id !== (int) $user->id || $e->acuse || $e->esFinal()
                || (int) $e->holder_id === (int) $user->id) {
                throw new AccionNoPermitida('Solo quien envió la solvencia puede recordar, y solo mientras siga sin acuse.');
            }

            $e->increment('recordatorios');

            Bitacora::registrar($e, $user, "Recordatorio #{$e->recordatorios} enviado",
                'A: ' . $e->nombreDe($e->holder_id) . '. Pendiente de acuse desde ' . Expediente::fecha($e->enviado_en) . '.', 'rec');

            return [[[
                'recordatorio', [$e->holder_id],
                "{$user->name} te recuerda acusar de recibido la solvencia {$e->folio}, pendiente desde "
                . Expediente::fecha($e->enviado_en) . '.',
            ]], $e];
        });

        $this->notificar($e, $user, $avisos);
    }

    public function devolver(Expediente $expediente, User $user, string $motivo): void
    {
        [$avisos, $e] = DB::transaction(function () use ($expediente, $user, $motivo) {
            $e = $this->bloquear($expediente);

            $this->exigirPoseedor($e, $user);

            if ($e->etapa === 0) {
                throw new AccionNoPermitida('La solvencia está en elaboración; no hay a quién devolverla.');
            }

            $motivo = $this->exigirMotivo($motivo);

            Bitacora::registrar($e, $user, 'Devuelta (no ' . Str::lower($this->pasado($e->verboActual())) . ')',
                "Motivo: {$motivo}\nRegresa a " . $e->nombreDe($e->creador_id) . ' para corrección.', 'dev', $e->etapa);

            $e->fill([
                'devuelta_desde'  => $e->etapa,
                'devuelta_por_id' => $user->id,
                'etapa'           => 0,
                'estado'          => Expediente::DEVUELTA,
                'holder_id'       => $e->creador_id,
                'acuse'           => false,
                'enviado_por_id'  => $user->id,
                'enviado_en'      => now(),
                'desde'           => now(),
                'recordatorios'   => 0,
            ])->save();

            return [[[
                'devuelta', [$e->creador_id],
                "{$user->name} devolvió la solvencia {$e->folio} para corrección. Motivo: {$motivo}",
            ]], $e];
        });

        $this->notificar($e, $user, $avisos);
    }

    public function cancelar(Expediente $expediente, User $user, string $motivo): void
    {
        [$avisos, $e] = DB::transaction(function () use ($expediente, $user, $motivo) {
            $e = $this->bloquear($expediente);

            if ($e->esFinal()) {
                throw new AccionNoPermitida('La solvencia ya está cerrada.');
            }
            if (! $this->puedeCancelar($e, $user)) {
                throw new AccionNoPermitida('Solo quien la elabora (en su etapa), quienes validan o autorizan en este flujo, o un administrador pueden cancelar.');
            }

            $motivo = $this->exigirMotivo($motivo);
            $ultimo = $e->holder_id;

            $e->fill([
                'estado'       => Expediente::CANCELADA,
                'cancelada_en' => now(),
                'desde'        => now(),
            ])->save();

            Bitacora::registrar($e, $user, 'Cancelada',
                "Motivo: {$motivo}\nÚltimo en poder: " . $e->nombreDe($ultimo) . '.', 'can');

            return [[[
                'cancelada', [$e->creador_id, $ultimo],
                "{$user->name} canceló la solvencia {$e->folio}. Motivo: {$motivo}",
            ]], $e];
        });

        $this->notificar($e, $user, $avisos);
    }

    public function autorizar(Expediente $expediente, User $user, ?string $comentario = null): void
    {
        [$avisos, $e] = DB::transaction(function () use ($expediente, $user, $comentario) {
            $e = $this->bloquear($expediente);

            $this->exigirPoseedor($e, $user);

            if (! $e->esUltimaEtapa()) {
                throw new AccionNoPermitida('Esta etapa no es la de autorización.');
            }

            $this->exigirChecklist($e, 'No se puede autorizar');

            Bitacora::registrar($e, $user, '✓ ' . $this->pasado($e->verboActual()), trim((string) $comentario), 'ok', $e->etapa);

            $e->fill([
                'estado'        => Expediente::AUTORIZADA,
                'desde'         => now(),
                'autorizada_en' => now(),
            ])->save();

            Bitacora::registrar($e, $user, 'Autorizada · pasa a Contabilidad y Finanzas para pago',
                'Contabilidad y Finanzas consulta el formato de solvencia original y, al pagar, adjunta el comprobante y la fecha de pago.', 'fin');

            $mensaje = "{$user->name} autorizó la solvencia {$e->folio}: {$this->dinero($e->monto)} a {$e->proveedor}. Lista para pago.";

            return [[
                ['autorizada', FlujoService::idsPagadores(), $mensaje],
                ['autorizada', [$e->creador_id], $mensaje],
            ], $e];
        });

        $this->notificar($e, $user, $avisos);
    }

    public function pagar(Expediente $expediente, User $user, UploadedFile $comprobante, string $fecha): void
    {
        $ruta = null;

        try {
            [$avisos, $e] = DB::transaction(function () use ($expediente, $user, $comprobante, $fecha, &$ruta) {
                $e = $this->bloquear($expediente);

                if (! FlujoService::esPagador($user)) {
                    throw new AccionNoPermitida('Solo Contabilidad y Finanzas registra pagos.');
                }
                if ($e->estado !== Expediente::AUTORIZADA) {
                    throw new AccionNoPermitida('Solo se registra el pago de solvencias autorizadas pendientes de pago.');
                }

                $ruta = $this->guardarArchivo($e, $comprobante);
                $doc  = $this->registrarDocumento($e, $user, 'comprobante', $comprobante, $ruta);

                $e->fill([
                    'pago_fecha'  => $fecha,
                    'pago_por_id' => $user->id,
                    'pago_en'     => now(),
                    'estado'      => Expediente::PAGADA,
                    'holder_id'   => $user->id,
                    'desde'       => now(),
                ])->save();

                $fechaTxt = Carbon::parse($fecha)->locale('es')->translatedFormat('d M Y');

                Bitacora::registrar($e, $user, 'Pago registrado · solicitud finalizada', implode("\n", [
                    "Fecha de pago: {$fechaTxt}",
                    "Comprobante: {$doc->nombre}",
                    'Monto: ' . $this->dinero($e->monto) . " a {$e->proveedor}",
                ]), 'pago');

                return [[[
                    'pagada', [$e->creador_id],
                    "Contabilidad y Finanzas registró el pago de la solvencia {$e->folio} el {$fechaTxt}. Solicitud finalizada.",
                ]], $e];
            });
        } catch (Throwable $ex) {
            $this->borrarArchivo($ruta);
            throw $ex;
        }

        $this->notificar($e, $user, $avisos);
    }

    /* ═════════════════ Reglas compartidas ═════════════════ */

    public function puedeCancelar(Expediente $e, User $user): bool
    {
        if ($e->esFinal()) {
            return false;
        }

        $elaboraYLaTiene = (int) $e->holder_id === (int) $user->id && $e->acuse && $e->etapa === 0;

        return $elaboraYLaTiene || $e->esAutoridad($user) || $user->can('solvencias_flujo.cancelar');
    }

    private function bloquear(Expediente $expediente): Expediente
    {
        return Expediente::with('documentos')->lockForUpdate()->findOrFail($expediente->id);
    }

    private function exigirPoseedor(Expediente $e, User $user): void
    {
        if ($e->esFinal()) {
            throw new AccionNoPermitida('La solvencia ya está cerrada.');
        }
        if ((int) $e->holder_id !== (int) $user->id) {
            throw new AccionNoPermitida('Solo ' . $e->nombreDe($e->holder_id) . ' puede actuar sobre esta solvencia en este momento.');
        }
        if (! $e->acuse) {
            throw new AccionNoPermitida('Mientras no acuses de recibido no puedes aprobar, devolver ni modificar esta solvencia.');
        }
    }

    private function exigirEditable(Expediente $e, User $user): void
    {
        if (! $e->editablePor($user)) {
            throw new AccionNoPermitida('Los datos y documentos solo se modifican en la etapa de elaboración, por quien tiene la solvencia y ya acusó de recibido.');
        }
    }

    private function exigirChecklist(Expediente $e, string $prefijo): void
    {
        $faltan = $e->faltantes();

        if ($faltan) {
            $nombres = collect($faltan)->map(fn ($t) => config("solvencias.documentos.{$t}.nombre", $t))->implode(', ');
            throw new AccionNoPermitida("{$prefijo}: faltan " . count($faltan) . " documento(s) del checklist ({$nombres}).");
        }
    }

    private function exigirMotivo(string $motivo): string
    {
        $motivo = trim($motivo);
        $minimo = (int) config('solvencias.min_motivo', 10);

        if (mb_strlen($motivo) < $minimo) {
            throw new AccionNoPermitida("Escribe un motivo de al menos {$minimo} caracteres.");
        }

        return $motivo;
    }

    private function registrarDocumento(Expediente $e, User $user, string $tipo, UploadedFile $archivo, string $ruta): SolvenciaDocumento
    {
        $previo = $e->documentoActivo($tipo);
        $previo?->update(['reemplazado' => true]);

        $doc = SolvenciaDocumento::create([
            'expediente_id' => $e->id,
            'tipo'          => $tipo,
            'archivo'       => $ruta,
            'nombre'        => $this->nombreSeguro($archivo->getClientOriginalName()),
            'tamano'        => (int) $archivo->getSize(),
            'mime'          => Str::limit((string) $archivo->getClientMimeType(), 115, ''),
            'user_id'       => $user->id,
        ]);

        $etiqueta = config("solvencias.documentos.{$tipo}.nombre", $tipo);

        $accion = match (true) {
            ! $previo         => 'Documento cargado',
            $previo->exento   => 'Documento cargado (sustituye la observación)',
            default           => 'Documento reemplazado (nueva versión)',
        };

        $detalle = "{$etiqueta}: {$doc->nombre}";
        if ($previo && ! $previo->exento) {
            $detalle .= "\nVersión anterior conservada: {$previo->nombre}";
        }

        Bitacora::registrar($e, $user, $accion, $detalle, 'doc');

        return $doc;
    }

    private function guardarArchivo(Expediente $e, UploadedFile $archivo): string
    {
        $cfg  = config('solvencias.archivo');
        $ruta = $archivo->store($cfg['directorio'] . '/' . $e->id, $cfg['disco']);

        if (! $ruta) {
            throw new AccionNoPermitida('No se pudo guardar el archivo en el servidor. Revisa permisos de storage/.');
        }

        return $ruta;
    }

    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta) {
            Storage::disk(config('solvencias.archivo.disco'))->delete($ruta);
        }
    }

    private function nombreSeguro(string $nombre): string
    {
        $nombre = preg_replace('/[\x00-\x1F\x7F\/\\\\]/u', '', $nombre) ?: 'archivo';

        return Str::limit($nombre, 250, '');
    }

    private function pasado(string $verbo): string
    {
        return config("solvencias.pasado.{$verbo}", $verbo);
    }

    private function dinero($monto): string
    {
        return '$' . number_format((float) $monto, 2);
    }

    /**
     * Notifica después de confirmar la transacción. Nunca a quien hizo la
     * acción ni a usuarios inactivos. Un fallo de correo no revierte nada.
     *
     * @param array<int, array{0:string, 1:array, 2:string}> $avisos
     */
    private function notificar(Expediente $e, User $actor, array $avisos): void
    {
        $yaAvisados = [];

        foreach ($avisos as [$evento, $ids, $mensaje]) {
            $ids = collect($ids)->filter()->map(fn ($id) => (int) $id)
                ->reject(fn ($id) => $id === (int) $actor->id || in_array($id, $yaAvisados, true))
                ->unique()->values();

            if ($ids->isEmpty()) {
                continue;
            }

            foreach (User::whereIn('id', $ids)->where('activo', true)->get() as $destinatario) {
                $yaAvisados[] = (int) $destinatario->id;

                try {
                    $destinatario->notify(new SolvenciaNotificacion($e, $evento, $mensaje, $actor->name));
                } catch (Throwable $ex) {
                    logger()->error("Solvencias: no se pudo notificar a {$destinatario->id} ({$evento}, {$e->folio}): " . $ex->getMessage());
                }
            }
        }
    }
}
