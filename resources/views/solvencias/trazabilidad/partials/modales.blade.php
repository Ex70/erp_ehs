{{-- Modales del expediente. Todos son formularios POST normales (sin AJAX). --}}

@if($a['enviar'] && $a['candidatos'])
    @php $verboSig = $e->esDevuelta() ? 'la devolvió y la revisará de nuevo' : mb_strtolower($e->recorrido[$e->siguienteEtapaIdx()]['verbo'] ?? ''); @endphp
    <div class="modal fade" id="solModalEnviar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('sol.expedientes.enviar', $e) }}" class="modal-content" data-sol-once>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ $e->esDevuelta() ? 'Reenviar' : 'Enviar' }} <span class="sol-mono">{{ $e->folio }}</span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    @if(count($a['candidatos']) === 1)
                        @php $id = array_key_first($a['candidatos']); @endphp
                        <input type="hidden" name="para_id" value="{{ $id }}">
                        <div class="sol-note">
                            Se enviará a <b>{{ $a['candidatos'][$id] }}</b>, quien <b>{{ $verboSig }}</b>.<br>
                            Quedará <b>Sin acuse</b> a su nombre hasta que confirme que la recibió.
                        </div>
                    @else
                        <div class="sol-note mb-3">
                            Esta etapa ({{ $verboSig }}) la puede hacer cualquiera de {{ count($a['candidatos']) }} personas.
                            Elige a quién se la entregas; quedará <b>Sin acuse</b> a su nombre hasta que confirme.
                        </div>
                        <div class="form-group">
                            <label>¿A quién se la entregas?</label>
                            <select name="para_id" class="form-control" required>
                                @foreach($a['candidatos'] as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="form-group mb-0 mt-3">
                        <label>Comentario <small class="text-muted">(opcional)</small></label>
                        <textarea name="comentario" rows="2" maxlength="500" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sol">Enviar</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if($a['autorizar'])
    @php $exentos = $e->exentos(); @endphp
    <div class="modal fade" id="solModalAutorizar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('sol.expedientes.autorizar', $e) }}" class="modal-content" data-sol-once>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Autorizar <span class="sol-mono">{{ $e->folio }}</span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="sol-sub">{{ $e->monto_formateado }} a {{ $e->proveedor }} · {{ $e->descripcion }}</div>
                    @if($exentos->isNotEmpty())
                        <div class="sol-warnbox">
                            ⚠ {{ $exentos->count() }} documento(s) sin archivo con observación:
                            @foreach($exentos as $x)
                                <div>{{ $x->etiqueta }} — “{{ $x->observacion }}”</div>
                            @endforeach
                        </div>
                    @endif
                    <div class="sol-note mt-3">Con la autorización pasa a Contabilidad y Finanzas, que consulta el formato original y registra el pago (comprobante y fecha).</div>
                    <div class="form-group mb-0 mt-3">
                        <label>Comentario <small class="text-muted">(opcional)</small></label>
                        <textarea name="comentario" rows="2" maxlength="500" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sol">Autorizar y cerrar</button>
                </div>
            </form>
        </div>
    </div>
@endif

@foreach(['devolver' => $a['devolver'], 'cancelar' => $a['cancelar']] as $tipo => $permitido)
    @continue(! $permitido)
    @php
        $t = $tipo === 'devolver'
            ? ['h' => 'Devolver', 'd' => 'Regresa a ' . $e->nombreDe($e->creador_id) . ' para corrección. El motivo queda en la bitácora.', 'b' => 'Devolver']
            : ['h' => 'Cancelar', 'd' => 'La cancelación es definitiva; el expediente conserva su folio y toda su bitácora.', 'b' => 'Cancelar solvencia'];
    @endphp
    <div class="modal fade" id="solModal{{ ucfirst($tipo) }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('sol.expedientes.' . $tipo, $e) }}" class="modal-content" data-sol-once>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t['h'] }} <span class="sol-mono">{{ $e->folio }}</span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="sol-sub mb-3">{{ $t['d'] }}</div>
                    <div class="form-group mb-0">
                        <label>Motivo <small class="text-muted">(obligatorio, mínimo {{ config('solvencias.min_motivo') }} caracteres)</small></label>
                        <textarea name="motivo" rows="3" maxlength="1000" class="form-control" required minlength="{{ config('solvencias.min_motivo') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-outline-danger">{{ $t['b'] }}</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

@if($a['editable'])
    {{-- No se anexa (el tipo lo fija solvencias.js) --}}
    <div class="modal fade" id="solModalExceptuar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('sol.expedientes.documentos.exceptuar', $e) }}" class="modal-content" data-sol-once>
                @csrf
                <input type="hidden" name="tipo" value="">
                <div class="modal-header">
                    <h5 class="modal-title">No anexar: <span data-exc-nombre></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="sol-sub mb-3">
                        {{ $e->folio }} · Solo procede con un argumento válido. La observación queda en el expediente y en la
                        bitácora, y la verán quienes revisan, validan, autorizan y Contabilidad.
                    </div>
                    <div class="form-group mb-0">
                        <label>Observación <small class="text-muted">(obligatoria, mínimo {{ config('solvencias.min_observacion') }} caracteres)</small></label>
                        <textarea name="observacion" rows="3" maxlength="1000" class="form-control" required minlength="{{ config('solvencias.min_observacion') }}"
                                  placeholder="Ej. Compra interna para uso de la empresa; no existe cotización enviada a cliente."></textarea>
                        <small class="text-muted"><span data-exc-contador>0</span> / {{ config('solvencias.min_observacion') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sol-amber">Registrar sin archivo</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Corregir datos --}}
    <div class="modal fade" id="solModalCorregir" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" action="{{ route('sol.expedientes.update', $e) }}" class="modal-content" data-sol-once>
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Corregir datos · <span class="sol-mono">{{ $e->folio }}</span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="sol-sub mb-3">Cada cambio queda en la bitácora con el valor anterior y el nuevo.</div>
                    @include('solvencias.trazabilidad.partials.campos', ['datos' => $e, 'flujos' => collect()])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sol">Guardar corrección</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if($a['pagar'])
    @include('solvencias.trazabilidad.partials.modal-pago')
@endif

@include('solvencias.trazabilidad.partials.visor-excel')
