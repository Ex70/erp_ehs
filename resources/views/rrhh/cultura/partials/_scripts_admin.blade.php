{{-- ══ SCRIPTS DE ADMINISTRACIÓN (solo con permiso de edición) ══ --}}
<script>
(function () {
    'use strict';

    const TOKEN     = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const $editor   = document.getElementById('modalCulturaSecciones');
    const $slideMdl = document.getElementById('modalCulturaSlide');
    if (!$editor || !$slideMdl) return;

    const URL_SECCIONES = $editor.dataset.urlSecciones;
    const URL_ITEMS     = $editor.dataset.urlItems;
    const URL_SLIDES    = $slideMdl.dataset.urlSlides;

    /* ───────── Utilidades ───────── */

    function aviso(mensaje, tipo) {
        const div = document.createElement('div');
        div.className = 'alert alert-' + (tipo || 'success') + ' shadow';
        div.style.cssText = 'position:fixed;top:70px;right:20px;z-index:2000;min-width:260px';
        div.textContent = mensaje;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3200);
    }

    function mostrarError(caja, datos) {
        let texto = 'Ocurrió un error al guardar.';
        if (datos && datos.errors) {
            texto = Object.values(datos.errors).flat().join(' ');
        } else if (datos && datos.message) {
            texto = datos.message;
        }
        caja.textContent = texto;
        caja.classList.remove('d-none');
    }

    async function enviar(url, opciones) {
        const respuesta = await fetch(url, Object.assign({
            headers: {
                'X-CSRF-TOKEN': TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        }, opciones));

        const datos = await respuesta.json().catch(() => ({}));
        if (!respuesta.ok) throw datos;
        return datos;
    }

    function leerFilas(contenedor) {
        return Array.from(contenedor.querySelectorAll('.cultura-fila')).map(function (fila) {
            const item = {};
            fila.querySelectorAll('[data-campo]').forEach(function (campo) {
                const valor = campo.value.trim();
                if (campo.dataset.campo === 'id') {
                    if (valor) item.id = valor;
                } else {
                    item[campo.dataset.campo] = valor;
                }
            });
            return item;
        }).filter(i => i.titulo);
    }

    /* ───────── Editor de contenido ───────── */

    // Agregar filas
    document.querySelectorAll('[data-agregar]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const tipo = boton.dataset.agregar;
            const tpl  = document.getElementById('tplFila' + tipo.charAt(0).toUpperCase() + tipo.slice(1));
            const destino = {
                valor:    'listaValores',
                objetivo: 'listaObjetivos',
                empresa:  'listaEmpresas',
            }[tipo];
            document.getElementById(destino).appendChild(tpl.content.cloneNode(true));
        });
    });

    // Quitar filas (delegado)
    $editor.addEventListener('click', function (e) {
        if (e.target.classList.contains('js-quitar-fila')) {
            e.target.closest('.cultura-fila').remove();
        }
    });

    document.getElementById('btnGuardarCultura').addEventListener('click', async function () {
        const boton = this;
        const caja  = document.getElementById('culturaEditorError');
        caja.classList.add('d-none');

        const pestana = $editor.querySelector('#culturaEditorTabs .nav-link.active');
        const modo    = pestana.dataset.modo;

        boton.disabled = true;
        boton.textContent = 'Guardando…';

        try {
            if (modo === 'seccion') {
                const clave = pestana.dataset.clave;
                await enviar(URL_SECCIONES + '/' + clave, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        titulo: $editor.querySelector(`[data-campo="titulo"][data-clave="${clave}"]`).value,
                        texto:  $editor.querySelector(`[data-campo="texto"][data-clave="${clave}"]`).value,
                    }),
                });
            } else {
                const tipo  = pestana.dataset.tipo;
                const lista = { valor: 'listaValores', objetivo: 'listaObjetivos', empresa: 'listaEmpresas' }[tipo];

                // Los objetivos comparten pestaña con el título de la sección.
                if (pestana.dataset.clave === 'objetivos') {
                    await enviar(URL_SECCIONES + '/objetivos', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            titulo: $editor.querySelector('[data-campo="titulo"][data-clave="objetivos"]').value,
                            texto: null,
                        }),
                    });
                }

                await enviar(URL_ITEMS + '/' + tipo, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: leerFilas(document.getElementById(lista)) }),
                });
            }

            aviso('✅ Contenido guardado. Actualizando…', 'success');
            setTimeout(() => window.location.reload(), 700);
        } catch (datos) {
            mostrarError(caja, datos);
            boton.disabled = false;
            boton.textContent = '💾 Guardar Cambios';
        }
    });

    /* ───────── Carrusel: alta / edición / baja ───────── */

    function abrirSlide(datos) {
        document.getElementById('slideError').classList.add('d-none');
        document.getElementById('slidePreview').innerHTML = '';
        document.getElementById('slideImagen').value = '';
        document.getElementById('slideImagenLabel').textContent = 'Seleccionar archivo…';

        document.getElementById('slideId').value          = datos.id || '';
        document.getElementById('slideTitulo').value      = datos.titulo || '';
        document.getElementById('slideDescripcion').value = datos.descripcion || '';
        document.getElementById('slideIcono').value       = datos.icono || '🏢';
        document.getElementById('slideColor').value       = datos.color || '#1a0a0a';
        document.getElementById('slideOrden').value       = datos.orden || {{ ($slides->max('orden') ?? 0) + 1 }};
        document.getElementById('slideDuracion').value    = datos.duracion || 5;

        document.getElementById('tituloModalSlide').textContent =
            datos.id ? 'Editar Diapositiva' : 'Agregar Imagen al Carrusel';

        $('#modalCulturaSlide').modal('show');
    }

    document.getElementById('btnNuevaDiapositiva')?.addEventListener('click', () => abrirSlide({}));

    document.querySelectorAll('.js-editar-slide').forEach(function (boton) {
        boton.addEventListener('click', () => abrirSlide(Object.assign({}, boton.dataset)));
    });

    document.querySelectorAll('.js-eliminar-slide').forEach(function (boton) {
        boton.addEventListener('click', async function () {
            if (!confirm('¿Eliminar esta diapositiva del carrusel?')) return;
            try {
                await enviar(URL_SLIDES + '/' + boton.dataset.id, { method: 'DELETE' });
                window.location.reload();
            } catch (datos) {
                aviso('No se pudo eliminar la diapositiva.', 'danger');
            }
        });
    });

    document.getElementById('slideImagen').addEventListener('change', function () {
        const archivo = this.files[0];
        document.getElementById('slideImagenLabel').textContent = archivo ? archivo.name : 'Seleccionar archivo…';
        const vista = document.getElementById('slidePreview');
        vista.innerHTML = '';
        if (archivo) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(archivo);
            img.style.cssText = 'max-width:100%;max-height:150px;border-radius:8px';
            vista.appendChild(img);
        }
    });

    document.getElementById('btnGuardarSlide').addEventListener('click', async function () {
        const boton = this;
        const caja  = document.getElementById('slideError');
        caja.classList.add('d-none');

        const id = document.getElementById('slideId').value;
        const formulario = new FormData();
        formulario.append('titulo',      document.getElementById('slideTitulo').value);
        formulario.append('descripcion', document.getElementById('slideDescripcion').value);
        formulario.append('icono',       document.getElementById('slideIcono').value);
        formulario.append('color',       document.getElementById('slideColor').value);
        formulario.append('orden',       document.getElementById('slideOrden').value);
        formulario.append('duracion',    document.getElementById('slideDuracion').value);

        const archivo = document.getElementById('slideImagen').files[0];
        if (archivo) formulario.append('imagen', archivo);

        boton.disabled = true;
        boton.textContent = 'Guardando…';

        try {
            // POST real: la ruta de actualización acepta POST para permitir multipart/form-data.
            await enviar(URL_SLIDES + (id ? '/' + id : ''), { method: 'POST', body: formulario });
            window.location.reload();
        } catch (datos) {
            mostrarError(caja, datos);
            boton.disabled = false;
            boton.textContent = '💾 Guardar Diapositiva';
        }
    });
})();
</script>
