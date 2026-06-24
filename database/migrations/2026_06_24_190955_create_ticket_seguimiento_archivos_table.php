<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_seguimiento_archivos', function (Blueprint $table) {
            $table->id();

            // ⚠️ Ajusta 'ticket_seguimientos' si tu tabla se llama distinto
            $table->foreignId('ticket_seguimiento_id')
                  ->constrained('ticket_seguimientos')
                  ->cascadeOnDelete();

            $table->string('nombre_original');
            $table->string('ruta');                 // ruta relativa dentro de storage/app/public
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('tamano')->nullable(); // bytes

            $table->foreignId('subido_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_seguimiento_archivos');
    }
};