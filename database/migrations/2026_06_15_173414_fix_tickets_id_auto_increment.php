<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soltar FK de tablas dependientes
        DB::statement('ALTER TABLE ticket_asignaciones DROP FOREIGN KEY ticket_asignaciones_ticket_id_foreign;');
        DB::statement('ALTER TABLE ticket_seguimientos DROP FOREIGN KEY ticket_seguimientos_ticket_id_foreign;');

        // Agregar AUTO_INCREMENT
        DB::statement('ALTER TABLE tickets MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;');

        // Restaurar FK
        DB::statement('ALTER TABLE ticket_asignaciones ADD CONSTRAINT ticket_asignaciones_ticket_id_foreign FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE;');
        DB::statement('ALTER TABLE ticket_seguimientos ADD CONSTRAINT ticket_seguimientos_ticket_id_foreign FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE;');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ticket_asignaciones DROP FOREIGN KEY ticket_asignaciones_ticket_id_foreign;');
        DB::statement('ALTER TABLE ticket_seguimientos DROP FOREIGN KEY ticket_seguimientos_ticket_id_foreign;');

        DB::statement('ALTER TABLE tickets MODIFY id BIGINT UNSIGNED NOT NULL;');

        DB::statement('ALTER TABLE ticket_asignaciones ADD CONSTRAINT ticket_asignaciones_ticket_id_foreign FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE;');
        DB::statement('ALTER TABLE ticket_seguimientos ADD CONSTRAINT ticket_seguimientos_ticket_id_foreign FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE;');
    }
};
