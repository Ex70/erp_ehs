<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_ip', function (Blueprint $table) {
            $table->id();

            // ID visual autogenerado tipo GQZ0001
            $table->string('codigo', 10)->unique();

            // Vínculo opcional con users ya existente
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Nombre del usuario asignado (puede ser diferente al user del sistema)
            $table->string('nombre', 100);

            // Red
            $table->string('direccion_ip', 15)->unique();
            $table->string('direccion_mac', 17)->unique();

            // Dispositivo
            $table->string('dispositivo', 50);   // Laptop, Desktop, etc.
            $table->string('marca', 60);
            $table->string('modelo', 80);
            $table->string('numero_serie', 60)->unique();

            // Ubicación
            $table->string('area', 100);
            $table->string('puesto', 100);

            // Auditoría
            $table->date('fecha_asignacion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_ip');
    }
};