<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamento_puesto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('departamento_id');
            $table->unsignedBigInteger('puesto_id');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('departamento_id')
                  ->references('id')->on('departamentos')
                  ->cascadeOnDelete();

            $table->foreign('puesto_id')
                  ->references('id')->on('puestos')
                  ->cascadeOnDelete();

            $table->unique(['departamento_id', 'puesto_id'], 'departamento_puesto_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamento_puesto');
    }
};