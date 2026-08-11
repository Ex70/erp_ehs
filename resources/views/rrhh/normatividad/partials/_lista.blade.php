@if ($lista->isEmpty())
    <div class="norm-vacio">
        <div class="norm-vacio-icono">📜</div>
        <div class="norm-vacio-titulo">Sin documentos</div>
        <small class="text-muted">
            @if (filled($termino))
                No hay coincidencias en esta categoría.
            @else
                Aún no se ha registrado ningún documento en {{ $cfg['nombre'] }}.
            @endif
        </small>
    </div>
@else
    @foreach ($lista as $documento)
        @php $etiqueta = $documento->etiqueta_vigencia; @endphp

        <div class="norm-card {{ $documento->activo ? '' : 'inactivo' }}">
            <div class="norm-card-icono">📄</div>

            <div class="norm-card-cuerpo">
                <div class="norm-card-titulo">
                    {{ $documento->titulo }}
                    @unless ($documento->activo)
                        <span class="badge badge-secondary ml-1">Inactivo</span>
                    @endunless
                </div>

                <div class="norm-card-meta">
                    @if ($documento->version)
                        <span>{{ $documento->version }}</span>
                    @endif
                    <span>
                        Vigencia:
                        {{ $documento->vigencia?->format('d/m/Y') ?? '—' }}
                        <span class="badge {{ $etiqueta['clase'] }} ml-1">{{ $etiqueta['texto'] }}</span>
                    </span>
                    @if ($documento->responsable)
                        <span>{{ $documento->responsable }}</span>
                    @endif
                </div>

                @if ($documento->descripcion)
                    <div class="norm-card-desc">{{ $documento->descripcion }}</div>
                @endif

                @if ($documento->tiene_archivo)
                    <div class="norm-card-archivo">
                        📎
                        <a href="{{ route('rrhh.normatividad.descargar', $documento) }}">
                            {{ $documento->archivo_nombre }}
                        </a>
                        @if ($documento->tamano_legible)
                            <span class="text-muted">({{ $documento->tamano_legible }})</span>
                        @endif
                    </div>
                @else
                    <div class="norm-card-archivo text-muted">Sin archivo adjunto</div>
                @endif

                @if ($documento->versiones->isNotEmpty())
                    <button class="btn btn-link btn-sm p-0 mt-2" type="button"
                            data-toggle="collapse" data-target="#historial-{{ $documento->id }}">
                        🕘 Historial de versiones ({{ $documento->versiones->count() }})
                    </button>

                    <div class="collapse norm-historial" id="historial-{{ $documento->id }}">
                        @foreach ($documento->versiones as $version)
                            <div class="norm-historial-item">
                                <span class="norm-historial-ver">{{ $version->version ?: 's/v' }}</span>
                                <a href="{{ route('rrhh.normatividad.versiones.descargar', $version) }}">
                                    {{ $version->archivo_nombre }}
                                </a>
                                <span class="text-muted">
                                    · Reemplazada el {{ $version->created_at->format('d/m/Y') }}
                                    @if ($version->notas)
                                        · {{ $version->notas }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="norm-card-acciones">
                @can('normatividad.editar')
                    <button type="button" class="btn btn-outline-secondary btn-sm js-editar-documento"
                            data-id="{{ $documento->id }}"
                            data-accion="{{ route('rrhh.normatividad.update', $documento) }}"
                            data-categoria="{{ $documento->categoria }}"
                            data-titulo="{{ $documento->titulo }}"
                            data-version="{{ $documento->version }}"
                            data-vigencia="{{ $documento->vigencia?->format('Y-m-d') }}"
                            data-responsable="{{ $documento->responsable }}"
                            data-descripcion="{{ $documento->descripcion }}"
                            data-activo="{{ $documento->activo ? 1 : 0 }}"
                            data-archivo="{{ $documento->archivo_nombre }}"
                            title="Editar">✏️</button>
                @endcan

                @can('normatividad.eliminar')
                    <form method="POST" action="{{ route('rrhh.normatividad.destroy', $documento) }}"
                          onsubmit="return confirm('¿Eliminar «{{ addslashes($documento->titulo) }}»? El archivo y su historial se conservan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">🗑️</button>
                    </form>
                @endcan
            </div>
        </div>
    @endforeach
@endif
