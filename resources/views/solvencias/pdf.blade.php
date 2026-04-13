<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #000;
        }

        /* Encabezado */
        .header {
            width: 100%;
            margin-bottom: 6px;
        }

        .titulo-central {
            text-align: center;
            background: #c0392b;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 4px;
            letter-spacing: 1px;
        }

        .folio-badge {
            background: #c0392b;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            padding: 3px 8px;
            float: right;
        }

        /* Tabla de datos empresa */
        .datos-empresa {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .datos-empresa td {
            border: 1px solid #ccc;
            padding: 3px 6px;
            font-size: 8px;
        }

        .datos-empresa .lbl {
            background: #d6e8f7;
            font-weight: bold;
            width: 18%;
            text-transform: uppercase;
        }

        .datos-empresa .val {
            width: 32%;
        }

        /* Tabla de partidas */
        .tabla-partidas {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .tabla-partidas th {
            background: #1a2d4a;
            color: #fff;
            font-size: 7px;
            font-weight: bold;
            padding: 3px 4px;
            text-align: center;
            border: 1px solid #999;
            text-transform: uppercase;
        }

        .tabla-partidas td {
            border: 1px solid #ccc;
            padding: 3px 4px;
            font-size: 8px;
            vertical-align: middle;
        }

        .tabla-partidas .num { text-align: center; width: 3%; }
        .tabla-partidas .desc { width: 18%; }
        .tabla-partidas .cant { text-align: center; width: 5%; }
        .tabla-partidas .imp { text-align: right; width: 7%; }
        .tabla-partidas .prov { width: 12%; }
        .tabla-partidas .rfc { width: 8%; font-size: 7px; }
        .tabla-partidas .banco { width: 7%; }
        .tabla-partidas .clabe { width: 12%; font-size: 7px; }
        .tabla-partidas .cuenta { width: 8%; font-size: 7px; }
        .tabla-partidas .ref { width: 8%; }
        .tabla-partidas .concepto { width: 12%; font-size: 7px; }

        /* Totales */
        .totales {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .totales td {
            padding: 2px 6px;
            font-size: 8px;
            border: 1px solid #ccc;
        }

        .totales .lbl {
            background: #f0f0f0;
            font-weight: bold;
            text-align: right;
            width: 88%;
        }

        .totales .val {
            text-align: right;
            width: 12%;
        }

        .totales .total-row .lbl {
            background: #1e8449;
            color: #fff;
        }

        .totales .total-row .val {
            background: #d5f0e0;
            font-weight: bold;
        }

        /* Firmas */
        .firmas {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .firmas td {
            width: 33.33%;
            text-align: center;
            padding: 6px 10px;
            vertical-align: bottom;
        }

        .firma-linea {
            border-top: 1px solid #333;
            padding-top: 5px;
            margin: 30px 20px 0;
        }

        .firma-nombre {
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }

        .firma-cargo {
            font-size: 7px;
            text-transform: uppercase;
        }

        .firma-titulo {
            background: #d6e8f7;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            padding: 3px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    {{-- Título y folio --}}
    <div class="header">
        <div style="overflow:hidden;margin-bottom:4px">
            <span class="folio-badge">{{ $solvencia->folio }}</span>
        </div>
        <div class="titulo-central">SOLICITUD DE SOLVENCIA ECONÓMICA</div>
    </div>

    {{-- Datos de empresa y cliente --}}
    <table class="datos-empresa">
        <tr>
            <td class="lbl">Empresa</td>
            <td class="val">{{ $solvencia->empresa?->nombre }}</td>
            <td class="lbl">Cliente</td>
            <td class="val">{{ $solvencia->cliente ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Fecha</td>
            <td class="val">{{ $solvencia->fecha?->format('d/m/Y') }}</td>
            <td class="lbl">Departamento</td>
            <td class="val">{{ $solvencia->departamento ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">N° Cotización</td>
            <td class="val" colspan="3">{{ $solvencia->numero_cotizacion ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Monto solicitado</td>
            <td class="val">${{ number_format($solvencia->monto_solicitado, 2) }}</td>
            <td class="lbl">Monto autorizado</td>
            <td class="val">${{ number_format($solvencia->monto_autorizado, 2) }}</td>
        </tr>
    </table>

    {{-- Tabla de partidas --}}
    <table class="tabla-partidas">
        <thead>
            <tr>
                <th class="num">N°</th>
                <th class="desc">Descripción</th>
                <th class="cant">Cantidad</th>
                <th class="imp">Importe</th>
                <th class="prov">Nombre del proveedor</th>
                <th class="rfc">RFC</th>
                <th class="banco">Banco</th>
                <th class="clabe">CLABE</th>
                <th class="cuenta">Cuenta</th>
                <th class="ref">Referencia</th>
                <th class="concepto">Concepto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($solvencia->partidas as $p)
                <tr>
                    <td class="num">{{ $p->numero }}</td>
                    <td class="desc">{{ $p->descripcion }}</td>
                    <td class="cant">{{ $p->cantidad }}</td>
                    <td class="imp">${{ number_format($p->importe, 2) }}</td>
                    <td class="prov">{{ $p->proveedor?->nombre ?? '—' }}</td>
                    <td class="rfc">{{ $p->proveedor?->rfc ?? '—' }}</td>
                    <td class="banco">{{ $p->cuentaBancaria?->banco ?? '—' }}</td>
                    <td class="clabe">{{ $p->cuentaBancaria?->clabe ?? '—' }}</td>
                    <td class="cuenta">{{ $p->cuentaBancaria?->cuenta ?? '—' }}</td>
                    <td class="ref">{{ $p->cuentaBancaria?->referencia ?? '—' }}</td>
                    <td class="concepto">{{ $p->concepto ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totales --}}
    <table class="totales">
        <tr>
            <td class="lbl">SUB TOTAL</td>
            <td class="val">${{ number_format($solvencia->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="lbl">IVA (16%)</td>
            <td class="val">${{ number_format($solvencia->iva, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td class="lbl">TOTAL</td>
            <td class="val">${{ number_format($solvencia->total, 2) }}</td>
        </tr>
    </table>

    {{-- Firmas --}}
    <table class="firmas">
        <tr>
            <td>
                <div class="firma-titulo">Elaboró</div>
            </td>
            <td>
                <div class="firma-titulo">Validó</div>
            </td>
            <td>
                <div class="firma-titulo">Autorizó</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="firma-linea">
                    <div class="firma-nombre">{{ $solvencia->elaboro_nombre ?? '—' }}</div>
                    <div class="firma-cargo">{{ $solvencia->elaboro_cargo ?? '' }}</div>
                </div>
            </td>
            <td>
                <div class="firma-linea">
                    <div class="firma-nombre">{{ $solvencia->valido_nombre ?? '—' }}</div>
                    <div class="firma-cargo">{{ $solvencia->valido_cargo ?? '' }}</div>
                </div>
            </td>
            <td>
                <div class="firma-linea">
                    <div class="firma-nombre">{{ $solvencia->autorizo_nombre ?? '—' }}</div>
                    <div class="firma-cargo">{{ $solvencia->autorizo_cargo ?? '' }}</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>