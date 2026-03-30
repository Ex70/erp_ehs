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
            $table->string('username')->unique()->after('name');
            $table->foreignId('puesto_id')
                ->nullable()
                ->constrained('puestos')
                ->nullOnDelete()
                ->after('username');
            $table->string('avatar')->nullable()->after('puesto_id');
            $table->boolean('activo')->default(true)->after('avatar');
        });
}

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['puesto_id']);
            $table->dropColumn(['username', 'puesto_id', 'avatar', 'activo']);
        });
    }
};
