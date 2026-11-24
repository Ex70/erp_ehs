{{-- Visor de Excel de solo lectura (lo llena solvencias.js) --}}
<div class="modal fade" id="solVisorExcel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" data-xl-titulo>Formato de solvencia</h5>
                    <div class="sol-sub" data-xl-sub></div>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap" style="gap:8px">
                    <button type="button" class="btn btn-sol btn-sm" data-xl-copiar><i class="fas fa-clipboard mr-1"></i>Copiar hoja (pegar en Excel)</button>
                    <a class="btn btn-outline-secondary btn-sm" href="#" data-xl-descargar><i class="fas fa-download mr-1"></i>Descargar original</a>
                </div>
                <div class="sol-xl-tabs" data-xl-tabs></div>
                <div class="sol-xl-wrap" data-xl-hoja></div>
                <div class="sol-hint">Vista previa de solo lectura: muestra los valores tal como quedaron guardados en el archivo. También puedes seleccionar celdas con el mouse y copiarlas.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
