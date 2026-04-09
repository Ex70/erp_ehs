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
        Schema::create('requerimiento_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimiento_id')
                ->constrained('requerimientos')
                ->cascadeOnDelete();
            $table->string('descripcion', 200);
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->foreignId('unidad_medida_id')
                ->nullable()
                ->constrained('unidades_medida')
                ->nullOnDelete();
            $table->decimal('precio_proveedor', 14, 2)->nullable();
            $table->decimal('precio_cliente', 14, 2)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requerimiento_partidas');
    }
};
