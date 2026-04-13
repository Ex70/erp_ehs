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
        Schema::create('proveedor_cuentas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_solvencia_id')
                ->constrained('proveedores_solvencia')
                ->cascadeOnDelete();
            $table->string('banco', 60);
            $table->string('clabe', 20)->nullable();
            $table->string('cuenta', 30)->nullable();
            $table->string('referencia', 60)->nullable();
            $table->string('tiempo_entrega', 60)->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('proveedor_cuentas_bancarias');
    }
};
