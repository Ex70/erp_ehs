<?php

use App\Http\Controllers\Adquisiciones\AdjudicacionController;
use App\Http\Controllers\Adquisiciones\CatalogoController;
use App\Http\Controllers\Adquisiciones\CategoriaProductoController;
use App\Http\Controllers\Adquisiciones\ClienteController;
use App\Http\Controllers\Adquisiciones\DependenciaController;
use App\Http\Controllers\Adquisiciones\DestinatarioController;
use App\Http\Controllers\Adquisiciones\EmpresaController;
use App\Http\Controllers\Adquisiciones\NotaController;
use App\Http\Controllers\Adquisiciones\ProductoController;
use App\Http\Controllers\Adquisiciones\ProveedorController;
use App\Http\Controllers\Adquisiciones\RequerimientoController;
use App\Http\Controllers\Adquisiciones\UnidadMedidaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Helpdesk\AsignacionController;
use App\Http\Controllers\Helpdesk\CalificacionController;
use App\Http\Controllers\Helpdesk\CatalogoHelpdeskController;
use App\Http\Controllers\Helpdesk\DashboardHelpdeskController;
use App\Http\Controllers\Helpdesk\SeguimientoController;
use App\Http\Controllers\Helpdesk\TicketController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\Sistemas\AsignacionIpController;
use App\Http\Controllers\Sistemas\DispositivoController;
use App\Http\Controllers\Sistemas\MarcaController;
use App\Http\Controllers\Solvencias\CuentaBancariaController;
use App\Http\Controllers\Solvencias\EmpresaSolvenciaController;
use App\Http\Controllers\Solvencias\ProveedorSolvenciaController;
use App\Http\Controllers\Solvencias\SolvenciaController;
use App\Http\Controllers\Solvencias\SolvenciaPdfController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Completar registro (enlace enviado por correo — acceso público)
Route::get('/registro/completar/{token}', [RegistroController::class, 'completar'])->name('registro.completar');

Route::post('/registro/completar/{token}', [RegistroController::class, 'guardar'])->name('registro.guardar');

Route::post('usuarios/{usuario}/reenviar-registro',
    [UsuarioController::class, 'reenviarRegistro'])
    ->name('usuarios.reenviar-registro')
    ->middleware(['auth', 'role:administrador']);

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

    // Route::resource('roles',    RolController::class);
    Route::resource('roles', RolController::class)->parameters(['roles' => 'rol']);
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

    // Adquisiciones — acceso por rol
        Route::middleware(['auth', 'role:administrador|coordinador|auxiliar'])
            ->prefix('adquisiciones')
            ->name('adquisiciones.')
            ->group(function () {

                // Requerimientos
                Route::resource('requerimientos', RequerimientoController::class);

                // Adjudicación
                Route::post('requerimientos/{requerimiento}/adjudicar',
                    [AdjudicacionController::class, 'store'])
                    ->name('requerimientos.adjudicar');

                // Notas
                Route::post('requerimientos/{requerimiento}/notas',
                    [NotaController::class, 'store'])
                    ->name('requerimientos.notas.store');
                Route::delete('notas/{nota}',
                    [NotaController::class, 'destroy'])
                    ->name('notas.destroy');

                // Catálogos
                Route::resource('clientes',ClienteController::class)->except(['create','edit','show']);
                Route::resource('empresas',EmpresaController::class)->except(['create','edit','show']);
                Route::resource('proveedores', ProveedorController::class)->except(['create', 'edit', 'show']);
                Route::get('proveedores/ranking', [ProveedorController::class, 'ranking'])->name('proveedores.ranking');
                Route::resource('unidades-medida',UnidadMedidaController::class)
                    ->except(['create','edit','show'])
                    ->parameters(['unidades-medida' => 'unidad_medida']);

                    Route::get('catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');

                // Destinatarios
                Route::resource('destinatarios', DestinatarioController::class)
                    ->except(['create', 'edit', 'show']);

                // Catálogo de dependencias
                Route::resource('dependencias', DependenciaController::class)
                    ->except(['create', 'edit', 'show']);

                // Productos y servicios frecuentes
                Route::resource('productos', ProductoController::class);

                // Catálogo de categorías
                Route::resource('categorias-producto', CategoriaProductoController::class)
                    ->except(['create', 'edit', 'show']);
            });


    Route::middleware(['auth', 'role:administrador|coordinador|auxiliar'])
    ->prefix('solvencias')
    ->name('solvencias.')
    ->group(function () {

        // Solvencias
        Route::resource('/', SolvenciaController::class)
             ->parameters(['' => 'solvencia'])
             ->names([
                 'index'   => 'solvencias.index',
                 'create'  => 'solvencias.create',
                 'store'   => 'solvencias.store',
                 'show'    => 'solvencias.show',
                 'edit'    => 'solvencias.edit',
                 'update'  => 'solvencias.update',
                 'destroy' => 'solvencias.destroy',
             ]);

        // PDF
        Route::get('{solvencia}/pdf',
            [SolvenciaPdfController::class, 'generar'])
            ->name('pdf');

        // Proveedores
        Route::resource('proveedores', ProveedorSolvenciaController::class)
             ->parameters(['proveedores' => 'proveedoresSolvencia']);

        // API: cuentas por proveedor
        Route::get('api/proveedor/{proveedor}/cuentas',
            [CuentaBancariaController::class, 'porProveedor'])
            ->name('api.cuentas');

        // Empresas
        Route::resource('empresas', EmpresaSolvenciaController::class)
             ->except(['create', 'edit', 'show']);
    });


    // Helpdesk — todos los autenticados pueden crear tickets
    Route::middleware('auth')->prefix('helpdesk')->name('helpdesk.')->group(function () {

        // Dashboard — solo admin/coordinador/auxiliar
        Route::get('dashboard', [DashboardHelpdeskController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:tickets.dashboard');

        // Tickets
        Route::resource('tickets', TicketController::class);

        // Asignación — solo admin/coordinador
        Route::post('tickets/{ticket}/asignar', [AsignacionController::class, 'store'])
            ->name('tickets.asignar')
            ->middleware('can:tickets.asignar');

        // Seguimiento — admin/coordinador/auxiliar
        Route::post('tickets/{ticket}/seguimiento', [SeguimientoController::class, 'store'])
            ->name('tickets.seguimiento')
            ->middleware('can:tickets.asignar');

        // Calificación — solicitante
        Route::post('tickets/{ticket}/calificar', [CalificacionController::class, 'store'])
            ->name('tickets.calificar');

        // Catálogos — solo admin
        Route::middleware('role:administrador|coordinador')->group(function () {
            Route::get('catalogos', [CatalogoHelpdeskController::class, 'index'])
                ->name('catalogos.index');
            Route::post('catalogos/tipos-falla', [CatalogoHelpdeskController::class, 'storeTipo'])
                ->name('catalogos.tipos.store');
            Route::put('catalogos/tipos-falla/{tipoFalla}', [CatalogoHelpdeskController::class, 'updateTipo'])
                ->name('catalogos.tipos.update');
            Route::post('catalogos/categorias', [CatalogoHelpdeskController::class, 'storeCategoria'])
                ->name('catalogos.categorias.store');
            Route::put('catalogos/categorias/{categoriaServicio}', [CatalogoHelpdeskController::class, 'updateCategoria'])
                ->name('catalogos.categorias.update');
        });

    });

});