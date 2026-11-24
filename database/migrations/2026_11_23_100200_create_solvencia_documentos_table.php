<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documentos del expediente. Al reemplazar, la versión anterior se marca
 * como reemplazada y se conserva (archivo incluido). Un documento puede
 * registrarse "sin archivo" (exento) con una observación que lo justifique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solvencia_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')
                  ->constrained('solvencia_expedientes')
                  ->cascadeOnDelete();
            $table->string('tipo', 30);

            $table->string('archivo')->nullable();          // ruta en disco privado
            $table->string('nombre', 255);                   // nombre original
            $table->unsignedBigInteger('tamano')->default(0);
            $table->string('mime', 120)->nullable();

            $table->boolean('exento')->default(false);
            $table->text('observacion')->nullable();
            $table->boolean('reemplazado')->default(false);

            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['expediente_id', 'tipo', 'reemplazado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solvencia_documentos');
    }
};
