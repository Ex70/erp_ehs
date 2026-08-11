<?php

namespace Database\Seeders;

use App\Models\DocumentoNormativo;
use Illuminate\Database\Seeder;

class NormatividadSeeder extends Seeder
{
    /**
     * Documentos tomados del prototipo grupo-quetzalcoatl-rrhh-v6.html
     * (DB.normatividad). Sin archivos adjuntos: se suben desde la interfaz.
     * Idempotente.
     */
    public function run(): void
    {
        $documentos = [
            [
                'categoria'         => 'politicas',
                'titulo'            => 'Política de Puntualidad y Asistencia',
                'version'           => 'v2.1',
                'vigencia'          => '2026-12-31',
                'responsable'       => 'Recursos Humanos',
                'descripcion'       => 'Establece los lineamientos sobre horarios, registros de asistencia y tolerancias permitidas.',
                'fecha_publicacion' => '2026-01-10',
            ],
            [
                'categoria'         => 'politicas',
                'titulo'            => 'Política de Código de Vestimenta',
                'version'           => 'v1.3',
                'vigencia'          => '2026-12-31',
                'responsable'       => 'Recursos Humanos',
                'descripcion'       => 'Define los estándares de imagen y presentación personal dentro de las instalaciones.',
                'fecha_publicacion' => '2025-09-01',
            ],
            [
                'categoria'         => 'protocolos',
                'titulo'            => 'Protocolo de Seguridad e Higiene en Taller',
                'version'           => 'v3.0',
                'vigencia'          => '2026-06-30',
                'responsable'       => 'Seguridad Industrial',
                'descripcion'       => 'Procedimientos obligatorios de seguridad para personal de taller y campo.',
                'fecha_publicacion' => '2026-02-01',
            ],
            [
                'categoria'         => 'reglamentos',
                'titulo'            => 'Reglamento Interior de Trabajo',
                'version'           => 'v5.0',
                'vigencia'          => '2027-01-01',
                'responsable'       => 'Jurídico',
                'descripcion'       => 'Marco normativo general que regula la relación laboral dentro del grupo.',
                'fecha_publicacion' => '2025-01-01',
            ],
            [
                'categoria'         => 'nom',
                'titulo'            => 'NOM-035-STPS-2018 Factores de Riesgo Psicosocial',
                'version'           => 'v1.0',
                'vigencia'          => '2026-10-23',
                'responsable'       => 'Capital Humano',
                'descripcion'       => 'Norma oficial que establece elementos para identificar, analizar y prevenir factores de riesgo psicosocial.',
                'fecha_publicacion' => '2024-10-23',
            ],
        ];

        foreach ($documentos as $documento) {
            DocumentoNormativo::firstOrCreate(
                ['categoria' => $documento['categoria'], 'titulo' => $documento['titulo']],
                $documento + ['activo' => true]
            );
        }
    }
}
