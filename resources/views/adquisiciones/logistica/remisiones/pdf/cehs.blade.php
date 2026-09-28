<!DOCTYPE html>
<html lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Remisión {{ $r->folio }}</title>
<style>
    @page { margin: 30px 45px 70px 45px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #000; }
    table { border-collapse: collapse; }
    #marca { position: fixed; top: 330px; left: 0; right: 0; text-align: center; }
    #pie { position: fixed; bottom: -48px; left: 0; right: 0; text-align: center; font-size: 11px; color: #0d4c8a; font-weight: bold; line-height: 1.6; }
    .info, .partidas { width: 100%; }
    .info td, .partidas th, .partidas td { border: 1px solid #000; padding: 6px; font-size: 11px; }
    .lbl { background: #e8edf2; font-weight: bold; text-align: center; width: 150px; }
    .dms { background: #e8edf2; font-weight: bold; text-align: center; width: 50px; }
    .partidas { margin-top: 16px; }
    .partidas thead { display: table-header-group; }
    .partidas tr { page-break-inside: avoid; }
    .partidas th { background: #e8edf2; font-weight: bold; }
    .partidas td { vertical-align: top; }
    .c { text-align: center; }
    .bloque-final { page-break-inside: avoid; }
    .titulo { text-align: center; margin-top: 22px; font-weight: bold; font-size: 12px; }
    .caja { width: 100%; margin-top: 8px; }
    .caja td { border: 1px solid #000; height: 55px; text-align: center; font-weight: bold; font-size: 12px; }
    .obs { width: 100%; margin-top: 10px; }
    .obs td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
    .sello { margin: 14px auto 0 auto; }
    .sello td { border: 1px solid #000; width: 280px; height: 110px; text-align: center; vertical-align: middle; color: #999; font-weight: bold; font-size: 13px; }
</style>
</head>
<body>
    @if (! empty($logos['marca_agua']))
        <div id="marca"><img src="{{ $logos['marca_agua'] }}" style="width: 260px;"></div>
    @endif

    <div id="pie">
        <div>Tel: {{ $f['telefono'] }}</div>
        <div>{{ $f['email'] }}</div>
    </div>

    @if (! empty($logos['encabezado']))
        <img src="{{ $logos['encabezado'] }}" style="width: 100%; margin-bottom: 12px;">
    @endif

    <table class="info">
        <tr>
            <td class="lbl">NÚMERO DE REMISIÓN</td>
            <td>{{ $r->num_remision ?: $r->folio }}</td>
            <td class="dms">DÍA</td>
            <td class="dms">MES</td>
            <td class="dms">AÑO</td>
        </tr>
        <tr>
            <td class="lbl">REFERENCIA</td>
            <td>{{ $r->referencia }}</td>
            <td class="c">{{ $dia }}</td>
            <td class="c">{{ $mes }}</td>
            <td class="c">{{ $anio }}</td>
        </tr>
        <tr>
            <td class="lbl">CLIENTE</td>
            <td colspan="4">{{ $cliente }}</td>
        </tr>
        <tr>
            <td class="lbl">RESPONSABLE DE ENTREGA</td>
            <td colspan="3">{{ $entrega }}</td>
            <td class="c" style="color: #999; font-style: italic;">FIRMA</td>
        </tr>
    </table>

    <table class="partidas">
        <thead>
            <tr>
                <th style="width: 55px;">NO. PARTIDA</th>
                <th>DESCRIPCIÓN</th>
                <th style="width: 65px;">CANTIDAD</th>
                <th style="width: 70px;">U/M</th>
                <th style="width: 150px;">OBSERVACIONES</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($partidas as $p)
                <tr>
                    <td class="c">{{ $p['numero'] }}</td>
                    <td>{!! nl2br(e($p['descripcion'])) !!}</td>
                    <td class="c">{{ $p['cantidad'] }}</td>
                    <td class="c">{{ $p['unidad'] }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="bloque-final">
        <div class="titulo">RECIBÍ DE CONFORMIDAD</div>
        <table class="caja">
            <tr><td>{{ $r->recibe_nombre }}</td></tr>
        </table>

        @if ($r->observaciones)
            <table class="obs">
                <tr><td style="background: #e8edf2; font-weight: bold;">OBSERVACIONES</td></tr>
                <tr><td>{!! nl2br(e($r->observaciones)) !!}</td></tr>
            </table>
        @endif

        <table class="sello">
            <tr><td>SELLO</td></tr>
        </table>
    </div>
</body>
</html>