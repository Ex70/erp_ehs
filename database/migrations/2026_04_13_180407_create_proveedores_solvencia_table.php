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
        Schema::create('proveedores_solvencia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->string('rfc', 30)->nullable();
            $table->string('giro', 100)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('facturacion', 100)->nullable();
            $table->string('tiempo_entrega', 60)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('proveedores_solvencia');
    }
};
