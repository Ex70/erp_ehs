<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dispositivo;
use App\Models\Marca;

class DispositivosMarcasSeeder extends Seeder
{
    public function run(): void
    {
        $dispositivos = [
            'Laptop', 'Desktop', 'Impresora',
            'Servidor', 'Switch', 'Router',
            'Tablet', 'Teléfono IP', 'Otro',
        ];

        foreach ($dispositivos as $nombre) {
            Dispositivo::firstOrCreate(['nombre' => $nombre]);
        }

        $marcas = [
            'ACER', 'HP', 'Dell', 'Lenovo', 'Apple',
            'Asus', 'Samsung', 'Epson', 'Canon',
            'Cisco', 'TP-Link', 'D-Link', 'Otro',
        ];

        foreach ($marcas as $nombre) {
            Marca::firstOrCreate(['nombre' => $nombre]);
        }
    }
}