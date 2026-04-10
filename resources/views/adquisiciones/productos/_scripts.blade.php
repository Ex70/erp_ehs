<script>
// Preview de imagen antes de subir
document.getElementById('input-imagen')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(ev) {
        let img = document.getElementById('preview-imagen');
        const container = document.getElementById('preview-imagen-container');

        if (!img) {
            img = document.createElement('img');
            img.id = 'preview-imagen';
            img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:8px';
        }

        img.src = ev.target.result;

        if (container) {
            container.style.display = 'block';
            if (!container.contains(img)) container.appendChild(img);
        }
    };
    reader.readAsDataURL(file);
});

// Nueva categoría rápida
function nuevaCategoria() {
    $('#modal-cat-rapida').modal('show');
}

function guardarCategoriaRapida() {
    const nombre = document.getElementById('cat-rapida-nombre').value.trim();
    if (!nombre) return;

    fetch('{{ url('adquisiciones/categorias-producto') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ nombre }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.id) {
            const sel = document.getElementById('select-categoria');
            const opt = new Option(data.nombre, data.id, true, true);
            sel.appendChild(opt);
            $('#modal-cat-rapida').modal('hide');
            document.getElementById('cat-rapida-nombre').value = '';
        }
    })
    .catch(() => alert('Error al guardar la categoría.'));
}
</script>