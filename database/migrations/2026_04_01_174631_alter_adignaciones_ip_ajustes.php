<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asignaciones_ip', function (Blueprint $table) {

            // 1. Soltar la FK original (que tenía nullOnDelete)
            $table->dropForeign(['user_id']);

            // 2. Cambiar la columna a NOT NULL
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // 3. Recrear la FK sin nullOnDelete
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            // 4. Reemplazar dispositivo texto por FK
            $table->dropColumn('dispositivo');
            $table->foreignId('dispositivo_id')
                ->after('user_id')
                ->constrained('dispositivos')
                ->restrictOnDelete();

            // 5. Reemplazar marca texto por FK
            $table->dropColumn('marca');
            $table->foreignId('marca_id')
                ->after('dispositivo_id')
                ->constrained('marcas')
                ->restrictOnDelete();

            // 6. Eliminar campos que vienen del usuario
            $table->dropColumn(['nombre', 'puesto']);
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_ip', function (Blueprint $table) {
            // Soltar FKs nuevas
            $table->dropForeign(['dispositivo_id']);
            $table->dropForeign(['marca_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn(['dispositivo_id', 'marca_id']);

            // Restaurar columnas eliminadas
            $table->string('dispositivo', 50)->nullable();
            $table->string('marca', 60)->nullable();
            $table->string('nombre', 100)->nullable();
            $table->string('puesto', 100)->nullable();

            // Restaurar user_id nullable con nullOnDelete
            $table->unsignedBigInteger('user_id')->nullable()->change();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
