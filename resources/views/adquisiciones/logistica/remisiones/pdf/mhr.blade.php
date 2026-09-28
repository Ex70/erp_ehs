<!DOCTYPE html>
<html lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Remisión {{ $r->folio }}</title>
<style>
    @page { margin: 30px 45px 40px 45px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #000; }
    table { border-collapse: collapse; }
    .encabezado { width: 100%; }
    .encabezado td { vertical-align: middle; }
    .info-emp { text-align: center; font-size: 12px; line-height: 1.5; }
    .info-emp .titulo { font-weight: bold; font-size: 18px; }
    .raya { border-bottom: 6px solid #00b050; margin-top: 6px; }
    .info { width: 100%; margin-top: 24px; }
    .info td { border: 1px solid #000; padding: 7px 10px; font-size: 12px; font-weight: bold; }
    .info .lbl { width: 180px; }
    .partidas { width: 100%; margin-top: 18px; }
    .partidas thead { display: table-header-group; }
    .partidas tr { page-break-inside: avoid; }
    .partidas th { border: 1px solid #000; padding: 8px; font-size: 12px; background: #92d050; text-align: center; }
    .partidas td { border: 1px solid #000; padding: 8px; font-size: 11px; text-align: center; vertical-align: middle; }
    .bloque-final { page-break-inside: avoid; }
    .nota-titulo { text-align: center; font-weight: bold; margin-top: 22px; font-size: 13px; }
    .nota { width: 100%; margin-top: 8px; }
    .nota td { border: 1px solid #000; height: 50px; padding: 8px; font-size: 11px; vertical-align: top; }
    .firmas { margin: 30px auto 0 auto; }
    .firmas td { border: 1px solid #000; width: 200px; text-align: center; font-size: 13px; font-weight: bold; padding: 10px; }
    .firmas .espacio { height: 80px; vertical-align: middle; }
</style>
</head>
<body>
    <table class="encabezado">
        <tr>
            <td style="width: 130px;">
                @if (! empty($logos['logo']))
                    <img src="{{ $logos['logo'] }}" style="width: 120px;">
                @endif
            </td>
            <td class="info-emp">
                <div class="titulo">{{ $f['razon_social'] }}</div>
                <div>PROVEEDORA DE BIENES Y SERVICIOS</div>
                <div>R.F.C: {{ $f['rfc'] }}</div>
                <div>{{ $f['direccion'] }}</div>
                <div>Correo Electrónico: {{ $f['email'] }}</div>
                <div>TELS: {{ $f['telefono'] }}</div>
            </td>
            <td style="width: 60px;"></td>
        </tr>
    </table>
    <div class="raya"></div>

    <table class="info">
        <tr><td class="lbl">Número de remisión</td><td>{{ $r->folio }}</td></tr>
        <tr><td class="lbl">Cliente</td><td>{{ $cliente }}</td></tr>
        <tr><td class="lbl">Fecha:</td><td>{{ $fechaLetra }}</td></tr>
        <tr><td class="lbl">Referencia</td><td>{{ $r->referencia }}</td></tr>
    </table>

    <table class="partidas">
        <thead>
            <tr>
                <th style="width: 70px;">No. Partida</th>
                <th>DESCRIPCIÓN</th>
                <th style="width: 70px;">Cantidad</th>
                <th style="width: 70px;">U/M</th>
                <th style="width: 175px;">IMAGEN ILUSTRATIVA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($partidas as $p)
                <tr>
                    <td>{{ $p['numero'] }}</td>
                    <td>{!! nl2br(e($p['descripcion'])) !!}</td>
                    <td>{{ $p['cantidad'] }}</td>
                    <td>{{ $p['unidad'] }}</td>
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
        <div class="nota-titulo">Nota</div>
        <table class="nota">
            <tr><td>{!! nl2br(e($r->observaciones)) !!}</td></tr>
        </table>

        <table class="firmas">
            <tr>
                <td>RECIBE</td>
                <td>ENTREGA</td>
            </tr>
            <tr>
                <td class="espacio">{{ $r->recibe_nombre }}</td>
                <td class="espacio">{{ $entrega }}</td>
            </tr>
        </table>
    </div>
</body>
</html>