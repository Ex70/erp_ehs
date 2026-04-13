{{-- Datos generales --}}
<div class="card card-outline card-primary mb-3">
    <div class="card-header py-2">
        <h3 class="card-title">Datos del proveedor</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $proveedor->nombre ?? '') }}"
                           required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>RFC</label>
                    <input type="text" name="rfc"
                           class="form-control text-uppercase"
                           value="{{ old('rfc', $proveedor->rfc ?? '') }}"
                           placeholder="RFC123456XXX">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Giro</label>
                    <input type="text" name="giro"
                           class="form-control"
                           value="{{ old('giro', $proveedor->giro ?? '') }}"
                           placeholder="Ej: Tecnología">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Contacto</label>
                    <input type="text" name="contacto"
                           class="form-control"
                           value="{{ old('contacto', $proveedor->contacto ?? '') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono"
                           class="form-control"
                           value="{{ old('telefono', $proveedor->telefono ?? '') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tiempo de entrega</label>
                    <input type="text" name="tiempo_entrega"
                           class="form-control"
                           value="{{ old('tiempo_entrega', $proveedor->tiempo_entrega ?? '') }}"
                           placeholder="Ej: 15 HORAS">
                </div>
            </div>
        </div>
        @isset($proveedor->id)
            <div class="form-group mb-0">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" class="custom-control-input"
                           id="activo" name="activo" value="1"
                           {{ old('activo', $proveedor->activo ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="activo">Activo</label>
                </div>
            </div>
        @endisset
    </div>
</div>

{{-- Cuentas bancarias --}}
<div class="card card-outline card-warning mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-university mr-1"></i> Cuentas bancarias
        </h3>
        <button type="button" class="btn btn-warning btn-sm"
                onclick="agregarCuenta()">
            <i class="fas fa-plus"></i> Agregar cuenta
        </button>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0" id="tabla-cuentas">
            <thead class="thead-light">
                <tr>
                    <th>Banco</th>
                    <th>CLABE</th>
                    <th>Cuenta</th>
                    <th>Referencia</th>
                    <th>Tiempo entrega</th>
                    <th>Principal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="cuentas-body">
                @php
                    $cuentasExistentes = old('cuentas',
                        isset($proveedor->cuentasBancarias)
                            ? $proveedor->cuentasBancarias->toArray()
                            : [['banco'=>'','clabe'=>'','cuenta'=>'','referencia'=>'','tiempo_entrega'=>'']]
                    );
                @endphp
                @foreach($cuentasExistentes as $i => $c)
                    <tr class="cuenta-row">
                        <td>
                            <input type="text" name="cuentas[{{ $i }}][banco]"
                                   class="form-control form-control-sm"
                                   value="{{ $c['banco'] ?? '' }}"
                                   placeholder="Ej: BBVA" required>
                        </td>
                        <td>
                            <input type="text" name="cuentas[{{ $i }}][clabe]"
                                   class="form-control form-control-sm"
                                   value="{{ $c['clabe'] ?? '' }}"
                                   placeholder="18 dígitos" maxlength="20">
                        </td>
                        <td>
                            <input type="text" name="cuentas[{{ $i }}][cuenta]"
                                   class="form-control form-control-sm"
                                   value="{{ $c['cuenta'] ?? '' }}">
                        </td>
                        <td>
                            <input type="text" name="cuentas[{{ $i }}][referencia]"
                                   class="form-control form-control-sm"
                                   value="{{ $c['referencia'] ?? '' }}">
                        </td>
                        <td>
                            <input type="text" name="cuentas[{{ $i }}][tiempo_entrega]"
                                   class="form-control form-control-sm"
                                   value="{{ $c['tiempo_entrega'] ?? '' }}"
                                   placeholder="Ej: 15 hrs">
                        </td>
                        <td class="text-center">
                            @if($i === 0)
                                <span class="badge badge-success">Principal</span>
                            @endif
                        </td>
                        <td>
                            @if($i > 0)
                                <button type="button" class="btn btn-danger btn-xs"
                                        onclick="this.closest('tr').remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<template id="tpl-cuenta">
    <tr class="cuenta-row">
        <td><input type="text" name="cuentas[__IDX__][banco]"
                   class="form-control form-control-sm" placeholder="Ej: BBVA" required></td>
        <td><input type="text" name="cuentas[__IDX__][clabe]"
                   class="form-control form-control-sm" placeholder="18 dígitos" maxlength="20"></td>
        <td><input type="text" name="cuentas[__IDX__][cuenta]"
                   class="form-control form-control-sm"></td>
        <td><input type="text" name="cuentas[__IDX__][referencia]"
                   class="form-control form-control-sm"></td>
        <td><input type="text" name="cuentas[__IDX__][tiempo_entrega]"
                   class="form-control form-control-sm" placeholder="Ej: 15 hrs"></td>
        <td></td>
        <td>
            <button type="button" class="btn btn-danger btn-xs"
                    onclick="this.closest('tr').remove()">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

<script>
let idxCuenta = {{ count($cuentasExistentes ?? []) }};

function agregarCuenta() {
    const tpl = document.getElementById('tpl-cuenta').innerHTML
                         .replace(/__IDX__/g, idxCuenta++);
    document.getElementById('cuentas-body').insertAdjacentHTML('beforeend', tpl);
}
</script>