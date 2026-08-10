<?php

use App\Http\Controllers\Adquisiciones\AdjudicacionController;
use App\Http\Controllers\Adquisiciones\CatalogoController;
use App\Http\Controllers\Adquisiciones\CategoriaProductoController;
use App\Http\Controllers\Adquisiciones\ClienteController;
use App\Http\Controllers\Adquisiciones\CuentaBancariaProveedorController;
use App\Http\Controllers\Adquisiciones\DependenciaController;
use App\Http\Controllers\Adquisiciones\DestinatarioController;
use App\Http\Controllers\Adquisiciones\EmpresaController;
use App\Http\Controllers\Adquisiciones\NotaController;
use App\Http\Controllers\Adquisiciones\ProductoController;
use App\Http\Controllers\Adquisiciones\ProveedorController;
use App\Http\Controllers\Adquisiciones\RequerimientoController;
use App\Http\Controllers\Adquisiciones\UnidadMedidaController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartamentoController;
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
use App\Http\Controllers\RRHH\ComunicadoController;
use App\Http\Controllers\RRHH\CulturaController;
use App\Http\Controllers\Sistemas\AsignacionIpController;
use App\Http\Controllers\Sistemas\DispositivoController;
use App\Http\Controllers\Sistemas\MarcaController;
use App\Http\Controllers\Solvencias\CuentaBancariaController;
use App\Http\Controllers\Solvencias\EmpresaSolvenciaController;
use App\Http\Controllers\Solvencias\SolvenciaController;
use App\Http\Controllers\Solvencias\SolvenciaPdfController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

// ─── Público ────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Completar registro (enlace enviado por correo — acceso público)
Route::get('/registro/completar/{token}',  [RegistroController::class, 'completar'])->name('registro.completar');
Route::post('/registro/completar/{token}', [RegistroController::class, 'guardar'])->name('registro.guardar');


// ─── Solo autenticados (sin permiso adicional) ──────────────────────────────
Route::middleware('auth')->group(function () {

    // Two-Factor Authentication
    Route::get('/two-factor/challenge',  [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/verify',    [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::get('/two-factor/setup',      [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/two-factor/enable',    [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/two-factor/disable',   [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil propio
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/',         [PerfilController::class, 'show'])           ->name('show');
        Route::get('/editar',   [PerfilController::class, 'edit'])           ->name('edit');
        Route::put('/editar',   [PerfilController::class, 'update'])         ->name('update');
        Route::put('/password', [PerfilController::class, 'password'])       ->name('password');
        Route::post('/avatar',  [PerfilController::class, 'avatar'])         ->name('avatar');
        Route::delete('/avatar',[PerfilController::class, 'eliminarAvatar']) ->name('avatar.eliminar');
    });

});


// ─── Departamentos ──────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // El endpoint del select encadenado va ANTES del resource;
    // si no, 'departamentos/{departamento}' lo captura primero.
    // Sin permiso: lo consume el formulario de usuarios.
    Route::get('departamentos/{departamento}/puestos', [DepartamentoController::class, 'puestos'])
        ->name('departamentos.puestos');

    Route::get('departamentos', [DepartamentoController::class, 'index'])
        ->name('departamentos.index')->middleware('can:departamentos.ver');

    Route::get('departamentos/create', [DepartamentoController::class, 'create'])
        ->name('departamentos.create')->middleware('can:departamentos.crear');

    Route::post('departamentos', [DepartamentoController::class, 'store'])
        ->name('departamentos.store')->middleware('can:departamentos.crear');

    Route::get('departamentos/{departamento}', [DepartamentoController::class, 'show'])
        ->name('departamentos.show')->middleware('can:departamentos.ver');

    Route::get('departamentos/{departamento}/edit', [DepartamentoController::class, 'edit'])
        ->name('departamentos.edit')->middleware('can:departamentos.editar');

    Route::put('departamentos/{departamento}', [DepartamentoController::class, 'update'])
        ->name('departamentos.update')->middleware('can:departamentos.editar');

    Route::delete('departamentos/{departamento}', [DepartamentoController::class, 'destroy'])
        ->name('departamentos.destroy')->middleware('can:departamentos.eliminar');

});


