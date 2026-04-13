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
        Schema::create('solvencias', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 15)->unique();

            // Empresa interna
            $table->foreignId('empresa_solvencia_id')
                ->constrained('empresas_solvencia')
                ->restrictOnDelete();

            // Usuario que elabora
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Datos del documento
            $table->date('fecha');
            $table->string('numero_cotizacion', 200)->nullable();
            $table->string('cliente', 120)->nullable();
            $table->string('departamento', 120)->nullable();

            // Montos calculados
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('monto_solicitado', 14, 2)->default(0);
            $table->decimal('monto_autorizado', 14, 2)->default(0);

            // Firmas
            $table->string('elaboro_nombre', 100)->nullable();
            $table->string('elaboro_cargo', 100)->nullable();
            $table->string('valido_nombre', 100)->nullable();
            $table->string('valido_cargo', 100)->nullable();
            $table->string('autorizo_nombre', 100)->nullable();
            $table->string('autorizo_cargo', 100)->nullable();

            // Flujo
            $table->enum('estatus', [
                'borrador',
                'pendiente',
                'aprobada',
                'rechazada',
                'pagada',
            ])->default('borrador');

            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('solvencias');
    }
};
