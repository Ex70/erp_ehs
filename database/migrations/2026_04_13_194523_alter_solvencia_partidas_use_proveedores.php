<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::table('solvencia_partidas', function (Blueprint $table) {
            // Eliminar FK anterior si existe
            if (Schema::hasColumn('solvencia_partidas', 'proveedor_solvencia_id')) {
                $table->dropForeign(['proveedor_solvencia_id']);
                $table->dropColumn('proveedor_solvencia_id');

                // Agregar FK correcta a proveedores existentes
                $table->foreignId('proveedor_id')
                    ->nullable()
                    ->after('solvencia_id')
                    ->constrained('proveedores')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void{
        Schema::table('solvencia_partidas', function (Blueprint $table) {
            $table->dropForeign(['proveedor_id']);
            $table->dropColumn('proveedor_id');
            $table->foreignId('proveedor_solvencia_id')
                ->nullable()
                ->constrained('proveedores_solvencia')
                ->nullOnDelete();
        });
    }
};
