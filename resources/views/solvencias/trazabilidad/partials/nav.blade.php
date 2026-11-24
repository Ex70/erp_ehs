@php
    $solPendientes = \App\Models\SolvenciaExpediente::activas()->where('holder_id', auth()->id())->count();
@endphp
<ul class="nav nav-pills sol-nav mb-3 sol-noprint">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('sol.bandeja') ? 'active' : '' }}" href="{{ route('sol.bandeja') }}">
            <i class="fas fa-inbox mr-1"></i>Mi bandeja
            @if($solPendientes)<span class="badge ml-1">{{ $solPendientes }}</span>@endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('sol.tablero', 'sol.expedientes.show') ? 'active' : '' }}" href="{{ route('sol.tablero') }}">
            <i class="fas fa-route mr-1"></i>Tablero de trazabilidad
        </a>
    </li>
    @can('solvencias-pagar')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('sol.contabilidad') ? 'active' : '' }}" href="{{ route('sol.contabilidad') }}">
                <i class="fas fa-money-check-alt mr-1"></i>Contabilidad
            </a>
        </li>
    @endcan
    @can('solvencias_flujo.configurar')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('sol.flujos.*') ? 'active' : '' }}" href="{{ route('sol.flujos.index') }}">
                <i class="fas fa-project-diagram mr-1"></i>Personas y flujos
            </a>
        </li>
    @endcan
</ul>
