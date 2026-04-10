<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaProducto;

class CategoriasProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Papelería', 'Ferretería', 'Tecnología e Informática',
            'Limpieza', 'Mobiliario', 'Equipo de oficina',
            'Material eléctrico', 'Servicios profesionales',
            'Consumibles', 'Otro',
        ];

        foreach ($categorias as $nombre) {
            CategoriaProducto::firstOrCreate(['nombre' => $nombre]);
        }
    }
}