<?php
namespace App\Http\Controllers\Solvencias;

use App\Http\Controllers\Controller;
use App\Models\Solvencia;

class SolvenciaPdfController extends Controller
{
    public function generar(Solvencia $solvencia)
    {
        $solvencia->load([
            'empresa',
            'elaborador.puesto',
            'partidas.proveedor',
            'partidas.cuentaBancaria',
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'solvencias.pdf',
            compact('solvencia')
        )
        ->setPaper('letter', 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
        ]);

        return $pdf->download("solvencia-{$solvencia->folio}.pdf");
    }
}