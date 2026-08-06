<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('departamento_id')->nullable()->after('puesto_id');

            $table->foreign('departamento_id')
                  ->references('id')->on('departamentos')
                  ->nullOnDelete();

            // Índice compuesto: acelera los reportes por departamento + puesto
            $table->index(['departamento_id', 'puesto_id'], 'users_departamento_puesto_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropIndex('users_departamento_puesto_index');
            $table->dropColumn('departamento_id');
        });
    }
};