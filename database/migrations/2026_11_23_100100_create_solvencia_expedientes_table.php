<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expediente de solvencia con cadena de custodia.
 * No se borra nunca: se cancela. Conserva el recorrido (snapshot del flujo)
 * con el que nació, aunque después cambie la configuración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solvencia_expedientes', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 25)->unique();

            $table->foreignId('flujo_id')->constrained('solvencia_flujos')->restrictOnDelete();
            $table->foreignId('departamento_id')->constrained('departamentos')->restrictOnDelete();

            // Captura mínima
            $table->string('tipo_proveedor', 10);          // validado | nuevo
            $table->string('proveedor', 200);
            $table->decimal('monto', 14, 2);
            $table->string('descripcion', 300);

            // Recorrido congelado y lista plana de participantes (para consultas)
            $table->json('recorrido');
            $table->json('participantes');

            // Estado y custodia
            $table->unsignedTinyInteger('etapa')->default(0);
            $table->string('estado', 20)->default('borrador');
            $table->foreignId('holder_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('acuse')->default(true);
            $table->timestamp('desde')->nullable();

            $table->foreignId('enviado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('enviado_en')->nullable();
            $table->unsignedSmallInteger('recordatorios')->default(0);

            $table->foreignId('creador_id')->constrained('users')->restrictOnDelete();

            // Devolución
            $table->unsignedTinyInteger('devuelta_desde')->nullable();
            $table->foreignId('devuelta_por_id')->nullable()->constrained('users')->nullOnDelete();

            // Cierre
            $table->timestamp('autorizada_en')->nullable();
            $table->date('pago_fecha')->nullable();
            $table->foreignId('pago_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('pago_en')->nullable();
            $table->timestamp('cancelada_en')->nullable();

            $table->timestamps();

            $table->index('estado');
            $table->index(['holder_id', 'estado']);
            $table->index(['enviado_por_id', 'acuse']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solvencia_expedientes');
    }
};