// ─── Helpdesk ───────────────────────────────────────────────────────────────
Route::middleware('auth')->prefix('helpdesk')->name('helpdesk.')->group(function () {

    Route::get('dashboard', [DashboardHelpdeskController::class, 'index'])
        ->name('dashboard')
        ->middleware('can:tickets.dashboard');

    // Tickets — cualquier autenticado crea y ve los suyos;
    // el alcance propio/todos se resuelve dentro del controlador
    Route::resource('tickets', TicketController::class);

    Route::post('tickets/{ticket}/asignar', [AsignacionController::class, 'store'])
        ->name('tickets.asignar')
        ->middleware('can:tickets.asignar');

    Route::post('tickets/{ticket}/seguimiento', [SeguimientoController::class, 'store'])
        ->name('tickets.seguimiento')
        ->middleware('can:tickets.asignar');

    Route::post('tickets/{ticket}/calificar', [CalificacionController::class, 'store'])
        ->name('tickets.calificar')
        ->middleware('can:tickets.calificar');

    // Catálogos del helpdesk
    Route::get('catalogos', [CatalogoHelpdeskController::class, 'index'])
        ->name('catalogos.index')->middleware('can:cat_helpdesk.ver');

    Route::post('catalogos/tipos-falla', [CatalogoHelpdeskController::class, 'storeTipo'])
        ->name('catalogos.tipos.store')->middleware('can:cat_helpdesk.crear');

    Route::put('catalogos/tipos-falla/{tipoFalla}', [CatalogoHelpdeskController::class, 'updateTipo'])
        ->name('catalogos.tipos.update')->middleware('can:cat_helpdesk.editar');

    Route::post('catalogos/categorias', [CatalogoHelpdeskController::class, 'storeCategoria'])
        ->name('catalogos.categorias.store')->middleware('can:cat_helpdesk.crear');

    Route::put('catalogos/categorias/{categoriaServicio}', [CatalogoHelpdeskController::class, 'updateCategoria'])
        ->name('catalogos.categorias.update')->middleware('can:cat_helpdesk.editar');

});


// ─── RRHH ───────────────────────────────────────────────────────────────────
Route::middleware('auth')->prefix('rrhh')->name('rrhh.')->group(function () {

    // ── Comunicados ──
    // La ruta específica va antes del resource
    Route::get('comunicados/defaults', [ComunicadoController::class, 'defaults'])
        ->name('comunicados.defaults');

    Route::post('comunicados/preview-destinatarios', [ComunicadoController::class, 'previewDestinatarios'])
        ->name('comunicados.preview-destinatarios')
        ->middleware('can:comunicados.crear');

    // Comunicados — todos consultan; la autorización de escritura
    // ya está resuelta dentro del controlador
    Route::resource('comunicados', ComunicadoController::class)
        ->except(['create', 'edit']);

    // ── Cultura Organizacional ──
    // OJO: este grupo ya está dentro de prefix('rrhh')->name('rrhh.'),
    // por eso aquí solo se agrega 'cultura'. Resultado: /rrhh/cultura
    // con nombres rrhh.cultura.*
    Route::prefix('cultura')->name('cultura.')->group(function () {

        // Lectura
        Route::get('/', [CulturaController::class, 'index'])
            ->middleware('can:cultura.ver.todos')
            ->name('index');

        // Edición
        Route::middleware('can:cultura.editar.todos')->group(function () {

            // Secciones de texto: presentacion | mision | vision | historia | objetivos
            Route::put('secciones/{clave}', [CulturaController::class, 'actualizarSeccion'])
                ->name('secciones.update');

            // Listas: valor | objetivo | empresa  (reemplazo completo de la colección)
            Route::put('items/{tipo}', [CulturaController::class, 'sincronizarItems'])
                ->name('items.sync');

            // Carrusel. Se usa POST también para actualizar porque el formulario
            // envía multipart/form-data (no se puede usar PUT con FormData en PHP).
            Route::post('slides', [CulturaController::class, 'guardarSlide'])->name('slides.store');
            Route::post('slides/{item}', [CulturaController::class, 'guardarSlide'])->name('slides.update');
            Route::delete('slides/{item}', [CulturaController::class, 'eliminarSlide'])->name('slides.destroy');
        });
    });

});


// ─── Usuarios y puestos ─────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::resource('usuarios', UsuarioController::class)
        ->middleware('can:usuarios.ver');

    Route::delete('usuarios/{usuario}/force-delete', [UsuarioController::class, 'forceDelete'])
        ->name('usuarios.forceDelete')
        ->middleware('can:usuarios.eliminar');

    Route::post('usuarios/{usuario}/reenviar-registro', [UsuarioController::class, 'reenviarRegistro'])
        ->name('usuarios.reenviar-registro')
        ->middleware('can:usuarios.editar');

    Route::resource('puestos', PuestoController::class)
        ->middleware('can:puestos.ver');

});


