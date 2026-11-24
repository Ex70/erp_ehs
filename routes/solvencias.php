<?php

/*
|--------------------------------------------------------------------------
| Solvencias económicas · Trazabilidad (cadena de custodia)
|--------------------------------------------------------------------------
| Se carga desde web.php. Disponible para todo usuario autenticado, como
| la Mesa de Ayuda: lo que cada quien puede hacer lo decide el flujo
| (quién tiene la solvencia), no un permiso.
|
| Permisos (Spatie, se asignan desde Roles):
|   solvencias_flujo.ver.todas   ver todas las solvencias en el tablero
|   solvencias_flujo.configurar  Personas y flujos, folios, Contabilidad
|   solvencias_flujo.cancelar    cancelar cualquier solvencia activa
| Gates (por persona, no por rol):
|   solvencias-elaborar  aparece en la etapa "Elabora" de algún flujo
|   solvencias-pagar     está en Contabilidad y Finanzas
|
|   URL:    /solvencias-economicas/...
|   Nombre: sol.*
*/

use App\Http\Controllers\Solvencias\Trazabilidad\BandejaController;
use App\Http\Controllers\Solvencias\Trazabilidad\ContabilidadController;
use App\Http\Controllers\Solvencias\Trazabilidad\DocumentoController;
use App\Http\Controllers\Solvencias\Trazabilidad\ExpedienteAccionController;
use App\Http\Controllers\Solvencias\Trazabilidad\ExpedienteController;
use App\Http\Controllers\Solvencias\Trazabilidad\FlujoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('solvencias-economicas')->name('sol.')->group(function () {

    // ── Bandeja y tablero ───────────────────────────────────────────────
    Route::get('/', [BandejaController::class, 'index'])->name('bandeja');
    Route::get('tablero', [ExpedienteController::class, 'index'])->name('tablero');

    // ── Expedientes ─────────────────────────────────────────────────────
    Route::get('nueva', [ExpedienteController::class, 'create'])
        ->name('expedientes.create')->middleware('can:solvencias-elaborar');
    Route::post('expedientes', [ExpedienteController::class, 'store'])
        ->name('expedientes.store')->middleware('can:solvencias-elaborar');

    Route::prefix('expedientes/{expediente}')->name('expedientes.')->group(function () {
        Route::get('/', [ExpedienteController::class, 'show'])->name('show');
        Route::put('/', [ExpedienteController::class, 'update'])->name('update');

        Route::controller(ExpedienteAccionController::class)->group(function () {
            Route::post('acuse', 'acusar')->name('acuse');
            Route::post('enviar', 'enviar')->name('enviar');
            Route::post('recordar', 'recordar')->name('recordar');
            Route::post('devolver', 'devolver')->name('devolver');
            Route::post('cancelar', 'cancelar')->name('cancelar');
            Route::post('autorizar', 'autorizar')->name('autorizar');
            Route::post('pagar', 'pagar')->name('pagar')->middleware('can:solvencias-pagar');
        });

        Route::post('documentos', [DocumentoController::class, 'store'])->name('documentos.store');
        Route::post('documentos/exceptuar', [DocumentoController::class, 'exceptuar'])->name('documentos.exceptuar');
    });

    // ── Archivos (siempre por controlador) ──────────────────────────────
    Route::get('documentos/{documento}/ver', [DocumentoController::class, 'ver'])->name('documentos.ver');
    Route::get('documentos/{documento}/descargar', [DocumentoController::class, 'descargar'])->name('documentos.descargar');

    // ── Contabilidad y Finanzas ─────────────────────────────────────────
    Route::get('contabilidad', [ContabilidadController::class, 'index'])
        ->name('contabilidad')->middleware('can:solvencias-pagar');

    // ── Personas y flujos ───────────────────────────────────────────────
    Route::prefix('flujos')->name('flujos.')
        ->middleware('can:solvencias_flujo.configurar')
        ->controller(FlujoController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');

            // Rutas con segmento fijo antes de {flujo}
            Route::post('pagadores', 'agregarPagador')->name('pagadores.store');
            Route::delete('pagadores/{user}', 'quitarPagador')->name('pagadores.destroy');
            Route::post('folios', 'guardarFolio')->name('folios.store');

            Route::put('etapas/{etapa}', 'cambiarVerbo')->name('etapas.update');
            Route::delete('etapas/{etapa}', 'quitarEtapa')->name('etapas.destroy');
            Route::post('etapas/{etapa}/personas', 'agregarPersona')->name('personas.store');
            Route::delete('etapas/{etapa}/personas/{user}', 'quitarPersona')->name('personas.destroy');

            Route::put('{flujo}', 'update')->name('update');
            Route::post('{flujo}/confirmar', 'confirmar')->name('confirmar');
            Route::post('{flujo}/etapas', 'agregarEtapa')->name('etapas.store');
        });
});
