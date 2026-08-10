{{-- ══ CARRUSEL DE PORTADA ══ --}}
<div class="cultura-hero">
    <div class="cultura-slides" id="culturaSlidesInner">
        @forelse ($slides as $slide)
            <div class="cultura-slide" data-duracion="{{ $slide->duracion }}">
                @if ($slide->imagen)
                    <img class="cultura-slide-img" src="{{ $slide->imagen_url }}" alt="{{ $slide->titulo }}">
                @else
                    <div class="cultura-slide-placeholder" style="background: {{ $slide->color ?: '#1a0a0a' }}">
                        {{ $slide->icono ?: '🏢' }}
                    </div>
                @endif

                <div class="cultura-slide-caption">
                    <h3>{{ $slide->titulo }}</h3>
                    <p>{{ $slide->descripcion }}</p>
                </div>

                @can('cultura.editar.todos')
                    <div class="cultura-slide-admin">
                        <button type="button" class="btn btn-dark btn-xs js-editar-slide"
                                data-id="{{ $slide->id }}"
                                data-titulo="{{ $slide->titulo }}"
                                data-descripcion="{{ $slide->descripcion }}"
                                data-icono="{{ $slide->icono }}"
                                data-color="{{ $slide->color }}"
                                data-orden="{{ $slide->orden }}"
                                data-duracion="{{ $slide->duracion }}">✏️ Editar</button>
                        <button type="button" class="btn btn-danger btn-xs js-eliminar-slide"
                                data-id="{{ $slide->id }}">🗑️</button>
                    </div>
                @endcan
            </div>
        @empty
            <div class="cultura-slide" data-duracion="5">
                <div class="cultura-slide-placeholder" style="background:#1a0a0a">🦅</div>
                <div class="cultura-slide-caption">
                    <h3>Grupo Quetzalcóatl</h3>
                    <p>Agrega imágenes al carrusel desde el botón «Agregar Imagen»</p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($slides->count() > 1)
        <button type="button" class="cultura-nav-btn cultura-prev" onclick="culturaNav(-1)" aria-label="Anterior">&lsaquo;</button>
        <button type="button" class="cultura-nav-btn cultura-next" onclick="culturaNav(1)" aria-label="Siguiente">&rsaquo;</button>
        <div class="cultura-dots">
            @foreach ($slides as $i => $s)
                <button type="button" class="cultura-dot {{ $i === 0 ? 'active' : '' }}" aria-label="Ir a la diapositiva {{ $i + 1 }}"></button>
            @endforeach
        </div>
    @endif
</div>
