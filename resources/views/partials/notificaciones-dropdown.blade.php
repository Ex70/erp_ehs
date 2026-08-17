@auth
<li class="nav-item dropdown" id="ehsNotiWrapper">
    <a class="nav-link" data-toggle="dropdown" href="#" role="button" title="Notificaciones">
        <i class="far fa-bell"></i>
        <span class="badge badge-danger navbar-badge"
              id="ehsNotiBadge"
              style="{{ $notiTotal > 0 ? '' : 'display:none;' }}">
            {{ $notiTotal > 99 ? '99+' : $notiTotal }}
        </span>
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header" id="ehsNotiHeader">
            {{ $notiTotal }} {{ $notiTotal === 1 ? 'notificación nueva' : 'notificaciones nuevas' }}
        </span>
        <div class="dropdown-divider"></div>

        <div id="ehsNotiLista" style="max-height: 320px; overflow-y: auto;">
            @forelse ($notiItems as $item)
                <a href="{{ route('notificaciones.ver', $item['id']) }}" class="dropdown-item">
                    <div class="media">
                        <span class="mr-2 mt-1 text-{{ $item['color'] }}">
                            <i class="{{ $item['icono'] }}"></i>
                        </span>
                        <div class="media-body">
                            <h3 class="dropdown-item-title text-sm mb-0">
                                {{ $item['titulo'] }}
                            </h3>
                            @if ($item['mensaje'])
                                <p class="text-sm text-muted mb-0 text-wrap">{{ $item['mensaje'] }}</p>
                            @endif
                            <p class="text-xs text-muted mb-0">
                                <i class="far fa-clock mr-1"></i>{{ $item['fecha_humana'] }}
                            </p>
                        </div>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
            @empty
                <span class="dropdown-item text-center text-muted py-3" id="ehsNotiVacio">
                    <i class="far fa-check-circle mr-1"></i> Sin notificaciones pendientes
                </span>
                <div class="dropdown-divider"></div>
            @endforelse
        </div>

        <a href="{{ route('notificaciones.index') }}" class="dropdown-item dropdown-footer">
            Ver todas las notificaciones
        </a>
    </div>
</li>

<script>
(function () {
    var INTERVALO = {{ (int) config('notificaciones.intervalo_refresco', 60) }};
    if (!INTERVALO || INTERVALO < 10) { return; }

    var URL_CONTADOR = "{{ route('notificaciones.contador') }}";
    var URL_VER_BASE = "{{ url('notificaciones') }}";

    function pintar(data) {
        var badge  = document.getElementById('ehsNotiBadge');
        var header = document.getElementById('ehsNotiHeader');
        var lista  = document.getElementById('ehsNotiLista');
        if (!badge || !header || !lista) { return; }

        var total = parseInt(data.total, 10) || 0;

        badge.textContent = total > 99 ? '99+' : total;
        badge.style.display = total > 0 ? '' : 'none';
        header.textContent = total + (total === 1 ? ' notificación nueva' : ' notificaciones nuevas');

        lista.innerHTML = '';

        if (!data.items || data.items.length === 0) {
            var vacio = document.createElement('span');
            vacio.className = 'dropdown-item text-center text-muted py-3';
            vacio.textContent = 'Sin notificaciones pendientes';
            lista.appendChild(vacio);
            lista.appendChild(divisor());
            return;
        }

        data.items.forEach(function (item) {
            var a = document.createElement('a');
            a.className = 'dropdown-item';
            a.href = URL_VER_BASE + '/' + item.id + '/ver';

            var media = document.createElement('div');
            media.className = 'media';

            var spanIcono = document.createElement('span');
            spanIcono.className = 'mr-2 mt-1 text-' + (item.color || 'primary');
            var i = document.createElement('i');
            i.className = item.icono || 'far fa-bell';
            spanIcono.appendChild(i);

            var body = document.createElement('div');
            body.className = 'media-body';

            var h3 = document.createElement('h3');
            h3.className = 'dropdown-item-title text-sm mb-0';
            h3.textContent = item.titulo || 'Notificación';
            body.appendChild(h3);

            if (item.mensaje) {
                var p = document.createElement('p');
                p.className = 'text-sm text-muted mb-0 text-wrap';
                p.textContent = item.mensaje;
                body.appendChild(p);
            }

            var pf = document.createElement('p');
            pf.className = 'text-xs text-muted mb-0';
            pf.textContent = item.fecha || '';
            body.appendChild(pf);

            media.appendChild(spanIcono);
            media.appendChild(body);
            a.appendChild(media);

            lista.appendChild(a);
            lista.appendChild(divisor());
        });
    }

    function divisor() {
        var d = document.createElement('div');
        d.className = 'dropdown-divider';
        return d;
    }

    function consultar() {
        fetch(URL_CONTADOR, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) { if (data) { pintar(data); } })
        .catch(function () { /* silencioso */ });
    }

    setInterval(consultar, INTERVALO * 1000);
})();
</script>
@endauth