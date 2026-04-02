<script>
// Auto-formatear MAC
document.getElementById('input-mac')?.addEventListener('input', function(e) {
    let val = e.target.value.replace(/[^0-9a-fA-F]/g, '');
    let formatted = val.match(/.{1,2}/g)?.join(':') || val;
    if (formatted.length > 17) formatted = formatted.slice(0, 17);
    e.target.value = formatted;
});

// Autocompletar IP base
document.getElementById('input-ip')?.addEventListener('focus', function() {
    if (!this.value) this.value = '192.168.0.';
});

// Cargar datos del usuario al seleccionarlo
function cargarDatosUsuario(select) {
    const opt = select.options[select.selectedIndex];
    const infoBox = document.getElementById('info-usuario');

    if (!opt.value) {
        infoBox.style.display = 'none';
        return;
    }

    document.getElementById('show-puesto').value = opt.dataset.puesto || '—';

    // Pre-llenar área con el puesto si está vacía
    const areaInput = document.getElementById('input-area');
    if (!areaInput.value) {
        areaInput.value = opt.dataset.area || '';
    }

    infoBox.style.display = 'flex';
}

// Mostrar info si ya hay usuario seleccionado (edición)
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('select-usuario');
    if (sel && sel.value) cargarDatosUsuario(sel);
});
</script>