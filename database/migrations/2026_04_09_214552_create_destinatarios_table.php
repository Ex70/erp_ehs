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
        Schema::create('destinatarios', function (Blueprint $table) {
            $table->id();
            $table->string('dirigido_a', 120);
            $table->string('cargo', 120)->nullable();
            $table->foreignId('dependencia_id')
                ->constrained('dependencias')
                ->restrictOnDelete();
            $table->string('atencion_a', 120)->nullable();
            $table->string('lugar', 150)->nullable();
            $table->string('correo', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('telefono_secundario', 30)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void{
        Schema::dropIfExists('destinatarios');
    }
};
