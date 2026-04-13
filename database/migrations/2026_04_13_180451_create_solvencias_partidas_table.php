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
        Schema::create('solvencia_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solvencia_id')
                ->constrained('solvencias')
                ->cascadeOnDelete();

            // Proveedor y cuenta bancaria seleccionada
            $table->foreignId('proveedor_solvencia_id')
                ->nullable()
                ->constrained('proveedores_solvencia')
                ->nullOnDelete();

            $table->foreignId('cuenta_bancaria_id')
                ->nullable()
                ->constrained('proveedor_cuentas_bancarias')
                ->nullOnDelete();

            // Datos de la partida
            $table->integer('numero')->default(1);
            $table->string('descripcion', 200);
            $table->decimal('cantidad', 10, 2)->default(1); // informativa
            $table->decimal('importe', 14, 2)->default(0);  // precio manual
            $table->string('concepto', 200)->nullable();     // forma de pago

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('solvencia_partidas');
    }
};
