@php use App\Models\Solvencia; @endphp

{{-- Empresa y fecha --}}
<div class="card card-outline card-primary mb-3">
    <div class="card-header py-2">
        <h3 class="card-title">
            <i class="fas fa-building mr-1"></i> Datos del documento
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Empresa <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="empresa_solvencia_id"
                                class="form-control @error('empresa_solvencia_id') is-invalid @enderror"
                                required>
                            <option value="">— Selecciona —</option>
                            @foreach($empresas as $e)
                                <option value="{{ $e->id }}"
                                    {{ old('empresa_solvencia_id', $solvencia->empresa_solvencia_id ?? '') == $e->id ? 'selected' : '' }}>
                                    {{ $e->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="nuevaEmpresaRapida()"
                                    title="Agregar empresa">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    @error('empresa_solvencia_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Fecha <span class="text-danger">*</span></label>
                    <input type="date" name="fecha"
                           class="form-control @error('fecha') is-invalid @enderror"
                           value="{{ old('fecha', isset($solvencia->fecha) ? $solvencia->fecha->format('Y-m-d') : today()->format('Y-m-d')) }}"
                           required>
                    @error('fecha')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label>N° Cotización</label>
                    <input type="text" name="numero_cotizacion"
                           class="form-control"
                           value="{{ old('numero_cotizacion', $solvencia->numero_cotizacion ?? '') }}"
                           placeholder="Ej: G144479079/G144157020/G143916936">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Cliente</label>
                    <input type="text" name="cliente"
                           class="form-control"
                           value="{{ old('cliente', $solvencia->cliente ?? '') }}"
                           placeholder="Ej: EHS">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Departamento</label>
                    <input type="text" name="departamento"
                           class="form-control"
                           value="{{ old('departamento', $solvencia->departamento ?? Auth::user()->puesto?->nombre ?? '') }}"
                           placeholder="Ej: Sistemas">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-0">
                    <label>Subtotal</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="text" id="resumen-subtotal"
                               class="form-control bg-light text-right"
                               value="{{ number_format($solvencia->subtotal ?? 0, 2) }}"
                               readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-0">
                    <label>IVA (16%)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="text" id="resumen-iva"
                               class="form-control bg-light text-right"
                               value="{{ number_format($solvencia->iva ?? 0, 2) }}"
                               readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-0">
                    <label>Total = Monto solicitado y autorizado</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="text" id="resumen-total"
                               class="form-control bg-light font-weight-bold text-right"
                               value="{{ number_format($solvencia->total ?? 0, 2) }}"
                               readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Partidas --}}
<div class="card card-outline card-warning mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i> Partidas
        </h3>
        <button type="button" class="btn btn-warning btn-sm" onclick="agregarPartida()">
            <i class="fas fa-plus"></i> Agregar partida
        </button>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0" id="tabla-partidas" style="min-width:1000px">
            <thead class="thead-light">
                <tr>
                    <th style="width:3%">#</th>
                    <th style="width:22%">Descripción</th>
                    <th style="width:8%">Cantidad</th>
                    <th style="width:10%">Importe</th>
                    <th style="width:20%">Proveedor</th>
                    <th style="width:15%">Cuenta bancaria</th>
                    <th style="width:18%">Concepto / Forma de pago</th>
                    <th style="width:4%"></th>
                </tr>
            </thead>
            <tbody id="partidas-body">
                @php
                    $partidasExistentes = old('partidas',
                        isset($solvencia->partidas)
                            ? $solvencia->partidas->toArray()
                            : [['descripcion'=>'','cantidad'=>1,'importe'=>0,'proveedor_solvencia_id'=>null,'cuenta_bancaria_id'=>null,'concepto'=>'']]
                    );
                @endphp
                @foreach($partidasExistentes as $i => $p)
                    <tr class="partida-row" data-idx="{{ $i }}">
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>
                            <input type="text"
                                   name="partidas[{{ $i }}][descripcion]"
                                   class="form-control form-control-sm"
                                   value="{{ $p['descripcion'] ?? '' }}"
                                   placeholder="Descripción" required>
                        </td>
                        <td>
                            <input type="number"
                                   name="partidas[{{ $i }}][cantidad]"
                                   class="form-control form-control-sm text-right"
                                   value="{{ $p['cantidad'] ?? 1 }}"
                                   min="0" step="0.01"
                                   title="Solo informativo, no afecta el importe">
                        </td>
                        <td>
                            <input type="number"
                                   name="partidas[{{ $i }}][importe]"
                                   class="form-control form-control-sm text-right input-importe"
                                   value="{{ $p['importe'] ?? 0 }}"
                                   min="0" step="0.01"
                                   oninput="recalcularTotales()"
                                   required>
                        </td>
                        <td>
                            <select name="partidas[{{ $i }}][proveedor_solvencia_id]"
                                    class="form-control form-control-sm select-proveedor"
                                    data-idx="{{ $i }}"
                                    onchange="cargarCuentas(this, {{ $i }})">
                                <option value="">— Proveedor —</option>
                                @foreach($proveedores as $pv)
                                    <option value="{{ $pv->id }}"
                                        {{ ($p['proveedor_solvencia_id'] ?? '') == $pv->id ? 'selected' : '' }}>
                                        {{ $pv->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="partidas[{{ $i }}][cuenta_bancaria_id]"
                                    class="form-control form-control-sm select-cuenta"
                                    id="cuentas-{{ $i }}">
                                <option value="">— Cuenta —</option>
                                @if(!empty($p['proveedor_solvencia_id']))
                                    @php
                                        $pvObj = $proveedores->firstWhere('id', $p['proveedor_solvencia_id']);
                                    @endphp
                                    @if($pvObj)
                                        @foreach($pvObj->cuentasBancarias as $cb)
                                            <option value="{{ $cb->id }}"
                                                {{ ($p['cuenta_bancaria_id'] ?? '') == $cb->id ? 'selected' : '' }}>
                                                {{ $cb->banco }} — {{ $cb->clabe }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endif
                            </select>
                        </td>
                        <td>
                            <input type="text"
                                   name="partidas[{{ $i }}][concepto]"
                                   class="form-control form-control-sm"
                                   value="{{ $p['concepto'] ?? '' }}"
                                   placeholder="Ej: PAGO CON TARJETA">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-xs"
                                    onclick="eliminarPartida(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Firmas --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-header py-2">
        <h3 class="card-title">
            <i class="fas fa-signature mr-1"></i> Firmas
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card border p-3">
                    <p class="text-center font-weight-bold mb-2 small text-uppercase">Elaboró</p>
                    <div class="form-group">
                        <label class="small">Nombre</label>
                        <input type="text" name="elaboro_nombre"
                               class="form-control form-control-sm"
                               value="{{ old('elaboro_nombre', $solvencia->elaboro_nombre ?? Auth::user()->name) }}">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small">Cargo</label>
                        <input type="text" name="elaboro_cargo"
                               class="form-control form-control-sm"
                               value="{{ old('elaboro_cargo', $solvencia->elaboro_cargo ?? Auth::user()->puesto?->nombre ?? '') }}">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border p-3">
                    <p class="text-center font-weight-bold mb-2 small text-uppercase">Validó</p>
                    <div class="form-group">
                        <label class="small">Nombre</label>
                        <input type="text" name="valido_nombre"
                               class="form-control form-control-sm"
                               value="{{ old('valido_nombre', $solvencia->valido_nombre ?? '') }}"
                               placeholder="Nombre del validador">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small">Cargo</label>
                        <input type="text" name="valido_cargo"
                               class="form-control form-control-sm"
                               value="{{ old('valido_cargo', $solvencia->valido_cargo ?? '') }}"
                               placeholder="Cargo del validador">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border p-3">
                    <p class="text-center font-weight-bold mb-2 small text-uppercase">Autorizó</p>
                    <div class="form-group">
                        <label class="small">Nombre</label>
                        <input type="text" name="autorizo_nombre"
                               class="form-control form-control-sm"
                               value="{{ old('autorizo_nombre', $solvencia->autorizo_nombre ?? '') }}"
                               placeholder="Nombre del autorizador">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small">Cargo</label>
                        <input type="text" name="autorizo_cargo"
                               class="form-control form-control-sm"
                               value="{{ old('autorizo_cargo', $solvencia->autorizo_cargo ?? '') }}"
                               placeholder="Cargo del autorizador">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-3 mb-0">
            <label>Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2"
                      placeholder="Observaciones adicionales...">{{ old('observaciones', $solvencia->observaciones ?? '') }}</textarea>
        </div>
    </div>
</div>

{{-- Modal empresa rápida --}}
<div class="modal fade" id="modal-empresa-rapida" tabindex="-1" style="z-index:1060">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva empresa</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="emp-rapida-nombre" class="form-control" required>
                </div>
                <div class="form-group mb-0">
                    <label>RFC</label>
                    <input type="text" id="emp-rapida-rfc" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                        data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm"
                        onclick="guardarEmpresaRapida()">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Template proveedor JSON --}}
<script>
const PROVEEDORES_DATA = @json($proveedores->map(fn($p) => [
    'id'     => $p->id,
    'nombre' => $p->nombre,
    'rfc'    => $p->rfc,
    'cuentas'=> $p->cuentasBancarias->map(fn($c) => [
        'id'    => $c->id,
        'banco' => $c->banco,
        'clabe' => $c->clabe,
        'cuenta'=> $c->cuenta,
        'ref'   => $c->referencia,
    ])->values(),
])->values());

const API_CUENTAS = '{{ url('solvencias/api/proveedor') }}';
</script>