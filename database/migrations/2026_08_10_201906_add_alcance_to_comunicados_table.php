<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comunicados', function (Blueprint $table) {
            $table->enum('alcance', ['todos', 'segmentado'])
                  ->default('todos')
                  ->after('archivo');
            $table->timestamp('notificado_en')->nullable()->after('alcance');
            $table->unsignedInteger('notificados_count')->default(0)->after('notificado_en');
        });
    }

    public function down(): void
    {
        Schema::table('comunicados', function (Blueprint $table) {
            $table->dropColumn(['alcance', 'notificado_en', 'notificados_count']);
        });
    }
};