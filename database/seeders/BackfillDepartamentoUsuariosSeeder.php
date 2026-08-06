<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Database\Seeder;

class BackfillDepartamentoUsuariosSeeder extends Seeder
{
    /**
     * Asigna departamento a los usuarios existentes.
     * Solo resuelve automáticamente los casos sin ambigüedad:
     * cuando el puesto del usuario pertenece a un único departamento.
     */
    public function run(): void
    {
        $pendientes = User::whereNull('departamento_id')
                          ->whereNotNull('puesto_id')
                          ->get();

        $resueltos = 0;
        $ambiguos  = [];

        foreach ($pendientes as $usuario) {
            $departamentos = Departamento::whereHas('puestos', function ($q) use ($usuario) {
                $q->where('puestos.id', $usuario->puesto_id);
            })->get();

            if ($departamentos->count() === 1) {
                $usuario->update(['departamento_id' => $departamentos->first()->id]);
                $resueltos++;
            } else {
                $ambiguos[] = $usuario;
            }
        }

        $this->command->info("✔ {$resueltos} usuarios asignados automáticamente");

        if (count($ambiguos)) {
            $this->command->warn(count($ambiguos) . ' usuarios requieren asignación manual:');

            foreach ($ambiguos as $u) {
                $puesto = Puesto::find($u->puesto_id)?->nombre ?? 'sin puesto';
                $this->command->line("   #{$u->id}  {$u->name}  —  {$puesto}");
            }
        }

        $huerfanos = User::whereNull('puesto_id')->count();

        if ($huerfanos) {
            $this->command->warn("{$huerfanos} usuarios no tienen puesto asignado.");
        }
    }
}