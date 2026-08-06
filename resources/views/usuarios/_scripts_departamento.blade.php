<script>
document.addEventListener('DOMContentLoaded', function () {

    const selDepto  = document.getElementById('departamento_id');
    const selPuesto = document.getElementById('puesto_id');

    if (!selDepto || !selPuesto) return;

    const baseUrl = "{{ url('departamentos') }}";

    /**
     * Recarga el select de puestos según el departamento.
     * @param {string|null} preseleccion  ID del puesto a dejar marcado
     */
    function cargarPuestos(preseleccion = null) {

        const deptoId = selDepto.value;

        selPuesto.innerHTML = '';
        selPuesto.disabled  = true;

        if (!deptoId) {
            selPuesto.innerHTML = '<option value="">Seleccione primero el departamento</option>';
            return;
        }

        selPuesto.innerHTML = '<option value="">Cargando...</option>';

        fetch(`${baseUrl}/${deptoId}/puestos`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (resp) {
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            return resp.json();
        })
        .then(function (puestos) {

            selPuesto.innerHTML = '<option value="">Seleccione un puesto...</option>';

            if (puestos.length === 0) {
                selPuesto.innerHTML =
                    '<option value="">Este departamento no tiene puestos asignados</option>';
                return;
            }

            puestos.forEach(function (p) {
                const opt = document.createElement('option');
                opt.value       = p.id;
                opt.textContent = p.nombre;
                if (preseleccion && String(p.id) === String(preseleccion)) {
                    opt.selected = true;
                }
                selPuesto.appendChild(opt);
            });

            selPuesto.disabled = false;
        })
        .catch(function (error) {
            console.error('Error al cargar puestos:', error);
            selPuesto.innerHTML = '<option value="">Error al cargar los puestos</option>';
        });
    }

    // Al cambiar de departamento se limpia la selección previa
    selDepto.addEventListener('change', function () {
        cargarPuestos(null);
    });

    // Carga inicial: cubre la edición y el retorno por error de validación
    if (selDepto.value) {
        cargarPuestos(selPuesto.dataset.selected || null);
    } else {
        selPuesto.disabled = true;
    }
});
</script>