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
        Schema::create('requerimiento_proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimiento_id')
                ->constrained('requerimientos')
                ->cascadeOnDelete();
            $table->foreignId('proveedor_id')
                ->nullable()
                ->constrained('proveedores')
                ->nullOnDelete();

            // Datos específicos de la cotización
            $table->decimal('monto', 14, 2)->nullable();
            $table->string('tiempo_entrega', 60)->nullable();
            $table->decimal('costo_envio', 10, 2)->default(0);
            $table->enum('disponibilidad', ['SI', 'NO', 'PARCIAL'])->default('SI');
            $table->string('url', 255)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('ganador')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requerimiento_proveedores');
    }
};
