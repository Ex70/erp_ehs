<?php

use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Solo administrador puede gestionar roles y permisos
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('roles', RolController::class);
    Route::resource('permisos', PermisoController::class);
});

// Administrador y jefe de área gestionan usuarios y puestos
Route::middleware(['auth', 'role:administrador|jefe_area'])->group(function () {
    Route::resource('puestos', PuestoController::class);
    Route::resource('usuarios', UsuarioController::class);
});

// Solo autenticados
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // Perfil propio — accesible para cualquier usuario autenticado
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/',         [PerfilController::class, 'show'])          ->name('show');
        Route::get('/editar',   [PerfilController::class, 'edit'])          ->name('edit');
        Route::put('/editar',   [PerfilController::class, 'update'])        ->name('update');
        Route::put('/password', [PerfilController::class, 'password'])      ->name('password');
        Route::post('/avatar',  [PerfilController::class, 'avatar'])        ->name('avatar');
        Route::delete('/avatar',[PerfilController::class, 'eliminarAvatar'])->name('avatar.eliminar');
    });
});