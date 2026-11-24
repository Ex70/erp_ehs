<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\SolvenciaFlujo;
use App\Models\SolvenciaFolio;
use App\Models\SolvenciaPagador;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Flujos iniciales de solvencias (del prototipo GQ_Solvencias_Trazabilidad).
 *
 * - Busca cada departamento por clave o nombre; si no existe, lo reporta.
 * - Asigna a las personas buscándolas por nombre entre los usuarios activos
 *   del ERP. Si no hay coincidencia única, deja la etapa vacía y la reporta.
 * - Un flujo que ya existe NO se toca (respeta lo configurado en pantalla).
 * - Sistemas usa el prefijo SIST y continúa la numeración del formato
 *   anterior (tabla solvencias, folios SIST-###).
 *
 * Todo lo que quede pendiente se termina en "Personas y flujos".
 */
class SolvenciaFlujosSeeder extends Seeder
{
    private array $reporte = [];

    public function run(): void
    {
        // Personas del prototipo (patrón LIKE sin acentos para no depender de la colación)
        $jahir  = ['nombre' => '%Jahir%Aburto%',     'etiqueta' => 'Jahir Julián Méndez Aburto'];
        $isaac  = ['nombre' => '%Isaac%Jord_n%',     'etiqueta' => 'Isaac Jordán López Hernández'];
        $olivia = ['nombre' => '%Olivia%Bautista%',  'etiqueta' => 'Olivia Bautista Castillo'];
        $mariel = ['nombre' => '%Mariel%Callejas%',  'etiqueta' => 'Mariel Callejas Vázquez'];
        $osv    = ['nombre' => '%Osvaldo%Bonilla%',  'etiqueta' => 'Osvaldo Alonso Bonilla Valencia'];
        $resp   = ['responsable' => true,            'etiqueta' => 'Responsable del departamento'];

        $cadena = fn (array $elabora) => [
            ['Elabora',  $elabora],
            ['Revisa',   [$jahir]],              // "Luis" se asigna en pantalla
            ['Valida',   [$isaac]],
            ['Autoriza', [$olivia]],
        ];

        $definicion = [
            ['Adquisiciones',  ['ADQ'],        ['Adquisiciones'],                  'ADQ',
                $cadena([$mariel, ['puesto' => 'Auxiliar Administrativo%', 'etiqueta' => 'Auxiliar administrativo de ADQ']])],
            ['Sistemas',       ['SIS', 'SIST'], ['Sistemas'],                      'SIST', $cadena([$resp])],
            ['Taller',         ['TAL'],        ['Taller'],                         'TAL',  $cadena([$resp])],
            ['Administración', ['ADM'],        ['Administración', 'Administracion'], 'ADM', [
                ['Elabora',         [$osv]],
                ['Revisa y valida', [$isaac]],
                ['Autoriza',        [$olivia]],
            ]],
            ['Jurídico',       ['JUR'],        ['Jurídico', 'Juridico'],           'JUR',  $cadena([$resp])],
            ['Ingeniería',     ['ING'],        ['Ingeniería', 'Ingenieria'],       'ING',  $cadena([$resp])],
        ];

        foreach ($definicion as [$etiqueta, $claves, $nombres, $prefijo, $etapas]) {
            $this->crearFlujo($etiqueta, $claves, $nombres, $prefijo, $etapas);
        }

        $this->pagadores();
        $this->continuarFoliosSistemas();

        $this->command?->table(['Flujo', 'Resultado'], $this->reporte);
        $this->command?->warn('Revisa "Personas y flujos" para completar etapas vacías (p. ej. "Luis" en Revisa) y confirmar cada flujo.');
    }

    private function crearFlujo(string $etiqueta, array $claves, array $nombres, string $prefijo, array $etapas): void
    {
        $depto = Departamento::whereIn('clave', $claves)->first();

        foreach ($nombres as $n) {
            $depto ??= Departamento::where('nombre', 'like', $n)->first();
        }

        if (! $depto) {
            $this->reporte[] = [$etiqueta, 'No existe el departamento (claves ' . implode('/', $claves) . '). Habilítalo en pantalla cuando exista.'];
            return;
        }

        if (SolvenciaFlujo::where('departamento_id', $depto->id)->exists()) {
            $this->reporte[] = [$etiqueta, 'Ya tenía flujo: no se modificó.'];
            return;
        }

        if (SolvenciaFlujo::where('prefijo_folio', $prefijo)->exists()) {
            $prefijo = strtoupper(substr($depto->clave ?: $prefijo, 0, 8)) . 'S';
        }

        $pendientes = [];

        DB::transaction(function () use ($depto, $prefijo, $etapas, &$pendientes) {
            $flujo = SolvenciaFlujo::create([
                'departamento_id' => $depto->id,
                'prefijo_folio'   => $prefijo,
                'activo'          => true,
                'por_confirmar'   => true,
            ]);

            foreach ($etapas as $orden => [$verbo, $personas]) {
                $etapa = $flujo->etapas()->create(['orden' => $orden, 'verbo' => $verbo]);
                $ids   = [];

                foreach ($personas as $p) {
                    $encontrados = $this->buscar($p, $depto);

                    if ($encontrados) {
                        array_push($ids, ...$encontrados);
                    } else {
                        $pendientes[] = "{$verbo}: {$p['etiqueta']}";
                    }
                }

                $etapa->personas()->syncWithoutDetaching(array_unique($ids));
            }
        });

        $this->reporte[] = [
            "{$etiqueta} ({$prefijo})",
            $pendientes ? 'Creado. Sin asignar → ' . implode('; ', $pendientes) : 'Creado con todas las etapas asignadas.',
        ];
    }

    /** @return array<int, int> ids de usuario encontrados */
    private function buscar(array $patron, Departamento $depto): array
    {
        if (! empty($patron['responsable'])) {
            $resp = $depto->responsable_id ? User::where('activo', true)->find($depto->responsable_id) : null;

            return $resp ? [$resp->id] : [];
        }

        if (! empty($patron['puesto'])) {
            return User::where('activo', true)
                ->where('departamento_id', $depto->id)
                ->whereHas('puesto', fn ($q) => $q->where('nombre', 'like', $patron['puesto']))
                ->pluck('id')->all();
        }

        $usuarios = User::where('activo', true)->where('name', 'like', $patron['nombre'])->pluck('id');

        // Solo una coincidencia exacta: ante la duda, mejor asignarlo en pantalla
        return $usuarios->count() === 1 ? [$usuarios->first()] : [];
    }

    private function pagadores(): void
    {
        if (SolvenciaPagador::exists()) {
            $this->reporte[] = ['Contabilidad y Finanzas', 'Ya tenía personas asignadas: no se modificó.'];
            return;
        }

        $depto = Departamento::whereIn('clave', ['CON', 'CYF', 'FIN'])->first()
            ?? Departamento::where('nombre', 'like', 'Contabilidad%')->first();

        if ($depto?->responsable_id && User::where('activo', true)->find($depto->responsable_id)) {
            SolvenciaPagador::create(['user_id' => $depto->responsable_id]);
            $this->reporte[] = ['Contabilidad y Finanzas', 'Se asignó al responsable del departamento ' . $depto->nombre . '.'];
            return;
        }

        $this->reporte[] = ['Contabilidad y Finanzas', 'Sin asignar: agrega en pantalla quién registra los pagos.'];
    }

    /**
     * La serie SIST-{año} arranca donde se quedó el formato anterior (SIST-###).
     */
    private function continuarFoliosSistemas(): void
    {
        $flujo = SolvenciaFlujo::where('prefijo_folio', 'SIST')->first();

        if (! $flujo || ! Schema::hasTable('solvencias')) {
            return;
        }

        $maximo = DB::table('solvencias')->where('folio', 'like', 'SIST-%')->pluck('folio')
            ->map(fn ($f) => preg_match('/^SIST-(\d+)$/', $f, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        if ($maximo < 1) {
            $this->reporte[] = ['Folios SIST', 'El formato anterior no tiene folios SIST-###; la serie inicia en 0001.'];
            return;
        }

        $serie = SolvenciaFolio::serie('SIST');
        $folio = SolvenciaFolio::firstOrNew(['serie' => $serie], ['ultimo' => 0]);

        if ($folio->ultimo < $maximo) {
            $folio->ultimo = $maximo;
            $folio->save();
        }

        $this->reporte[] = ['Folios SIST', "Último folio anterior SIST-{$maximo}: la siguiente solvencia de Sistemas será {$serie}-" . str_pad((string) ($folio->ultimo + 1), 4, '0', STR_PAD_LEFT) . '.'];
    }
}
