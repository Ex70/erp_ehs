{{-- ══ SCRIPTS DE ADMINISTRACIÓN ══ --}}
<script>
(function () {
    'use strict';

    const formulario = document.getElementById('formDocumentoNormativo');
    if (!formulario) return;

    const ACCION_ALTA = '{{ route('rrhh.normatividad.store') }}';

    const campos = {
        titulo:      document.getElementById('docTitulo'),
        categoria:   document.getElementById('docCategoria'),
        version:     document.getElementById('docVersion'),
        vigencia:    document.getElementById('docVigencia'),
        responsable: document.getElementById('docResponsable'),
        descripcion: document.getElementById('docDescripcion'),
        activo:      document.getElementById('docActivo'),
        archivo:     document.getElementById('docArchivo'),
    };

    const metodo         = document.getElementById('metodoDocumento');
    const formAccion     = document.getElementById('formAccionDocumento');
    const tituloModal    = document.getElementById('tituloModalDocumento');
    const etiquetaArch   = document.getElementById('docArchivoLabel');
    const archivoActual  = document.getElementById('docArchivoActual');
    const grupoNotas     = document.getElementById('grupoNotasVersion');
    const notasVersion   = document.getElementById('docNotasVersion');

    function limpiar() {
        campos.titulo.value      = '';
        campos.version.value     = '';
        campos.vigencia.value    = '';
        campos.responsable.value = '';
        campos.descripcion.value = '';
        campos.activo.checked    = true;
        campos.archivo.value     = '';
        notasVersion.value       = '';

        etiquetaArch.textContent = 'Seleccionar archivo…';
        archivoActual.classList.add('d-none');
        archivoActual.textContent = '';
        grupoNotas.classList.add('d-none');
    }

    /* ── Alta ── */
    document.getElementById('btnNuevoDocumento')?.addEventListener('click', function () {
        limpiar();

        formulario.action  = ACCION_ALTA;
        formAccion.value   = ACCION_ALTA;
        metodo.value       = 'POST';
        tituloModal.textContent = 'Nuevo Documento';

        // Preselecciona la categoría de la pestaña visible
        const activa = document.querySelector('.norm-tab.active');
        if (activa) campos.categoria.value = activa.dataset.pane;

        $('#modalDocumentoNormativo').modal('show');
    });

    /* ── Edición ── */
    document.querySelectorAll('.js-editar-documento').forEach(function (boton) {
        boton.addEventListener('click', function () {
            limpiar();

            const d = boton.dataset;

            formulario.action  = d.accion;
            formAccion.value   = d.accion;
            metodo.value       = 'PUT';
            tituloModal.textContent = 'Editar Documento';

            campos.titulo.value      = d.titulo || '';
            campos.categoria.value   = d.categoria || '';
            campos.version.value     = d.version || '';
            campos.vigencia.value    = d.vigencia || '';
            campos.responsable.value = d.responsable || '';
            campos.descripcion.value = d.descripcion || '';
            campos.activo.checked    = d.activo === '1';

            if (d.archivo) {
                archivoActual.textContent = 'Archivo actual: ' + d.archivo +
                    ' — solo se reemplaza si seleccionas uno nuevo.';
                archivoActual.classList.remove('d-none');
                grupoNotas.classList.remove('d-none');
            }

            $('#modalDocumentoNormativo').modal('show');
        });
    });

    /* ── Nombre del archivo seleccionado ── */
    campos.archivo.addEventListener('change', function () {
        etiquetaArch.textContent = this.files[0] ? this.files[0].name : 'Seleccionar archivo…';
    });

    /* ── Evita doble envío ── */
    formulario.addEventListener('submit', function () {
        const boton = formulario.querySelector('button[type="submit"]');
        if (boton) {
            boton.disabled = true;
            boton.textContent = 'Guardando…';
        }
    });

    /* ── Reabre el modal si la validación del servidor falló ── */
    @if ($errors->any())
        $('#modalDocumentoNormativo').modal('show');
    @endif
})();
</script>
