{{-- Datos generales --}}
<div class="card card-outline card-primary mb-3">
    <div class="card-header py-2">
        <h3 class="card-title">
            <i class="fas fa-info-circle mr-1"></i> Datos generales
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Cliente <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="cliente_id"
                                class="form-control @error('cliente_id') is-invalid @enderror">
                            <option value="">— Selecciona —</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}"
                                    {{ old('cliente_id', $requerimiento->cliente_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <a href="{{ url('adquisiciones/clientes') }}" target="_blank"
                               class="btn btn-outline-secondary btn-sm" title="Gestionar clientes">
                                <i class="fas fa-cog"></i>
                            </a>
                        </div>
                    </div>
                    @error('cliente_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Empresa emisora <span class="text-danger">*</span></label>
                    <select name="empresa_emisora_id"
                            class="form-control @error('empresa_emisora_id') is-invalid @enderror">
                        <option value="">— Selecciona —</option>
                        @foreach($empresas as $e)
                            <option value="{{ $e->id }}"
                                {{ old('empresa_emisora_id', $requerimiento->empresa_emisora_id ?? '') == $e->id ? 'selected' : '' }}>
                                {{ $e->clave }} — {{ $e->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('empresa_emisora_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Empresa que realiza</label>
                    <input type="text" name="empresa_realiza"
                           class="form-control"
                           value="{{ old('empresa_realiza', $requerimiento->empresa_realiza ?? '') }}"
                           placeholder="Ej: TECNOLOGÍA">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Analista asignado</label>
                    <select name="analista_id" class="form-control">
                        <option value="">— Selecciona —</option>
                        @foreach($analistas as $a)
                            <option value="{{ $a->id }}"
                                {{ old('analista_id', $requerimiento->analista_id ?? auth()->id()) == $a->id ? 'selected' : '' }}>
                                {{ $a->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tipo <span class="text-danger">*</span></label>
                    <select name="tipo"
                            class="form-control @error('tipo') is-invalid @enderror">
                        @foreach($tipos as $key => $label)
                            <option value="{{ $key }}"
                                {{ old('tipo', $requerimiento->tipo ?? 'normal') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Línea de negocio</label>
                    <input type="text" name="linea_negocio"
                           class="form-control"
                           value="{{ old('linea_negocio', $requerimiento->linea_negocio ?? '') }}"
                           placeholder="Ej: Construcción">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Fecha solicitud <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_solicitud"
                           class="form-control @error('fecha_solicitud') is-invalid @enderror"
                           value="{{ old('fecha_solicitud', isset($requerimiento->fecha_solicitud) ? $requerimiento->fecha_solicitud->format('Y-m-d') : today()->format('Y-m-d')) }}">
                    @error('fecha_solicitud')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Fecha entrega</label>
                    <input type="date" name="fecha_entrega"
                           class="form-control"
                           value="{{ old('fecha_entrega', isset($requerimiento->fecha_entrega) ? $requerimiento->fecha_entrega->format('Y-m-d') : '') }}">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Margen %</label>
                    <input type="number" name="margen" step="0.01" min="0" max="100"
                           class="form-control"
                           value="{{ old('margen', $requerimiento->margen ?? 0) }}">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Indirectos %</label>
                    <input type="number" name="indirectos" step="0.01" min="0" max="100"
                           class="form-control"
                           value="{{ old('indirectos', $requerimiento->indirectos ?? 0) }}">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Monto estimado</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" name="monto_estimado" step="0.01" min="0"
                               class="form-control"
                               value="{{ old('monto_estimado', $requerimiento->monto_estimado ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Observaciones</label>
            <textarea name="observaciones" rows="2"
                      class="form-control"
                      placeholder="Observaciones generales...">{{ old('observaciones', $requerimiento->observaciones ?? '') }}</textarea>
        </div>

        @isset($requerimiento->id)
            <div class="form-group">
                <label>Estatus</label>
                <select name="status" class="form-control" style="max-width:200px">
                    @foreach(App\Models\Requerimiento::estatuses() as $key => $label)
                        <option value="{{ $key }}"
                            {{ $requerimiento->status == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endisset
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
    <div class="card-body p-0">
        <table class="table table-sm mb-0" id="tabla-partidas">
            <thead class="thead-light">
                <tr>
                    <th style="width:35%">Descripción</th>
                    <th style="width:10%">Cantidad</th>
                    <th style="width:15%">Unidad</th>
                    <th style="width:15%">P. Proveedor</th>
                    <th style="width:15%">P. Cliente</th>
                    <th style="width:10%"></th>
                </tr>
            </thead>
            <tbody id="partidas-body">
                @php
                    $partidasExistentes = old('partidas', isset($requerimiento->partidas)
                        ? $requerimiento->partidas->toArray()
                        : []);
                @endphp
                @forelse($partidasExistentes as $i => $p)
                    <tr class="partida-row">
                        <td>
                            <input type="text" name="partidas[{{ $i }}][descripcion]"
                                   class="form-control form-control-sm"
                                   value="{{ $p['descripcion'] ?? '' }}"
                                   placeholder="Descripción del artículo" required>
                        </td>
                        <td>
                            <input type="number" name="partidas[{{ $i }}][cantidad]"
                                   class="form-control form-control-sm"
                                   value="{{ $p['cantidad'] ?? 1 }}"
                                   min="0.01" step="0.01">
                        </td>
                        <td>
                            <select name="partidas[{{ $i }}][unidad_medida_id]"
                                    class="form-control form-control-sm">
                                <option value="">—</option>
                                @foreach($unidades as $u)
                                    <option value="{{ $u->id }}"
                                        {{ ($p['unidad_medida_id'] ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->clave }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="partidas[{{ $i }}][precio_proveedor]"
                                   class="form-control form-control-sm"
                                   value="{{ $p['precio_proveedor'] ?? '' }}"
                                   min="0" step="0.01" placeholder="0.00">
                        </td>
                        <td>
                            <input type="number" name="partidas[{{ $i }}][precio_cliente]"
                                   class="form-control form-control-sm"
                                   value="{{ $p['precio_cliente'] ?? '' }}"
                                   min="0" step="0.01" placeholder="0.00">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-xs"
                                    onclick="this.closest('tr').remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    {{-- Fila vacía inicial --}}
                    <tr class="partida-row">
                        <td><input type="text" name="partidas[0][descripcion]"
                                   class="form-control form-control-sm"
                                   placeholder="Descripción del artículo"></td>
                        <td><input type="number" name="partidas[0][cantidad]"
                                   class="form-control form-control-sm" value="1"
                                   min="0.01" step="0.01"></td>
                        <td>
                            <select name="partidas[0][unidad_medida_id]"
                                    class="form-control form-control-sm">
                                <option value="">—</option>
                                @foreach($unidades as $u)
                                    <option value="{{ $u->id }}">{{ $u->clave }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="partidas[0][precio_proveedor]"
                                   class="form-control form-control-sm"
                                   min="0" step="0.01" placeholder="0.00"></td>
                        <td><input type="number" name="partidas[0][precio_cliente]"
                                   class="form-control form-control-sm"
                                   min="0" step="0.01" placeholder="0.00"></td>
                        <td><button type="button" class="btn btn-danger btn-xs"
                                    onclick="this.closest('tr').remove()">
                                <i class="fas fa-times"></i>
                        </button></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Proveedores cotizantes --}}
<div class="card card-outline card-success mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-truck mr-1"></i> Proveedores cotizantes
        </h3>
        <button type="button" class="btn btn-success btn-sm" onclick="agregarProveedor()">
            <i class="fas fa-plus"></i> Agregar proveedor
        </button>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0" id="tabla-proveedores">
            <thead class="thead-light">
                <tr>
                    <th style="width:25%">Proveedor</th>
                    <th style="width:15%">Monto</th>
                    <th style="width:15%">T. Entrega</th>
                    <th style="width:12%">C. Envío</th>
                    <th style="width:12%">Disponib.</th>
                    <th style="width:15%">URL</th>
                    <th style="width:6%"></th>
                </tr>
            </thead>
            <tbody id="proveedores-body">
                @php
                    $provsExistentes = old('proveedores', isset($requerimiento->proveedores)
                        ? $requerimiento->proveedores->toArray()
                        : []);
                @endphp
                @forelse($provsExistentes as $i => $pv)
                    <tr class="proveedor-row">
                        <td>
                            <select name="proveedores[{{ $i }}][proveedor_id]"
                                    class="form-control form-control-sm">
                                <option value="">— Selecciona —</option>
                                @foreach($proveedores as $p)
                                    <option value="{{ $p->id }}"
                                        {{ ($pv['proveedor_id'] ?? '') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="proveedores[{{ $i }}][monto]"
                                   class="form-control form-control-sm"
                                   value="{{ $pv['monto'] ?? '' }}"
                                   min="0" step="0.01" placeholder="0.00">
                        </td>
                        <td>
                            <input type="text" name="proveedores[{{ $i }}][tiempo_entrega]"
                                   class="form-control form-control-sm"
                                   value="{{ $pv['tiempo_entrega'] ?? '' }}"
                                   placeholder="Ej: 3 días">
                        </td>
                        <td>
                            <input type="number" name="proveedores[{{ $i }}][costo_envio]"
                                   class="form-control form-control-sm"
                                   value="{{ $pv['costo_envio'] ?? 0 }}"
                                   min="0" step="0.01">
                        </td>
                        <td>
                            <select name="proveedores[{{ $i }}][disponibilidad]"
                                    class="form-control form-control-sm">
                                <option value="SI" {{ ($pv['disponibilidad'] ?? 'SI') == 'SI' ? 'selected' : '' }}>SI</option>
                                <option value="NO" {{ ($pv['disponibilidad'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                                <option value="PARCIAL" {{ ($pv['disponibilidad'] ?? '') == 'PARCIAL' ? 'selected' : '' }}>PARCIAL</option>
                            </select>
                        </td>
                        <td>
                            <input type="url" name="proveedores[{{ $i }}][url]"
                                   class="form-control form-control-sm"
                                   value="{{ $pv['url'] ?? '' }}"
                                   placeholder="https://...">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-xs"
                                    onclick="this.closest('tr').remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    {{-- Sin proveedores iniciales --}}
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Template oculto de partida --}}
<template id="tpl-partida">
    <tr class="partida-row">
        <td><input type="text" name="partidas[__IDX__][descripcion]"
                   class="form-control form-control-sm"
                   placeholder="Descripción del artículo"></td>
        <td><input type="number" name="partidas[__IDX__][cantidad]"
                   class="form-control form-control-sm" value="1" min="0.01" step="0.01"></td>
        <td>
            <select name="partidas[__IDX__][unidad_medida_id]"
                    class="form-control form-control-sm">
                <option value="">—</option>
                @foreach($unidades as $u)
                    <option value="{{ $u->id }}">{{ $u->clave }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="partidas[__IDX__][precio_proveedor]"
                   class="form-control form-control-sm" min="0" step="0.01" placeholder="0.00"></td>
        <td><input type="number" name="partidas[__IDX__][precio_cliente]"
                   class="form-control form-control-sm" min="0" step="0.01" placeholder="0.00"></td>
        <td><button type="button" class="btn btn-danger btn-xs"
                    onclick="this.closest('tr').remove()">
                <i class="fas fa-times"></i>
        </button></td>
    </tr>
</template>

{{-- Template oculto de proveedor --}}
<template id="tpl-proveedor">
    <tr class="proveedor-row">
        <td>
            <select name="proveedores[__IDX__][proveedor_id]"
                    class="form-control form-control-sm">
                <option value="">— Selecciona —</option>
                @foreach($proveedores as $p)
                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="proveedores[__IDX__][monto]"
                   class="form-control form-control-sm" min="0" step="0.01" placeholder="0.00"></td>
        <td><input type="text" name="proveedores[__IDX__][tiempo_entrega]"
                   class="form-control form-control-sm" placeholder="Ej: 3 días"></td>
        <td><input type="number" name="proveedores[__IDX__][costo_envio]"
                   class="form-control form-control-sm" value="0" min="0" step="0.01"></td>
        <td>
            <select name="proveedores[__IDX__][disponibilidad]"
                    class="form-control form-control-sm">
                <option value="SI">SI</option>
                <option value="NO">NO</option>
                <option value="PARCIAL">PARCIAL</option>
            </select>
        </td>
        <td><input type="url" name="proveedores[__IDX__][url]"
                   class="form-control form-control-sm" placeholder="https://..."></td>
        <td><button type="button" class="btn btn-danger btn-xs"
                    onclick="this.closest('tr').remove()">
                <i class="fas fa-times"></i>
        </button></td>
    </tr>
</template>