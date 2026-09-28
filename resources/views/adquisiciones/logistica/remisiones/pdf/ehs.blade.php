<!DOCTYPE html>
<html lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Remisión {{ $r->folio }}</title>
<style>
    @page { margin: 34px 45px 40px 45px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #000; }
    table { border-collapse: collapse; }
    .encabezado { width: 100%; }
    .encabezado td { vertical-align: middle; }
    .info-emp { text-align: center; font-size: 11px; line-height: 1.5; font-weight: bold; }
    .info-emp .titulo { color: #ff5722; font-size: 14px; }
    .info-emp .rfc { color: #ff5722; }
    .raya { border-bottom: 3px solid #2196f3; margin: 8px 0 16px 0; }
    .fecha, .info, .partidas, .firmas, .notas { width: 100%; }
    .fecha td, .info td { border: 1px solid #000; padding: 6px; font-size: 11px; }
    .lbl { background: #e8edf2; font-weight: bold; text-align: center; width: 130px; }
    .partidas thead { display: table-header-group; }
    .partidas tr { page-break-inside: avoid; }
    .partidas th { background: #e8edf2; border: 1px solid #000; padding: 6px; font-size: 11px; text-align: center; }
    .partidas td { border: 1px solid #000; padding: 6px; font-size: 11px; text-align: center; vertical-align: middle; }
    .partidas td.desc { text-align: left; vertical-align: top; }
    .bloque-final { page-break-inside: avoid; }
    .firmas td, .notas td { border: 1px solid #000; padding: 8px; font-size: 11px; vertical-align: top; }
    .head { background: #e8edf2; font-weight: bold; text-align: center; }
    .formulario { line-height: 2; }
    .sello { text-align: center; font-size: 24px; color: #bbb; letter-spacing: 3px; padding: 26px 0 10px 0; }
</style>
</head>
<body>
    <table class="encabezado">
        <tr>
            <td style="width: 100px;">
                @if (! empty($logos['logo']))
                    <img src="{{ $logos['logo'] }}" style="width: 90px;">
                @endif
            </td>
            <td class="info-emp">
                <div class="titulo">{{ $f['razon_social'] }}</div>
                <div class="rfc">{{ $f['rfc'] }}</div>
                <div>{{ $f['direccion'] }}</div>
                <div>TEL: {{ $f['telefono'] }}</div>
                <div style="font-weight: normal;">Correo: {{ $f['email'] }}</div>
            </td>
            <td style="width: 100px;"></td>
        </tr>
    </table>
    <div class="raya"></div>

    <table class="fecha">
        <tr>
            <td style="border: 0;"></td>
            <td class="lbl" style="width: 80px;">FECHA</td>
            <td style="width: 130px; text-align: center; font-weight: bold;">{{ $fechaCorta }}</td>
        </tr>
    </table>
    <table class="info">
        <tr><td class="lbl">CLIENTE</td><td><b>{{ $cliente }}</b></td></tr>
        <tr><td class="lbl">COTIZACIÓN</td><td><b>{{ $r->folio }}</b></td></tr>
        <tr><td class="lbl">REFERENCIA</td><td><b>{{ $r->referencia }}</b></td></tr>
    </table>

    <table class="partidas">
        <thead>
            <tr>
                <th style="width: 55px;">NO. PARTIDA</th>
                <th style="width: 60px;">CANTIDAD</th>
                <th style="width: 80px;">UNIDAD DE MEDIDA</th>
                <th>DESCRIPCIÓN</th>
                <th style="width: 130px;">IMAGEN</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($partidas as $p)
                <tr>
                    <td>{{ $p['numero'] }}</td>
                    <td>{{ $p['cantidad'] }}</td>
                    <td>{{ $p['unidad'] }}</td>
                    <td class="desc">{!! nl2br(e($p['descripcion'])) !!}</td>
                    <td>
                        @if ($p['imagen'])
                            <img src="{{ $p['imagen']['src'] }}" style="width: {{ $p['imagen']['w'] }}px; height: {{ $p['imagen']['h'] }}px;">
                        @else
                            &nbsp;
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="bloque-final">
        <table class="firmas">
            <tr>
                <td class="head" style="width: 50%;">RECIBE</td>
                <td class="head" style="width: 50%;">ENTREGA</td>
            </tr>
            <tr>
                <td class="formulario">
                    <b>NOMBRE:</b> {{ $r->recibe_nombre ?: '____________________' }}<br>
                    <b>FECHA:</b> {{ $fechaCorta }}<br>
                    <b>FIRMA:</b> _______________________<br>
                    <b>CARGO:</b> {{ $r->recibe_cargo ?: '______________________' }}
                    <div class="sello">SELLO</div>
                </td>
                <td class="formulario">
                    <b>NOMBRE:</b> {{ $entrega ?: '____________________' }}<br>
                    <b>FECHA:</b> {{ $fechaCorta }}<br>
                    <b>FIRMA:</b> _______________________
                </td>
            </tr>
        </table>

        <table class="notas">
            <tr><td class="head">NOTAS</td></tr>
            <tr>
                <td>{!! nl2br(e($r->observaciones ?: '*Favor de colocar nombre de la persona que recibe, puesto de la persona y sello de la dependencia.')) !!}</td>
            </tr>
        </table>
    </div>
</body>
</html>