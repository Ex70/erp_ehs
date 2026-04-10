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
        Schema::table('users', function (Blueprint $table) {
            $table->string('registro_token', 64)
                ->nullable()
                ->unique()
                ->after('activo');
            $table->timestamp('registro_completado_at')
                ->nullable()
                ->after('registro_token');
        });
    }

    public function down(): void{
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registro_token', 'registro_completado_at']);
        });
    }
};
