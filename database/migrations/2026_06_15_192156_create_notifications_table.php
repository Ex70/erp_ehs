<?php
// database/migrations/2026_06_15_000001_create_notifications_table.php
// Solo necesaria si NO existe ya la tabla notifications en tu BD.
// Verifica primero con: php8.2 artisan migrate:status
// Si ya existe la tabla notifications, omite esta migración.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};