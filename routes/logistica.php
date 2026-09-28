<?php

/*
|--------------------------------------------------------------------------
| Logística y Entregas — submódulo de Adquisiciones
|--------------------------------------------------------------------------
| Se carga DENTRO del grupo de adquisiciones en web.php: hereda su
| middleware, el prefijo /adquisiciones y el nombre "adquisiciones.".
|   URL final:    /adquisiciones/logistica/...
|   Nombre final: adquisiciones.logistica.*
*/

use App\Http\Controllers\Adquisiciones\Logistica\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::prefix('logistica')->name('logistica.')->group(function () {

    // ── Catálogo de unidades vehiculares ────────────────────────────────
    Route::middleware('can:cat_logistica.ver')->group(function () {

        Route::resource('vehiculos', VehiculoController::class)
            ->only(['index', 'store', 'update']);

        Route::delete('vehiculos/{vehiculo}', [VehiculoController::class, 'destroy'])
            ->name('vehiculos.destroy')
            ->middleware('can:logistica.eliminar');
    });

});