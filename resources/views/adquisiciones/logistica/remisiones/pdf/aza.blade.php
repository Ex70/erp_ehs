<!DOCTYPE html>
<html lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Remisión {{ $r->folio }}</title>
<style>
    @page { margin: 36px 55px 85px 55px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #000; }
    table { border-collapse: collapse; }
    #marca { position: fixed; top: 290px; left: 0; right: 0; text-align: center; }
    #pie { position: fixed; bottom: -60px; left: 0; right: 0; text-align: center; font-size: 12px; line-height: 1.6; }
    #pie .paginas:after { content: counter(page) " de " counter(pages); }
    .encabezado { width: 100%; }
    .encabezado td { vertical-align: top; }
    .encabezado .info { text-align: right; font-size: 11px; line-height: 1.55; }
    .campos { margin-top: 30px; padding-left: 14px; }
    .campo { margin: 9px 0; font-size: 14px; font-weight: bold; }
    .partidas { width: 100%; margin-top: 20px; }
    .partidas thead { display: table-header-group; }
    .partidas tr { page-break-inside: avoid; }
    .partidas th { color: #e85d2f; border: 1px solid #000; padding: 7px; font-size: 12px; text-align: center; }
    .partidas td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; vertical-align: top; }
    .c { text-align: center; }
    .b { font-weight: bold; }
    .obs { width: 100%; }
    .obs td { border: 1px solid #000; padding: 7px 8px; font-size: 11px; vertical-align: top; }
    .firmas { width: 100%; margin-top: 110px; page-break-inside: avoid; }
    .firmas td { width: 50%; text-align: center; font-size: 13px; }
    .linea { border-top: 1px solid #000; width: 220px; margin: 0 auto 6px auto; }
</style>
</head>
<body>
    @if (! empty($logos['marca_agua']))
        <div id="marca"><img src="{{ $logos['marca_agua'] }}" style="width: 470px;"></div>
    @endif

    <div id="pie">
        <div>Página <span class="paginas"></span></div>
        <div>Tel. {{ $f['telefono'] }} | {{ $f['email'] }}</div>
    </div>

    <table class="encabezado">
        <tr>
            <td style="width: 45%;">
                @if (! empty($logos['logo']))
                    <img src="{{ $logos['logo'] }}" style="width: 185px;">
                @endif
            </td>
            <td class="info">
                {{ $f['razon_social'] }}<br>
                RFC <b>{{ $f['rfc'] }}</b><br>
                Tel: {{ $f['telefono'] }}<br>
                {{ $f['direccion'] }}<br>
                Correo: <span style="color: #1155cc; text-decoration: underline;">{{ $f['email'] }}</span>
            </td>
        </tr>
    </table>

    <div class="campos">
        <div class="campo">RECIBO: {{ $r->folio }}</div>
        <div class="campo">CLIENTE: {{ $cliente }}</div>
        <div class="campo">REFERENCIA: {{ $r->referencia }}</div>
        <div class="campo">FECHA: {{ $fechaLetra }}</div>
    </div>

    <table class="partidas">
        <thead>
            <tr>
                <th style="width: 70px;">PARTIDA</th>
                <th>DESCRIPCIÓN</th>
                <th style="width: 105px;">UNIDAD DE MEDIDA</th>
                <th style="width: 85px;">CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($partidas as $p)
                <tr>
                    <td class="c b">{{ $p['numero'] }}</td>
                    <td>{!! nl2br(e($p['descripcion'])) !!}</td>
                    <td class="c">{{ $p['unidad'] }}</td>
                    <td class="c">{{ $p['cantidad'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="obs">
        <tr><td class="b" style="border-top: 0;">OBSERVACIONES:</td></tr>
        <tr><td style="height: 60px;">{!! nl2br(e($r->observaciones)) !!}</td></tr>
    </table>

    <table class="firmas">
        <tr>
            <td><div class="linea"></div>Entrega</td>
            <td><div class="linea"></div>Recibe</td>
        </tr>
    </table>
</body>
</html>