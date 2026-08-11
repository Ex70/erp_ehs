<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('normatividad_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('categoria', 40)->comment('Clave de config/normatividad.php');
            $table->string('titulo', 255);
            $table->string('version', 30)->nullable()->comment('Ej. v2.1');
            $table->date('vigencia')->nullable()->comment('Fecha hasta la que el documento es vigente');
            $table->string('responsable', 150)->nullable()->comment('Área o persona responsable');
            $table->text('descripcion')->nullable();

            // Archivo vigente
            $table->string('archivo')->nullable()->comment('Ruta en el disco privado');
            $table->string('archivo_nombre')->nullable()->comment('Nombre original mostrado al usuario');
            $table->unsignedBigInteger('archivo_tamano')->nullable()->comment('Bytes');
            $table->string('archivo_mime', 120)->nullable();

            $table->date('fecha_publicacion')->nullable();
            $table->boolean('activo')->default(true);

            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['categoria', 'activo']);
            $table->index('vigencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('normatividad_documentos');
    }
};
