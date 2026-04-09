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
        Schema::create('requerimientos', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique();

            // Relaciones con catálogos
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->restrictOnDelete();
            $table->foreignId('empresa_emisora_id')
                ->constrained('empresas')
                ->restrictOnDelete();
            $table->string('empresa_realiza', 60)->nullable();

            // Analista asignado (user del sistema)
            $table->foreignId('analista_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Clasificación
            $table->enum('tipo', ['normal', 'urgente', 'critico'])->default('normal');
            $table->string('linea_negocio', 60)->nullable();

            // Fechas
            $table->date('fecha_solicitud');
            $table->date('fecha_entrega')->nullable();
            $table->date('fecha_max_entrega_aut')->nullable();

            // Montos
            $table->decimal('margen', 5, 2)->default(0);
            $table->decimal('indirectos', 5, 2)->default(0);
            $table->decimal('monto_estimado', 14, 2)->nullable();
            $table->decimal('monto_autorizado', 14, 2)->nullable();
            $table->decimal('costo_proveedor', 14, 2)->nullable();

            // Flujo
            $table->enum('status', [
                'pendiente',
                'cotizando',
                'enviado',
                'autorizado',
                'cancelado',
            ])->default('pendiente');

            $table->boolean('autorizado')->default(false);
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void{
        Schema::dropIfExists('requerimientos');
    }
};
