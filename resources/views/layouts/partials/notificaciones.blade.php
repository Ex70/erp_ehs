{{-- resources/views/layouts/partials/notificaciones.blade.php --}}
{{-- Incluir en el navbar de AdminLTE con: @include('layouts.partials.notificaciones') --}}

@auth
@php
    $notifs = auth()->user()
        ->unreadNotifications()
        ->where('type', 'like', '%Ticket%')
        ->latest()
        ->take(10)
        ->get();
    $total = auth()->user()->unreadNotifications()->count();
@endphp

<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" title="Notificaciones">
        <i class="far fa-bell"></i>
        @if($total > 0)
            <span class="badge badge-warning navbar-badge">{{ $total > 99 ? '99+' : $total }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">
            {{ $total }} notificación{{ $total !== 1 ? 'es' : '' }} sin leer
        </span>

        <div class="dropdown-divider"></div>

        @forelse($notifs as $notif)
        @php $data = $notif->data; @endphp
        <a href="{{ $data['url'] ?? '#' }}"
           class="dropdown-item notif-item"
           data-id="{{ $notif->id }}">
            <i class="{{ $data['icono'] ?? 'fas fa-bell' }} mr-2 text-primary"></i>
            <span class="text-wrap" style="white-space:normal; font-size:0.85rem;">
                {{ \Str::limit($data['detalle'] ?? '', 80) }}
            </span>
            <span class="float-right text-muted text-sm">
                {{ $notif->created_at->diffForHumans() }}
            </span>
        </a>
        <div class="dropdown-divider"></div>
        @empty
        <div class="dropdown-item text-center text-muted">
            <i class="fas fa-check-circle text-success mr-1"></i> Todo al día
        </div>
        @endforelse

        @if($total > 0)
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item dropdown-footer text-danger" id="marcar-todo-leido">
            <i class="fas fa-check-double mr-1"></i> Marcar todo como leído
        </a>
        @endif
    </div>
</li>

{{-- Script para marcar como leído al hacer clic --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Marcar notificación individual al hacer clic
    document.querySelectorAll('.notif-item').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const id = this.dataset.id;
            fetch(`/notificaciones/${id}/leer`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            });
        });
    });

    // Marcar todas como leídas
    const btnTodo = document.getElementById('marcar-todo-leido');
    if (btnTodo) {
        btnTodo.addEventListener('click', function (e) {
            e.preventDefault();
            fetch('/notificaciones/leer-todas', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            }).then(() => window.location.reload());
        });
    }

});
</script>
@endauth