// ─── Adquisiciones ──────────────────────────────────────────────────────────
Route::middleware('auth')->prefix('adquisiciones')->name('adquisiciones.')->group(function () {

    Route::resource('requerimientos', RequerimientoController::class)
        ->middleware('can:adquisiciones.ver');

    Route::post('requerimientos/{requerimiento}/adjudicar', [AdjudicacionController::class, 'store'])
        ->name('requerimientos.adjudicar')
        ->middleware('can:adquisiciones.adjudicar');

    Route::post('requerimientos/{requerimiento}/notas', [NotaController::class, 'store'])
        ->name('requerimientos.notas.store')
        ->middleware('can:adquisiciones.editar');

    Route::delete('notas/{nota}', [NotaController::class, 'destroy'])
        ->name('notas.destroy')
        ->middleware('can:adquisiciones.editar');

    // Catálogos de adquisiciones
    Route::middleware('can:cat_adquisiciones.ver')->group(function () {

        Route::resource('clientes', ClienteController::class)->except(['create', 'edit', 'show']);
        Route::resource('empresas', EmpresaController::class)->except(['create', 'edit', 'show']);

        // La ruta específica va antes del resource
        Route::get('proveedores/ranking', [ProveedorController::class, 'ranking'])
            ->name('proveedores.ranking');
        Route::resource('proveedores', ProveedorController::class)->except(['create', 'edit']);

        Route::resource('unidades-medida', UnidadMedidaController::class)
            ->except(['create', 'edit', 'show'])
            ->parameters(['unidades-medida' => 'unidad_medida']);

        Route::get('catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');

        Route::resource('destinatarios', DestinatarioController::class)->except(['create', 'edit', 'show']);
        Route::resource('dependencias',  DependenciaController::class)->except(['create', 'edit', 'show']);
        Route::resource('productos',     ProductoController::class);

        Route::resource('categorias-producto', CategoriaProductoController::class)
            ->except(['create', 'edit', 'show']);
    });

});


// ─── Solvencias ─────────────────────────────────────────────────────────────
Route::middleware('auth')->prefix('solvencias')->name('solvencias.')->group(function () {

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
        ])
        ->middleware('can:solvencias.ver');

    Route::get('{solvencia}/pdf', [SolvenciaPdfController::class, 'generar'])
        ->name('pdf')
        ->middleware('can:solvencias.ver');

    Route::get('api/proveedor/{proveedor}/cuentas', [CuentaBancariaController::class, 'porProveedor'])
        ->name('api.cuentas');

    Route::resource('empresas', EmpresaSolvenciaController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:solvencias.ver');

});


// ─── Cuentas bancarias de proveedores ───────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('api/proveedor/{proveedor}/cuentas', [CuentaBancariaProveedorController::class, 'porProveedor'])
        ->name('api.cuentas');

    Route::post('proveedores/{proveedor}/cuentas', [CuentaBancariaProveedorController::class, 'store'])
        ->name('proveedores.cuentas.store')
        ->middleware('can:cat_adquisiciones.crear');

    Route::delete('cuentas/{cuenta}', [CuentaBancariaProveedorController::class, 'destroy'])
        ->name('proveedores.cuentas.destroy')
        ->middleware('can:cat_adquisiciones.eliminar');

});


// ─── Sistemas ───────────────────────────────────────────────────────────────
Route::middleware('auth')->prefix('sistemas')->name('sistemas.')->group(function () {

    Route::resource('redes', AsignacionIpController::class)
        ->parameters(['redes' => 'asignacion_ip'])
        ->middleware('can:redes.ver');

    Route::resource('dispositivos', DispositivoController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:catalogos_sistemas.ver');

    Route::resource('marcas', MarcaController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:catalogos_sistemas.ver');

});


// ─── Administración del sistema ─────────────────────────────────────────────
// Se mantiene por ROL a propósito: quien administra roles puede otorgarse
// cualquier permiso, así que este acceso no debe ser delegable por permiso.
Route::middleware(['auth', 'role:administrador'])->group(function () {

    Route::resource('roles', RolController::class)
        ->parameters(['roles' => 'rol']);

    Route::resource('permisos', PermisoController::class);

});