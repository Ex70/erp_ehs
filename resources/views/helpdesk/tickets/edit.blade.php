@php
    use App\Models\Ticket;
@endphp
@extends('adminlte::page')
@section('title', 'Editar ticket — ' . $ticket->folio)

@section('content_header')
    <h1>Editar ticket — {{ $ticket->folio }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos de la incidencia</h3>
        </div>
        <form action="{{ route('helpdesk.tickets.update', $ticket) }}"
              method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Solicitante</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $ticket->solicitante?->name }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Departamento</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $ticket->solicitante?->puesto?->nombre ?? '—' }}"
                                   readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo de falla <span class="text-danger">*</span></label>
                            <select name="tipo_falla_id" class="form-control" required>
                                @foreach($tiposFalla as $t)
                                    <option value="{{ $t->id }}"
                                        {{ $ticket->tipo_falla_id == $t->id ? 'selected' : '' }}>
                                        {{ $t->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Prioridad <span class="text-danger">*</span></label>
                            <select name="prioridad" class="form-control" required>
                                <option value="baja"    {{ $ticket->prioridad == 'baja'    ? 'selected' : '' }}>Baja</option>
                                <option value="media"   {{ $ticket->prioridad == 'media'   ? 'selected' : '' }}>Media</option>
                                <option value="alta"    {{ $ticket->prioridad == 'alta'    ? 'selected' : '' }}>Alta</option>
                                <option value="urgente" {{ $ticket->prioridad == 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Categoría de servicio</label>
                            <select name="categoria_servicio_id" class="form-control">
                                <option value="">Selecciona...</option>
                                @foreach($categorias as $c)
                                    <option value="{{ $c->id }}"
                                        {{ $ticket->categoria_servicio_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @can('tickets.editar.todos')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado del ticket</label>
                                <select name="seguimiento" class="form-control">
                                    @foreach(Ticket::etiquetasSeguimiento() as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ $ticket->seguimiento == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endcan
                </div>

                @can('tickets.editar.todos')
                    <div class="form-group">
                        <label>Resolución</label>
                        <textarea name="resolucion" class="form-control" rows="2"
                                  placeholder="Descripción de cómo se resolvió...">{{ $ticket->resolucion }}</textarea>
                    </div>
                @endcan

                <div class="form-group">
                    <label>Descripción <span class="text-danger">*</span></label>
                    <textarea name="descripcion" class="form-control" rows="4"
                              minlength="10" required>{{ $ticket->descripcion }}</textarea>
                </div>

                <div class="form-group">
                    <label>Evidencia</label>
                    @if($ticket->evidencia)
                        <div class="mb-2">
                            <a href="{{ asset('storage/'.$ticket->evidencia) }}"
                               target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-paperclip"></i> Ver evidencia actual
                            </a>
                        </div>
                    @endif
                    <input type="file" name="evidencia" class="form-control-file"
                           accept="image/*,.pdf,.doc,.docx">
                    <small class="text-muted">Deja vacío para mantener la evidencia actual.</small>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('helpdesk.tickets.show', $ticket) }}"
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar ticket
                </button>
            </div>
        </form>
    </div>
@stop