<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_recurso', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);                                   // combustible | viaticos (fase futura)
            $table->string('folio', 30)->unique();                        // SOL-COMB-2026-0001
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete();
            $table->string('cliente', 150);
            $table->string('cotizacion', 80)->nullable();
            $table->date('fecha');
            $table->decimal('monto', 12, 2)->default(0);                  // suma de conceptos
            $table->foreignId('destinatario_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('banco', 80)->nullable();
            $table->string('cuenta', 40)->nullable();
            $table->string('clabe', 18)->nullable();
            $table->string('forma_pago', 30)->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->foreignId('elaboro_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('elaboro_cargo', 120)->nullable();
            $table->string('reviso1_nombre', 120)->nullable();
            $table->string('reviso1_cargo', 120)->nullable();
            $table->string('reviso2_nombre', 120)->nullable();
            $table->string('reviso2_cargo', 120)->nullable();
            $table->string('autorizo_nombre', 120)->nullable();
            $table->string('autorizo_cargo', 120)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tipo', 'fecha']);
        });

        Schema::create('solicitud_recurso_conceptos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes_recurso')->cascadeOnDelete();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->string('descripcion', 60);                            // COMBUSTIBLE, CASETAS, VIATICOS…
            $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos')->nullOnDelete();
            $table->string('forma_pago', 30)->nullable();
            $table->decimal('total', 12, 2);
            $table->timestamps();

            $table->index(['solicitud_id', 'orden']);
            $table->index('vehiculo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_recurso_conceptos');
        Schema::dropIfExists('solicitudes_recurso');
    }
};