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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120)->unique();
            $table->string('rfc', 20)->nullable();
            $table->string('giro', 100)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->string('correo', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('proveedores');
    }
};
