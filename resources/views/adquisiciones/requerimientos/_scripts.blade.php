<script>
let idxPartida   = document.querySelectorAll('.partida-row').length;
let idxProveedor = document.querySelectorAll('.proveedor-row').length;

function agregarPartida() {
    const tpl  = document.getElementById('tpl-partida').innerHTML
                         .replace(/__IDX__/g, idxPartida++);
    document.getElementById('partidas-body').insertAdjacentHTML('beforeend', tpl);
}

function agregarProveedor() {
    const tpl  = document.getElementById('tpl-proveedor').innerHTML
                         .replace(/__IDX__/g, idxProveedor++);
    document.getElementById('proveedores-body').insertAdjacentHTML('beforeend', tpl);
}
</script>