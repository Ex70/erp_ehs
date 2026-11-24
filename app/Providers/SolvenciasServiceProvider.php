<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Solvencias\FlujoService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Gates del módulo de solvencias con trazabilidad. Se resuelven por PERSONA
 * (configuración de flujos), no por rol: los roles del ERP son por nivel y
 * no por departamento.
 */
class SolvenciasServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Aparece en la etapa "Elabora" de al menos un flujo activo y completo
        Gate::define('solvencias-elaborar', fn (User $user) => FlujoService::puedeElaborar($user));

        // Está en Contabilidad y Finanzas (registra pagos)
        Gate::define('solvencias-pagar', fn (User $user) => FlujoService::esPagador($user));
    }
}
