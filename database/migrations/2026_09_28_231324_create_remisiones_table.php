<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete();
            $table->foreignId('dependencia_id')->constrained('dependencias')->restrictOnDelete();
            $table->string('folio', 40);
            $table->string('referencia', 80)->nullable();        // folio de la cotización
            $table->string('num_remision', 40)->nullable();      // solo CEHS
            $table->date('fecha');
            $table->foreignId('entrega_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recibe_nombre', 120)->nullable();    // persona de la dependencia
            $table->string('recibe_cargo', 120)->nullable();     // solo EHS
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Incluye eliminadas: un folio usado no se vuelve a emitir
            $table->unique(['empresa_id', 'folio']);
            $table->index('fecha');
        });

        Schema::create('remision_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remision_id')->constrained('remisiones')->cascadeOnDelete();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->string('numero', 20)->nullable();            // "PARTIDA" editable
            $table->text('descripcion');
            $table->string('unidad', 40);                        // texto impreso (PIEZA, KILOGRAMO…)
            $table->decimal('cantidad', 12, 2);
            $table->string('imagen_path')->nullable();           // disco public
            $table->timestamps();

            $table->index(['remision_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remision_partidas');
        Schema::dropIfExists('remisiones');
    }
};