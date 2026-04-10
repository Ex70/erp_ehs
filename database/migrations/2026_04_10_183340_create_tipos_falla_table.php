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
        Schema::create('tipos_falla', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();
            $table->string('color', 20)->default('secondary');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('tipos_falla'); }
};
