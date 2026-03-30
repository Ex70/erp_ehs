<?php

use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Solo administrador
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('roles', RolController::class);
});

// Jefe de área y administrador
Route::middleware(['auth', 'role:administrador|jefe_area'])->group(function () {
    Route::resource('puestos', PuestoController::class);
});

// Solo autenticados
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
});

Route::middleware(['auth', 'role:administrador|jefe_area'])->group(function () {
    Route::resource('usuarios', UsuarioController::class);
});