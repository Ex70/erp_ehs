@extends('adminlte::page')
@section('title', 'Ticket ' . $ticket->folio)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">{{ $ticket->folio }}</h1>
            <small class="text-muted">
                Departamento de Sistemas — {{ config('app.name') }}
            </small>
        </div>
        <div>
            @if(auth()->user()->can('tickets.editar.todos') || $ticket->user_id === auth()->id())
                <a href="{{ route('helpdesk.tickets.edit', $ticket) }}"
                   class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endif
            <a href="{{ route('helpdesk.tickets.index') }}"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>
    </div>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @php
        $clsPrioridad   = Ticket::coloresPrioridad()[$ticket->prioridad] ?? 'secondary';
        $clsSeguimiento = Ticket::coloresSeguimiento()[$ticket->seguimiento] ?? 'secondary';
        $lblSeguimiento = Ticket::etiquetasSeguimiento()[$ticket->seguimiento] ?? $ticket->seguimiento;
    @endphp

    <div class="row">

        {{-- Columna izquierda: datos del ticket --}}
        <div class="col-md-4">

            {{-- Info general --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Datos del ticket</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <tr><th>Folio</th>
                            <td><strong>{{ $ticket->folio }}</strong></td></tr>
                        <tr><th>Solicitante</th>
                            <td>{{ $ticket->solicitante?->name }}</td></tr>
                        <tr><th>Departamento</th>
                            <td>{{ $ticket->solicitante?->puesto?->nombre ?? '—' }}</td></tr>
                        <tr><th>Tipo de falla</th>
                            <td>
                                <span class="badge badge-{{ $ticket->tipoFalla?->color ?? 'secondary' }}">
                                    {{ $ticket->tipoFalla?->nombre ?? '—' }}
                                </span>
                            </td></tr>
                        <tr><th>Categoría</th>
                            <td>{{ $ticket->categoriaServicio?->nombre ?? '—' }}</td></tr>
                        <tr><th>Prioridad</th>
                            <td>
                                <span class="badge badge-{{ $clsPrioridad }}">
                                    {{ ucfirst($ticket->prioridad) }}
                                </span>
                            </td></tr>
                        <tr><th>Estado</th>
                            <td>
                                <span class="badge badge-{{ $clsSeguimiento }}">
                                    {{ $lblSeguimiento }}
                                </span>
                            </td></tr>
                        <tr><th>Fecha registro</th>
                            <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td></tr>
                        @if($ticket->fecha_cierre)
                            <tr><th>Fecha cierre</th>
                                <td>{{ $ticket->fecha_cierre->format('d/m/Y H:i') }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Técnicos asignados --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-cog mr-1"></i> Técnicos asignados
                    </h3>
                </div>
                <div class="card-body">
                    @forelse($ticket->tecnicos as $tec)
                        <div class="d-flex align-items-center mb-2">
                            @if($tec->avatar)
                                <img src="{{ asset('storage/'.$tec->avatar) }}"
                                     class="img-circle mr-2"
                                     style="width:28px;height:28px;object-fit:cover">
                            @else
                                <div class="img-circle bg-secondary mr-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px">
                                    <i class="fas fa-user text-white" style="font-size:12px"></i>
                                </div>
                            @endif
                            <span>{{ $tec->name }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Sin asignar</p>
                    @endforelse
                </div>
            </div>

            {{-- Formulario de asignación (solo admin/coordinador) --}}
            @can('tickets.asignar')
                @if($ticket->seguimiento !== 'finalizado')
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-plus mr-1"></i> Asignar técnico(s)
                            </h3>
                        </div>
                        <form action="{{ route('helpdesk.tickets.asignar', $ticket) }}"
                              method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group mb-0">
                                    <select name="tecnicos[]" class="form-control" multiple>
                                        @foreach($tecnicos as $tec)
                                            <option value="{{ $tec->id }}"
                                                {{ $ticket->tecnicos->contains($tec->id) ? 'selected' : '' }}>
                                                {{ $tec->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        Mantén <kbd>Ctrl</kbd> para seleccionar varios
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning btn-sm btn-block">
                                    <i class="fas fa-save"></i> Guardar asignación
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @endcan

            {{-- Calificación --}}
            @if($ticket->seguimiento === 'finalizado' && $ticket->user_id === auth()->id())
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-star mr-1"></i> Calificar atención
                        </h3>
                    </div>
                    @if($ticket->calificacion > 0)
                        <div class="card-body text-center">
                            <div class="mb-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $ticket->calificacion ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                            </div>
                            <small class="text-muted">
                                {{ $ticket->comentario_calificacion ?? 'Sin comentario' }}
                            </small>
                        </div>
                    @else
                        <form action="{{ route('helpdesk.tickets.calificar', $ticket) }}"
                              method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group text-center">
                                    <label>Tu calificación</label>
                                    <div class="d-flex justify-content-center gap-2 mb-2"
                                         id="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star fa-2x text-muted star-btn"
                                               data-val="{{ $i }}"
                                               style="cursor:pointer"></i>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="calificacion" id="cal-val" value="0">
                                </div>
                                <div class="form-group">
                                    <textarea name="comentario_calificacion"
                                              class="form-control form-control-sm"
                                              rows="2"
                                              placeholder="Comentario opcional..."></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success btn-sm btn-block">
                                    <i class="fas fa-paper-plane"></i> Enviar calificación
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            @endif

        </div>

        {{-- Columna derecha: descripción + seguimiento --}}
        <div class="col-md-8">

            {{-- Descripción --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt mr-1"></i> Descripción
                    </h3>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $ticket->descripcion }}</p>

                    @if($ticket->evidencia)
                        <div class="mt-3">
                            <strong>Evidencia adjunta:</strong>
                            @php
                                $ext = pathinfo($ticket->evidencia, PATHINFO_EXTENSION);
                                $url = asset('storage/'.$ticket->evidencia);
                            @endphp
                            @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                                <div class="mt-2">
                                    <img src="{{ $url }}"
                                         class="img-fluid rounded"
                                         style="max-height:200px">
                                </div>
                            @else
                                <a href="{{ $url }}" target="_blank"
                                   class="btn btn-outline-secondary btn-sm mt-2">
                                    <i class="fas fa-paperclip"></i>
                                    Ver archivo adjunto
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Resolución --}}
            @if($ticket->resolucion)
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-check-circle mr-1"></i> Resolución
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $ticket->resolucion }}</p>
                    </div>
                </div>
            @endif

            {{-- Actualizar seguimiento (solo admin/coordinador/auxiliar) --}}
            @can('tickets.asignar')
                @if($ticket->seguimiento !== 'finalizado')
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sync mr-1"></i> Actualizar seguimiento
                            </h3>
                        </div>
                        <form action="{{ route('helpdesk.tickets.seguimiento', $ticket) }}"
                              method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nuevo estado</label>
                                            <select name="estado" class="form-control" required>
                                                @foreach(Ticket::etiquetasSeguimiento() as $key => $label)
                                                    <option value="{{ $key }}"
                                                        {{ $ticket->seguimiento == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" id="resolucion-group" style="display:none">
                                            <label>Descripción de resolución</label>
                                            <textarea name="resolucion" class="form-control"
                                                      rows="2" placeholder="¿Cómo se resolvió?"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Comentario</label>
                                    <input type="text" name="comentario"
                                           class="form-control"
                                           placeholder="Nota del seguimiento (opcional)">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="fas fa-save"></i> Guardar seguimiento
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @endcan

            {{-- Historial de seguimiento --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-1"></i> Historial de seguimiento
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="timeline timeline-inverse p-3">
                        @forelse($ticket->seguimientos as $seg)
                            @php
                                $cls = Ticket::coloresSeguimiento()[$seg->estado] ?? 'secondary';
                                $lbl = Ticket::etiquetasSeguimiento()[$seg->estado] ?? $seg->estado;
                            @endphp
                            <div class="time-label">
                                <span class="badge badge-{{ $cls }}">{{ $lbl }}</span>
                            </div>
                            <div>
                                <i class="fas fa-user bg-info"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fas fa-clock"></i>
                                        {{ $seg->created_at->format('d/m/Y H:i') }}
                                    </span>
                                    <h3 class="timeline-header">
                                        {{ $seg->usuario?->name ?? '—' }}
                                    </h3>
                                    @if($seg->comentario)
                                        <div class="timeline-body">
                                            {{ $seg->comentario }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                Sin historial de seguimiento.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

@stop

@section('js')
<script>
// Estrellas de calificación
document.querySelectorAll('.star-btn').forEach(function(star) {
    star.addEventListener('click', function() {
        const val = parseInt(this.dataset.val);
        document.getElementById('cal-val').value = val;
        document.querySelectorAll('.star-btn').forEach(function(s, i) {
            s.classList.toggle('text-warning', i < val);
            s.classList.toggle('text-muted', i >= val);
        });
    });
});

// Mostrar campo resolución cuando se selecciona "Finalizado"
const estadoSelect = document.querySelector('select[name="estado"]');
if (estadoSelect) {
    estadoSelect.addEventListener('change', function() {
        const resGroup = document.getElementById('resolucion-group');
        if (resGroup) {
            resGroup.style.display = this.value === 'finalizado' ? 'block' : 'none';
        }
    });
}
</script>
@stop