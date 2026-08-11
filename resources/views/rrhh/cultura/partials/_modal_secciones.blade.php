{{-- ══ MODAL: EDITAR CONTENIDO DE CULTURA ══ --}}
<div class="modal fade" id="modalCulturaSecciones" tabindex="-1" role="dialog" aria-hidden="true"
     data-url-secciones="{{ url('rrhh/cultura/secciones') }}"
     data-url-items="{{ url('rrhh/cultura/items') }}">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title cultura-serif">✏️ Editar Contenido de Cultura</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="culturaEditorTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#ed-presentacion" data-modo="seccion" data-clave="presentacion">🏢 Presentación</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ed-mision" data-modo="seccion" data-clave="mision">🎯 Misión</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ed-vision" data-modo="seccion" data-clave="vision">🔭 Visión</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ed-valores" data-modo="lista" data-tipo="valor">💎 Valores</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ed-historia" data-modo="seccion" data-clave="historia">📖 Historia</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ed-objetivos" data-modo="lista" data-tipo="objetivo" data-clave="objetivos">🏁 Objetivos</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ed-empresas" data-modo="lista" data-tipo="empresa">🏭 Empresas</a></li>
                </ul>

                <div class="tab-content">

                    {{-- ── Secciones de texto ── --}}
                    @foreach ([
                        'presentacion' => ['Quiénes Somos', 'Texto', 130],
                        'mision'       => ['Misión', 'Texto de Misión', 110],
                        'vision'       => ['Visión', 'Texto de Visión', 110],
                        'historia'     => ['Nuestra Historia', 'Historia', 160],
                    ] as $clave => $cfg)
                        <div class="tab-pane fade {{ $clave === 'presentacion' ? 'show active' : '' }}" id="ed-{{ $clave }}">
                            <div class="form-group">
                                <label class="text-muted text-uppercase small">Título de sección</label>
                                <input type="text" class="form-control" data-campo="titulo" data-clave="{{ $clave }}"
                                       value="{{ $secciones[$clave]->titulo ?? $cfg[0] }}" maxlength="150">
                            </div>
                            <div class="form-group mb-0">
                                <label class="text-muted text-uppercase small">{{ $cfg[1] }}</label>
                                <textarea class="form-control" data-campo="texto" data-clave="{{ $clave }}"
                                          style="min-height: {{ $cfg[2] }}px" maxlength="5000">{{ $secciones[$clave]->texto ?? '' }}</textarea>
                            </div>
                        </div>
                    @endforeach

                    {{-- ── Valores ── --}}
                    <div class="tab-pane fade" id="ed-valores">
                        <div id="listaValores">
                            @foreach ($valores as $valor)
                                <div class="cultura-fila">
                                    <input type="hidden" data-campo="id" value="{{ $valor->id }}">
                                    <div class="col-icono"><input type="text" class="form-control text-center" data-campo="icono" maxlength="4" value="{{ $valor->icono }}" placeholder="💡"></div>
                                    <div class="col-titulo"><input type="text" class="form-control" data-campo="titulo" value="{{ $valor->titulo }}" placeholder="Nombre del valor"></div>
                                    <div class="col-desc"><input type="text" class="form-control" data-campo="descripcion" value="{{ $valor->descripcion }}" placeholder="Descripción"></div>
                                    <div class="col-quitar"><button type="button" class="btn btn-danger btn-sm js-quitar-fila">&times;</button></div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-agregar="valor">+ Agregar Valor</button>
                    </div>

                    {{-- ── Objetivos ── --}}
                    <div class="tab-pane fade" id="ed-objetivos">
                        <div class="form-group">
                            <label class="text-muted text-uppercase small">Título de sección</label>
                            <input type="text" class="form-control" data-campo="titulo" data-clave="objetivos"
                                   value="{{ $secciones['objetivos']->titulo ?? 'Objetivos Estratégicos' }}" maxlength="150">
                        </div>
                        <div id="listaObjetivos">
                            @foreach ($objetivos as $objetivo)
                                <div class="cultura-fila">
                                    <input type="hidden" data-campo="id" value="{{ $objetivo->id }}">
                                    <div class="col-titulo" style="flex:1 1 100%"><input type="text" class="form-control" data-campo="titulo" value="{{ $objetivo->titulo }}" placeholder="Objetivo estratégico"></div>
                                    <div class="col-quitar"><button type="button" class="btn btn-danger btn-sm js-quitar-fila">&times;</button></div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-agregar="objetivo">+ Agregar Objetivo</button>
                    </div>

                    {{-- ── Empresas ── --}}
                    <div class="tab-pane fade" id="ed-empresas">
                        <div id="listaEmpresas">
                            @foreach ($empresas as $empresa)
                                <div class="cultura-fila">
                                    <input type="hidden" data-campo="id" value="{{ $empresa->id }}">
                                    <div class="col-icono"><input type="text" class="form-control text-center" data-campo="icono" maxlength="4" value="{{ $empresa->icono }}" placeholder="🏢"></div>
                                    <div class="col-color"><input type="text" class="form-control" data-campo="color" value="{{ $empresa->color }}" placeholder="#1a1a3a"></div>
                                    <div class="col-titulo"><input type="text" class="form-control" data-campo="titulo" value="{{ $empresa->titulo }}" placeholder="Nombre de la empresa"></div>
                                    <div class="col-desc"><input type="text" class="form-control" data-campo="descripcion" value="{{ $empresa->descripcion }}" placeholder="Descripción"></div>
                                    <div class="col-quitar"><button type="button" class="btn btn-danger btn-sm js-quitar-fila">&times;</button></div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-agregar="empresa">+ Agregar Empresa</button>
                    </div>

                </div>

                <div class="alert alert-danger mt-3 d-none" id="culturaEditorError"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCultura">💾 Guardar Cambios</button>
            </div>

        </div>
    </div>
</div>

{{-- Plantillas de fila --}}
<template id="tplFilaValor">
    <div class="cultura-fila">
        <input type="hidden" data-campo="id" value="">
        <div class="col-icono"><input type="text" class="form-control text-center" data-campo="icono" maxlength="4" value="⭐"></div>
        <div class="col-titulo"><input type="text" class="form-control" data-campo="titulo" placeholder="Nombre del valor"></div>
        <div class="col-desc"><input type="text" class="form-control" data-campo="descripcion" placeholder="Descripción"></div>
        <div class="col-quitar"><button type="button" class="btn btn-danger btn-sm js-quitar-fila">&times;</button></div>
    </div>
</template>

<template id="tplFilaObjetivo">
    <div class="cultura-fila">
        <input type="hidden" data-campo="id" value="">
        <div class="col-titulo" style="flex:1 1 100%"><input type="text" class="form-control" data-campo="titulo" placeholder="Objetivo estratégico"></div>
        <div class="col-quitar"><button type="button" class="btn btn-danger btn-sm js-quitar-fila">&times;</button></div>
    </div>
</template>

<template id="tplFilaEmpresa">
    <div class="cultura-fila">
        <input type="hidden" data-campo="id" value="">
        <div class="col-icono"><input type="text" class="form-control text-center" data-campo="icono" maxlength="4" value="🏢"></div>
        <div class="col-color"><input type="text" class="form-control" data-campo="color" placeholder="#1a1a3a"></div>
        <div class="col-titulo"><input type="text" class="form-control" data-campo="titulo" placeholder="Nombre de la empresa"></div>
        <div class="col-desc"><input type="text" class="form-control" data-campo="descripcion" placeholder="Descripción"></div>
        <div class="col-quitar"><button type="button" class="btn btn-danger btn-sm js-quitar-fila">&times;</button></div>
    </div>
</template>
