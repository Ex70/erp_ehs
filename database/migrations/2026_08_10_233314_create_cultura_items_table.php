<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elementos repetibles de Cultura Organizacional.
     * tipo: slide | valor | objetivo | empresa
     *
     * Se usa string (no enum) para no depender de doctrine/dbal
     * si en el futuro se agrega un tipo nuevo.
     */
    public function up(): void
    {
        Schema::create('cultura_items', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->string('icono', 16)->nullable()->comment('Emoji');
            $table->string('color', 20)->nullable()->comment('HEX de fondo, ej. #1a1a3a');
            $table->string('imagen')->nullable()->comment('Ruta en disco public (solo slides)');
            $table->unsignedSmallInteger('duracion')->default(5)->comment('Segundos en carrusel (solo slides)');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tipo', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultura_items');
    }
};
