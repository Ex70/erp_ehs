<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunicado_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comunicado_id')->constrained('comunicados')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipo', ['incluido', 'excluido'])->default('incluido');
            $table->timestamps();

            $table->unique(['comunicado_id', 'user_id'], 'comunicado_user_unique');
            $table->index(['comunicado_id', 'tipo'], 'comunicado_user_tipo_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunicado_user');
    }
};