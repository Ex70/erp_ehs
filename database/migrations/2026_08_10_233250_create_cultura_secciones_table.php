<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Secciones de texto único de Cultura Organizacional.
     * Claves: presentacion, mision, vision, historia, objetivos
     */
    public function up(): void
    {
        Schema::create('cultura_secciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 40)->unique()->comment('presentacion|mision|vision|historia|objetivos');
            $table->string('titulo', 150);
            $table->text('texto')->nullable();
            $table->string('icono', 16)->nullable()->comment('Emoji mostrado en la tarjeta');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultura_secciones');
    }
};
