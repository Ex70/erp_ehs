<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de versiones. Cada vez que se reemplaza el archivo de un
     * documento, la versión anterior se archiva aquí en lugar de borrarse.
     */
    public function up(): void
    {
        Schema::create('normatividad_versiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')
                ->constrained('normatividad_documentos')
                ->cascadeOnDelete();

            $table->string('version', 30)->nullable();
            $table->string('archivo');
            $table->string('archivo_nombre');
            $table->unsignedBigInteger('archivo_tamano')->nullable();
            $table->string('archivo_mime', 120)->nullable();
            $table->date('vigencia')->nullable()->comment('Vigencia que tenía esta versión');
            $table->string('notas', 255)->nullable();

            $table->foreignId('reemplazado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['documento_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('normatividad_versiones');
    }
};
