{{-- ══ MODAL: DIAPOSITIVA DEL CARRUSEL ══ --}}
<div class="modal fade" id="modalCulturaSlide" tabindex="-1" role="dialog" aria-hidden="true"
     data-url-slides="{{ url('rrhh/cultura/slides') }}">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title cultura-serif">🖼️ <span id="tituloModalSlide">Agregar Imagen al Carrusel</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="slideId" value="">

                <div class="form-group">
                    <label class="text-muted text-uppercase small">Título de la diapositiva *</label>
                    <input type="text" class="form-control" id="slideTitulo" maxlength="200"
                           placeholder="Ej. Bienvenidos al Grupo Quetzalcóatl">
                </div>

                <div class="form-group">
                    <label class="text-muted text-uppercase small">Descripción / Subtítulo</label>
                    <input type="text" class="form-control" id="slideDescripcion" maxlength="500"
                           placeholder="Historia, misión, valores...">
                </div>

                <div class="form-row">
                    <div class="form-group col-6">
                        <label class="text-muted text-uppercase small">Orden</label>
                        <input type="number" class="form-control" id="slideOrden" min="1" max="999" value="1">
                    </div>
                    <div class="form-group col-6">
                        <label class="text-muted text-uppercase small">Duración (seg)</label>
                        <input type="number" class="form-control" id="slideDuracion" min="2" max="30" value="5">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-6">
                        <label class="text-muted text-uppercase small">Emoji (si no hay imagen)</label>
                        <input type="text" class="form-control" id="slideIcono" maxlength="4" value="🏢">
                    </div>
                    <div class="form-group col-6">
                        <label class="text-muted text-uppercase small">Color de fondo</label>
                        <input type="text" class="form-control" id="slideColor" maxlength="20" value="#1a0a0a" placeholder="#1a0a0a">
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="text-muted text-uppercase small">🖼️ Imagen de la diapositiva</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="slideImagen" accept="image/jpeg,image/png,image/gif,image/webp">
                        <label class="custom-file-label" for="slideImagen" id="slideImagenLabel">Seleccionar archivo…</label>
                    </div>
                    <small class="form-text text-muted">JPG, PNG, GIF o WEBP · máx. 4 MB · recomendado 1200×400 px</small>
                    <div id="slidePreview" class="mt-2"></div>
                </div>

                <div class="alert alert-danger mt-3 d-none" id="slideError"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarSlide">💾 Guardar Diapositiva</button>
            </div>

        </div>
    </div>
</div>
