<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora inmutable del expediente. Cada movimiento lleva una huella
 * (sha256) que depende de la anterior: si alguien altera un registro
 * directamente en la base de datos, la cadena deja de verificar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solvencia_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')
                  ->constrained('solvencia_expedientes')
                  ->restrictOnDelete();
            $table->unsignedInteger('n');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre', 150);       // nombre al momento del movimiento
            $table->string('rol', 120)->nullable();
            $table->string('accion', 150);
            $table->text('detalle')->nullable();
            $table->string('tipo', 10)->nullable();   // crea|doc|exc|ok|envio|acuse|rec|dev|can|fin|pago|edit
            $table->unsignedTinyInteger('etapa')->nullable();
            $table->char('hash', 64);
            // DATETIME (no TIMESTAMP): MySQL no lo convierte por zona horaria de
            // sesión, así la huella se recalcula siempre sobre el mismo valor.
            $table->dateTime('created_at')->nullable();

            $table->unique(['expediente_id', 'n']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solvencia_movimientos');
    }
};
