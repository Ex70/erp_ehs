<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();                   // Alias operativo: GOL, HILUX, TORNADO 1…
            $table->string('marca', 60)->nullable();
            $table->string('modelo', 60)->nullable();
            $table->unsignedSmallInteger('anio')->nullable();
            $table->string('placas', 20)->nullable()->unique();
            $table->string('numero_serie', 40)->nullable();
            $table->string('color', 30)->nullable();
            $table->string('combustible', 20)->default('gasolina');   // llaves de config('logistica.combustibles')
            $table->decimal('rendimiento_km_l', 5, 2)->nullable();    // lo usará el cotizador de viajes
            $table->unsignedInteger('km_actual')->nullable();         // odómetro vigente
            $table->timestamp('km_actualizado_at')->nullable();
            $table->string('color_etiqueta', 7)->default('#1e3a5f');  // color de la unidad en tableros
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};