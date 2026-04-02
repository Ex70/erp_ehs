<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\Sistemas\AsignacionIpController;
use App\Http\Controllers\Sistemas\DispositivoController;
use App\Http\Controllers\Sistemas\MarcaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// ─── Solo autenticados ────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/',         [PerfilController::class, 'show'])           ->name('show');
        Route::get('/editar',   [PerfilController::class, 'edit'])           ->name('edit');
        Route::put('/editar',   [PerfilController::class, 'update'])         ->name('update');
        Route::put('/password', [PerfilController::class, 'password'])       ->name('password');
        Route::post('/avatar',  [PerfilController::class, 'avatar'])         ->name('avatar');
        Route::delete('/avatar',[PerfilController::class, 'eliminarAvatar']) ->name('avatar.eliminar');
    });

});

// ─── Administrador y jefe de área ────────────────────────────────────────────
Route::middleware(['auth', 'role:administrador|jefe_area'])->group(function () {
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('puestos',  PuestoController::class);
});

// ─── Solo administrador ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:administrador'])->group(function () {

    Route::resource('roles',    RolController::class);
    Route::resource('permisos', PermisoController::class);

    // Módulo Sistemas — prefijo y nombre agrupados
    Route::prefix('sistemas')->name('sistemas.')->group(function () {

        Route::resource('redes', AsignacionIpController::class)
             ->parameters(['redes' => 'asignacion_ip']);

        Route::resource('dispositivos', DispositivoController::class)
             ->except(['create', 'edit', 'show']);

        Route::resource('marcas', MarcaController::class)
             ->except(['create', 'edit', 'show']);

    });

});