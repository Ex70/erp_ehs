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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias_producto')
                ->nullOnDelete();
            $table->foreignId('unidad_medida_id')
                ->nullable()
                ->constrained('unidades_medida')
                ->nullOnDelete();
            $table->decimal('precio_referencia', 14, 2)->nullable();
            $table->text('especificaciones')->nullable();
            $table->string('imagen', 255)->nullable();
            $table->string('ficha_tecnica', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void{
        Schema::dropIfExists('productos');
    }
};
