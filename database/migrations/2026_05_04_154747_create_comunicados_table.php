<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunicados', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->enum('categoria', [
                'Infografía',
                'Organización',
                'Cumpleaños',
                'Reconocimiento',
                'Promoción',
                'Comunicado',
                'Evento',
            ]);
            $table->string('icono_emoji', 10)->nullable();
            $table->string('color_fondo', 7)->default('#F3F4F6');
            $table->date('fecha_publicacion');
            $table->string('autor')->default('Capital Humano');
            $table->text('extracto')->nullable();
            $table->longText('contenido_completo')->nullable();
            $table->string('archivo')->nullable(); // ruta imagen/PDF adjunto
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunicados');
    }
};