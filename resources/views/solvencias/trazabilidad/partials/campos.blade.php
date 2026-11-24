{{-- Captura mínima. Se usa en "Nueva solvencia" ($flujos) y en "Corregir datos" ($datos = expediente). --}}
@php
    $tipo = old('tipo_proveedor', $datos?->tipo_proveedor ?? 'validado');
    $req  = config('solvencias.requeridos');
@endphp

<div class="form-group">
    <label class="d-block">Tipo de proveedor <small class="text-muted">(define el checklist)</small></label>
    <div class="btn-group btn-group-toggle flex-wrap" data-toggle="buttons">
        <label class="btn btn-outline-secondary {{ $tipo === 'validado' ? 'active' : '' }}">
            <input type="radio" name="tipo_proveedor" value="validado" autocomplete="off" @checked($tipo === 'validado')>
            Proveedor validado · {{ count($req['validado']) }} documentos
        </label>
        <label class="btn btn-outline-secondary {{ $tipo === 'nuevo' ? 'active' : '' }}">
            <input type="radio" name="tipo_proveedor" value="nuevo" autocomplete="off" @checked($tipo === 'nuevo')>
            Nuevo proveedor · {{ count($req['nuevo']) }} documentos
        </label>
    </div>
</div>

<div class="form-group">
    <label for="solProveedor">Proveedor <small class="text-muted">(escríbelo en el momento)</small></label>
    <input type="text" id="solProveedor" name="proveedor" class="form-control @error('proveedor') is-invalid @enderror"
           maxlength="200" required autocomplete="off" list="solProveedores"
           value="{{ old('proveedor', $datos?->proveedor) }}" placeholder="Nombre o razón social del proveedor">
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="solMonto">¿Cuánto se paga? <small class="text-muted">(con IVA)</small></label>
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
            <input type="number" id="solMonto" name="monto" step="0.01" min="0.01" required
                   class="form-control @error('monto') is-invalid @enderror"
                   value="{{ old('monto', $datos?->monto) }}" placeholder="0.00">
        </div>
    </div>

    <div class="form-group col-md-6">
        <label>Unidad <small class="text-muted">(define el flujo y el folio)</small></label>
        @if($datos)
            <input type="text" class="form-control" disabled value="{{ $datos->departamento?->nombre }}">
        @elseif($flujos->count() === 1)
            <input type="text" class="form-control" disabled value="{{ $flujos->first()->nombre }} · {{ $flujos->first()->prefijo_folio }}">
            <input type="hidden" name="flujo_id" value="{{ $flujos->first()->id }}">
        @else
            <select name="flujo_id" class="form-control" required>
                @foreach($flujos as $f)
                    <option value="{{ $f->id }}" @selected((string) old('flujo_id') === (string) $f->id)>{{ $f->nombre }} · {{ $f->prefijo_folio }}</option>
                @endforeach
            </select>
        @endif
    </div>
</div>

<div class="form-group mb-0">
    <label for="solDescripcion">¿De qué se trata? <small class="text-muted">(descripción)</small></label>
    <textarea id="solDescripcion" name="descripcion" rows="3" maxlength="300" required
              class="form-control @error('descripcion') is-invalid @enderror"
              placeholder="Ej. Cabezal de corte AUTOCUT C 26-2 STIHL para cotización MHR-0375-2026">{{ old('descripcion', $datos?->descripcion) }}</textarea>
    <small class="text-muted">Empresa, cotización, cliente, partidas y datos bancarios van en el formato de solvencia (Excel) adjunto.</small>
</div>
