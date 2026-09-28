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

use App\Http\Controllers\Adquisiciones\Logistica\RemisionController;
use App\Http\Controllers\Adquisiciones\Logistica\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::prefix('logistica')->name('logistica.')->group(function () {

    // ── Notas de remisión ───────────────────────────────────────────────
    // "create" va antes de {remision} para que no lo capture el parámetro.
    Route::prefix('remisiones')->name('remisiones.')
        ->controller(RemisionController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:logistica.ver');
            Route::get('create', 'create')->name('create')->middleware('can:logistica.crear');
            Route::post('/', 'store')->name('store')->middleware('can:logistica.crear');
            Route::get('{remision}', 'show')->name('show')->middleware('can:logistica.ver');
            Route::get('{remision}/pdf', 'pdf')->name('pdf')->middleware('can:logistica.ver');
            Route::get('{remision}/edit', 'edit')->name('edit')->middleware('can:logistica.editar');
            Route::put('{remision}', 'update')->name('update')->middleware('can:logistica.editar');
            Route::delete('{remision}', 'destroy')->name('destroy')->middleware('can:logistica.eliminar');
        });

    // ── Catálogo de unidades vehiculares ────────────────────────────────
    Route::middleware('can:cat_logistica.ver')->group(function () {

        Route::resource('vehiculos', VehiculoController::class)
            ->only(['index', 'store', 'update']);

        Route::delete('vehiculos/{vehiculo}', [VehiculoController::class, 'destroy'])
            ->name('vehiculos.destroy')
            ->middleware('can:logistica.eliminar');
    });

});