{{-- ══ MODAL: DOCUMENTO NORMATIVO ══ --}}
<div class="modal fade" id="modalDocumentoNormativo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">

        {{-- multipart + spoofing de método: obligatorio para que @method('PUT')
             funcione junto con la subida de archivo --}}
        {{-- action, _method y form_action se conservan con old() para que, si la
             validación del servidor falla, el modal se reabra en el mismo modo
             (alta o edición) y contra el mismo documento --}}
        <form method="POST" enctype="multipart/form-data" id="formDocumentoNormativo"
              action="{{ old('form_action', route('rrhh.normatividad.store')) }}">
            @csrf
            <input type="hidden" name="_method" value="{{ old('_method', 'POST') }}" id="metodoDocumento">
            <input type="hidden" name="form_action" id="formAccionDocumento"
                   value="{{ old('form_action', route('rrhh.normatividad.store')) }}">

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title norm-serif">
                        📜 <span id="tituloModalDocumento">Nuevo Documento</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label class="text-muted text-uppercase small">Título *</label>
                        <input type="text" name="titulo" id="docTitulo" class="form-control"
                               maxlength="255" required placeholder="Nombre del documento"
                               value="{{ old('titulo') }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="text-muted text-uppercase small">Categoría *</label>
                            <select name="categoria" id="docCategoria" class="form-control" required>
                                @foreach ($categorias as $clave => $cfg)
                                    <option value="{{ $clave }}" @selected(old('categoria') === $clave)>{{ $cfg['icono'] }} {{ $cfg['singular'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label class="text-muted text-uppercase small">Versión</label>
                            <input type="text" name="version" id="docVersion" class="form-control"
                                   maxlength="30" placeholder="v1.0" value="{{ old('version') }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label class="text-muted text-uppercase small">Fecha de vigencia</label>
                            <input type="date" name="vigencia" id="docVigencia" class="form-control" value="{{ old('vigencia') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-muted text-uppercase small">Responsable</label>
                        <input type="text" name="responsable" id="docResponsable" class="form-control"
                               maxlength="150" placeholder="Área o persona responsable"
                               value="{{ old('responsable') }}">
                    </div>

                    <div class="form-group">
                        <label class="text-muted text-uppercase small">Descripción</label>
                        <textarea name="descripcion" id="docDescripcion" class="form-control"
                                  style="min-height:90px" maxlength="2000"
                                  placeholder="Resumen del documento…">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="text-muted text-uppercase small">📎 Archivo del documento</label>
                        <div class="custom-file">
                            <input type="file" name="archivo" id="docArchivo" class="custom-file-input"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                            <label class="custom-file-label" for="docArchivo" id="docArchivoLabel">
                                Seleccionar archivo…
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            PDF, Word, Excel o PowerPoint · máx.
                            {{ round(config('normatividad.archivo.max_kb') / 1024) }} MB
                        </small>
                        <div id="docArchivoActual" class="mt-2 small text-muted d-none"></div>
                    </div>

                    {{-- Solo visible al editar un documento que ya tiene archivo --}}
                    <div class="form-group d-none" id="grupoNotasVersion">
                        <label class="text-muted text-uppercase small">Nota de la versión reemplazada</label>
                        <input type="text" name="notas_version" id="docNotasVersion" class="form-control"
                               maxlength="255" placeholder="Ej. Actualización por cambio en NOM-035"
                               value="{{ old('notas_version') }}">
                        <small class="form-text text-muted">
                            Al subir un archivo nuevo, el anterior se archiva en el historial con esta nota.
                        </small>
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" class="custom-control-input" id="docActivo" name="activo" value="1"
                               @checked(old('activo', '1') == '1')>
                        <label class="custom-control-label" for="docActivo">
                            Documento activo (visible para todos los colaboradores)
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">💾 Guardar</button>
                </div>

            </div>
        </form>
    </div>
</div>
