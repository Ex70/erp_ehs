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
        DB::statement('ALTER TABLE tickets MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tickets MODIFY id BIGINT UNSIGNED NOT NULL;');
    }
};
