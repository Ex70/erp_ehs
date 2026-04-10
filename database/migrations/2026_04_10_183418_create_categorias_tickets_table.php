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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 15)->unique();

            // Solicitante — viene del usuario del sistema
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Catálogos
            $table->foreignId('tipo_falla_id')
                ->nullable()
                ->constrained('tipos_falla')
                ->nullOnDelete();

            $table->foreignId('categoria_servicio_id')
                ->nullable()
                ->constrained('categorias_servicio')
                ->nullOnDelete();

            // Prioridad y estado
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])
                ->default('media');

            $table->enum('seguimiento', [
                'pendiente',
                'en_atencion',
                'en_desarrollo',
                'en_pruebas',
                'finalizado',
                'escalado',
            ])->default('pendiente');

            // Contenido
            $table->text('descripcion');
            $table->string('evidencia', 255)->nullable();

            // Resolución
            $table->text('resolucion')->nullable();
            $table->timestamp('fecha_cierre')->nullable();

            // Calificación del usuario (1-5 estrellas, 0 = sin calificar)
            $table->tinyInteger('calificacion')->default(0);
            $table->text('comentario_calificacion')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('tickets'); }
};
