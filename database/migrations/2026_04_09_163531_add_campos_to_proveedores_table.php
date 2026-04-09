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
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('telefono_secundario', 30)->nullable()->after('telefono');
            $table->string('condiciones_pago', 100)->nullable()->after('telefono_secundario');
            $table->string('tiempo_entrega', 60)->nullable()->after('condiciones_pago');
            $table->string('direccion', 200)->nullable()->after('tiempo_entrega');
            $table->text('observaciones')->nullable()->after('direccion');
        });
    }

    public function down(): void{
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn([
                'telefono_secundario',
                'condiciones_pago',
                'tiempo_entrega',
                'direccion',
                'observaciones',
            ]);
        });
    }
};
