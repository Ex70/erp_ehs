<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunicado_departamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comunicado_id')->constrained('comunicados')->cascadeOnDelete();
            $table->foreignId('departamento_id')->constrained('departamentos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['comunicado_id', 'departamento_id'], 'comunicado_depto_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunicado_departamento');
    }
};