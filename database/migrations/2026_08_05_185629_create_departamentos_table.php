<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120)->unique();
            $table->string('clave', 20)->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('responsable_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};