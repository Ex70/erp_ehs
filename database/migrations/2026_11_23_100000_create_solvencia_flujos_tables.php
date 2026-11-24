<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuración del módulo de solvencias con trazabilidad:
 *  - solvencia_flujos            un flujo por departamento (unidad de negocio)
 *  - solvencia_flujo_etapas      etapas ordenadas: Elabora → … → Autoriza
 *  - solvencia_flujo_etapa_user  personas (usuarios del ERP) asignadas a cada etapa
 *  - solvencia_pagadores         Contabilidad y Finanzas: quienes registran el pago
 *  - solvencia_folios            consecutivo por serie {PREFIJO}-{AÑO}
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solvencia_flujos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')
                  ->unique()
                  ->constrained('departamentos')
                  ->restrictOnDelete();
            $table->string('prefijo_folio', 10)->unique();
            $table->boolean('activo')->default(true);
            $table->boolean('por_confirmar')->default(false);
            $table->timestamps();
        });

        Schema::create('solvencia_flujo_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flujo_id')
                  ->constrained('solvencia_flujos')
                  ->cascadeOnDelete();
            $table->unsignedTinyInteger('orden');
            $table->string('verbo', 30);
            $table->timestamps();

            $table->index(['flujo_id', 'orden']);
        });

        Schema::create('solvencia_flujo_etapa_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etapa_id')
                  ->constrained('solvencia_flujo_etapas')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['etapa_id', 'user_id']);
        });

        Schema::create('solvencia_pagadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('solvencia_folios', function (Blueprint $table) {
            $table->id();
            $table->string('serie', 20)->unique();     // p. ej. ADQ-2026
            $table->unsignedInteger('ultimo')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solvencia_folios');
        Schema::dropIfExists('solvencia_pagadores');
        Schema::dropIfExists('solvencia_flujo_etapa_user');
        Schema::dropIfExists('solvencia_flujo_etapas');
        Schema::dropIfExists('solvencia_flujos');
    }
};
