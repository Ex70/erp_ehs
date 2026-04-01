<script>
// Auto-formatear MAC mientras escribe
document.getElementById('input-mac')?.addEventListener('input', function(e) {
    let val = e.target.value.replace(/[^0-9a-fA-F]/g, '');
    let formatted = val.match(/.{1,2}/g)?.join(':') || val;
    if (formatted.length > 17) formatted = formatted.slice(0, 17);
    e.target.value = formatted;
});

// Autocompletar IP base al escribir
document.getElementById('input-ip')?.addEventListener('focus', function() {
    if (!this.value) this.value = '192.168.0.';
});
</script>