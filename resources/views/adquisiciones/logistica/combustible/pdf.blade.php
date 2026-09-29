<!DOCTYPE html>
<html lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>{{ $s->folio }}</title>
<style>
    @page { margin: 20px 24px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #000; }
    table { border-collapse: collapse; }
    .encabezado { width: 100%; }
    .encabezado td { vertical-align: middle; }
    .titulo { font-size: 24px; font-weight: bold; color: #1e3a5f; letter-spacing: 1px; }
    .empresa { font-size: 18px; font-weight: bold; color: #00b050; margin-top: 8px; }
    .folio { width: 100%; border: 2px solid #1e3a5f; }
    .folio td { text-align: center; }
    .folio .head { background: #1e3a5f; color: #fff; font-size: 11px; font-weight: bold; letter-spacing: 3px; padding: 4px; }
    .folio .fecha { font-size: 14px; font-weight: bold; color: #1e3a5f; padding: 6px; line-height: 1.3; }
    .folio .numero { background: #eef3f8; font-size: 9px; font-weight: bold; color: #1e3a5f; border-top: 1px solid #1e3a5f; padding: 3px; }
    .raya { border-bottom: 3px solid #1e3a5f; margin: 6px 0 8px 0; }
    .banner { background: #1e3a5f; color: #fff; padding: 6px; text-align: center; font-size: 13px; font-weight: bold; letter-spacing: 3px; margin-bottom: 8px; }
    .datos { width: 100%; }
    .datos td { border: 1px solid #1e3a5f; padding: 5px 9px; font-size: 11px; font-weight: bold; }
    .datos .lbl { background: #eef3f8; color: #1e3a5f; }
    .conceptos { width: 100%; margin-top: 8px; }
    .conceptos th { background: #1e3a5f; color: #fff; border: 1px solid #1e3a5f; padding: 5px 3px; font-size: 10px; }
    .conceptos td { border: 1px solid #1e3a5f; padding: 5px 4px; font-size: 10px; }
    .c { text-align: center; }
    .r { text-align: right; }
    .b { font-weight: bold; }
    .subtotal td { background: #92d050; font-weight: bold; }
    .total td { background: #00b050; color: #fff; font-weight: bold; }
    .firmas { width: 100%; margin-top: 14px; page-break-inside: avoid; }
    .firmas th { background: #1e3a5f; color: #fff; border: 1px solid #1e3a5f; padding: 6px; font-size: 11px; letter-spacing: 1px; width: 25%; }
    .firmas td { border: 1px solid #1e3a5f; height: 105px; vertical-align: bottom; text-align: center; padding: 5px; }
    .firma-nombre { border-top: 1px solid #1e3a5f; margin: 0 12px; padding-top: 4px; font-weight: bold; font-size: 10px; color: #1e3a5f; }
    .firma-cargo { font-size: 9px; margin-top: 2px; font-weight: bold; }
</style>
</head>
<body>
    <table class="encabezado">
        <tr>
            <td style="width: 170px;">
                @if ($logo)
                    <img src="{{ $logo }}" style="width: 150px;">
                @endif
            </td>
            <td style="text-align: center; padding: 0 14px;">
                <div class="titulo">SOLICITUD DE COMBUSTIBLE</div>
                <div class="empresa">{{ $empresa }}</div>
            </td>
            <td style="width: 175px;">
                <table class="folio">
                    <tr><td class="head">FOLIO</td></tr>
                    <tr>
                        <td class="fecha">
                            {{ $s->created_at->format('d/m/Y') }}<br>
                            <span style="font-size: 11px; color: #000;">{{ $s->created_at->format('H:i') }} hrs</span>
                        </td>
                    </tr>
                    <tr><td class="numero">{{ $s->folio }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="raya"></div>

    <div class="banner">SOLICITUD DE RECURSO</div>

    <table class="datos">
        <tr>
            <td class="lbl" style="width: 130px;">FECHA</td>
            <td>{{ $fechaLetra }}</td>
            <td class="lbl" style="width: 140px;">Nº COTIZACIÓN</td>
            <td>{{ $s->cotizacion }}</td>
        </tr>
        <tr>
            <td class="lbl">EMPRESA</td>
            <td style="color: #00b050;">{{ $empresa }}</td>
            <td class="lbl">MONTO SOLICITADO</td>
            <td style="font-size: 12px;">$ {{ number_format((float) $s->monto, 2) }}</td>
        </tr>
        <tr>
            <td class="lbl">CLIENTE</td>
            <td colspan="3">{{ $s->cliente }}</td>
        </tr>
    </table>

    <table class="conceptos">
        <thead>
            <tr>
                <th style="width: 25px;">Nº</th>
                <th>DESCRIPCIÓN</th>
                <th style="width: 75px;">TOTAL</th>
                <th style="width: 95px;">COTIZACIÓN</th>
                <th>NOMBRE</th>
                <th style="width: 75px;">BANCO</th>
                <th style="width: 55px;">CUENTA</th>
                <th style="width: 95px;">UNIDAD</th>
                <th style="width: 80px;">FORMA DE PAGO</th>
                <th style="width: 110px;">OBSERVACIONES</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($s->conceptos as $c)
                <tr>
                    <td class="c b">{{ $loop->iteration }}</td>
                    <td class="b">{{ $c->descripcion }}</td>
                    <td class="r b">$ {{ number_format((float) $c->total, 2) }}</td>
                    <td class="c">{{ $s->cotizacion ?: 'N/A' }}</td>
                    <td>{{ $destinatario }}</td>
                    <td class="c">{{ $s->banco }}</td>
                    <td class="c">{{ $s->cuenta ?: 'N/A' }}</td>
                    <td class="c b" style="background: #fff8e1;">{{ $c->vehiculo?->nombre ?? 'N/A' }}</td>
                    <td class="c">{{ $c->forma_pago ?: $s->forma_pago }}</td>
                    <td>{{ $s->observaciones }}</td>
                </tr>
            @endforeach
            <tr class="subtotal">
                <td></td>
                <td class="r">SUBTOTAL</td>
                <td class="r">$ {{ number_format((float) $s->monto, 2) }}</td>
                <td colspan="7"></td>
            </tr>
            <tr class="total">
                <td></td>
                <td class="r">TOTAL</td>
                <td class="r">$ {{ number_format((float) $s->monto, 2) }}</td>
                <td colspan="7"></td>
            </tr>
        </tbody>
    </table>

    <table class="firmas">
        <tr>
            <th>ELABORÓ</th>
            <th>REVISÓ</th>
            <th>REVISÓ</th>
            <th>AUTORIZÓ</th>
        </tr>
        <tr>
            <td>
                <div class="firma-nombre">{{ $elaboro }}</div>
                <div class="firma-cargo">{{ $s->elaboro_cargo }}</div>
            </td>
            <td>
                <div class="firma-nombre">{{ $s->reviso1_nombre }}</div>
                <div class="firma-cargo">{{ $s->reviso1_cargo }}</div>
            </td>
            <td>
                <div class="firma-nombre">{{ $s->reviso2_nombre }}</div>
                <div class="firma-cargo">{{ $s->reviso2_cargo }}</div>
            </td>
            <td>
                <div class="firma-nombre">{{ $s->autorizo_nombre }}</div>
                <div class="firma-cargo">{{ $s->autorizo_cargo }}</div>
            </td>
        </tr>
    </table>
</body>
</html>