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
        // Verificar si la columna ya es proveedor_id o aún es proveedor_solvencia_id
        if (Schema::hasColumn('proveedor_cuentas_bancarias', 'proveedor_solvencia_id')) {

            Schema::table('proveedor_cuentas_bancarias', function (Blueprint $table) {
                // Soltar FK y columna antigua
                $table->dropForeign(['proveedor_solvencia_id']);
                $table->dropColumn('proveedor_solvencia_id');
            });

            Schema::table('proveedor_cuentas_bancarias', function (Blueprint $table) {
                // Agregar FK correcta a proveedores existentes
                $table->foreignId('proveedor_id')
                    ->after('id')
                    ->constrained('proveedores')
                    ->cascadeOnDelete();
            });

        }
        // Si ya tiene proveedor_id no hace nada
    }

    public function down(): void{
        if (Schema::hasColumn('proveedor_cuentas_bancarias', 'proveedor_id')) {

            Schema::table('proveedor_cuentas_bancarias', function (Blueprint $table) {
                $table->dropForeign(['proveedor_id']);
                $table->dropColumn('proveedor_id');
            });

            Schema::table('proveedor_cuentas_bancarias', function (Blueprint $table) {
                $table->foreignId('proveedor_solvencia_id')
                    ->after('id')
                    ->constrained('proveedores_solvencia')
                    ->cascadeOnDelete();
            });
        }
    }
};
