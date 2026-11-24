{{-- Registrar pago (Contabilidad y Finanzas). La acción del formulario la fija
     solvencias.js con el data-url del botón [data-sol-pagar]. --}}
<div class="modal fade" id="solModalPago" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="#" enctype="multipart/form-data" class="modal-content" data-sol-once>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Registrar pago · <span class="sol-mono" data-pago-folio></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="sol-sub mb-3" data-pago-resumen></p>
                <div class="form-group">
                    <label>Comprobante de pago <span class="text-danger">*</span></label>
                    <div class="custom-file">
                        <input type="file" name="comprobante" class="custom-file-input" id="solComprobante" required
                               accept="{{ collect(config('solvencias.documentos.comprobante.extensiones'))->map(fn ($x) => '.' . $x)->implode(',') }}">
                        <label class="custom-file-label" for="solComprobante" data-browse="Examinar">Selecciona el comprobante…</label>
                    </div>
                    <small class="text-muted">{{ implode(', ', config('solvencias.documentos.comprobante.extensiones')) }} · máx. {{ number_format(config('solvencias.archivo.max_kb') / 1024) }} MB</small>
                </div>
                <div class="form-group mb-0">
                    <label>Fecha del pago <span class="text-danger">*</span></label>
                    <input type="date" name="fecha" class="form-control" required
                           value="{{ now(config('solvencias.zona_horaria'))->toDateString() }}"
                           max="{{ now(config('solvencias.zona_horaria'))->toDateString() }}">
                </div>
                <div class="sol-note mt-3">Al registrar el pago, la solicitud de recurso queda <b>finalizada</b>.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-sol">Registrar pago y finalizar</button>
            </div>
        </form>
    </div>
</div>
