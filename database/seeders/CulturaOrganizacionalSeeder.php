<?php

namespace Database\Seeders;

use App\Models\CulturaItem;
use App\Models\CulturaSeccion;
use Illuminate\Database\Seeder;

class CulturaOrganizacionalSeeder extends Seeder
{
    /**
     * Contenido inicial tomado del prototipo grupo-quetzalcoatl-rrhh-v6.html (DB.cultura).
     * Es idempotente: puede ejecutarse varias veces sin duplicar.
     */
    public function run(): void
    {
        /* ── Secciones de texto ── */
        $secciones = [
            [
                'clave'  => CulturaSeccion::CLAVE_PRESENTACION,
                'titulo' => 'Quiénes Somos',
                'icono'  => '🏢',
                'orden'  => 1,
                'texto'  => 'Grupo Quetzalcóatl es un conglomerado empresarial con presencia en el sureste de México, especializado en soluciones eléctricas, hidráulicas y servicios profesionales. Nos distinguimos por nuestra calidad, compromiso y el talento de nuestro capital humano.',
            ],
            [
                'clave'  => CulturaSeccion::CLAVE_MISION,
                'titulo' => 'Misión',
                'icono'  => '🎯',
                'orden'  => 2,
                'texto'  => 'Proveer soluciones integrales de calidad en los sectores eléctrico, hidráulico y de servicios profesionales, generando valor para nuestros clientes, colaboradores y comunidad, con estándares de excelencia y responsabilidad.',
            ],
            [
                'clave'  => CulturaSeccion::CLAVE_VISION,
                'titulo' => 'Visión',
                'icono'  => '🔭',
                'orden'  => 3,
                'texto'  => 'Ser el grupo empresarial referente del sureste mexicano, reconocido por la innovación, calidad de servicio y el desarrollo de nuestro capital humano, expandiendo nuestra presencia a nivel nacional.',
            ],
            [
                'clave'  => CulturaSeccion::CLAVE_HISTORIA,
                'titulo' => 'Nuestra Historia',
                'icono'  => '📖',
                'orden'  => 4,
                'texto'  => 'Fundado con la visión de transformar el sector energético e hidráulico del sureste mexicano, Grupo Quetzalcóatl ha crecido hasta convertirse en un referente regional. A lo largo de nuestra trayectoria hemos concretado proyectos de alta complejidad técnica, formado equipos de alto rendimiento y construido relaciones duraderas con nuestros clientes. Cada empresa del grupo aporta su especialización para ofrecer soluciones completas e integrales.',
            ],
            [
                'clave'  => CulturaSeccion::CLAVE_OBJETIVOS,
                'titulo' => 'Objetivos Estratégicos',
                'icono'  => '🎯',
                'orden'  => 5,
                'texto'  => null,
            ],
        ];

        foreach ($secciones as $seccion) {
            CulturaSeccion::firstOrCreate(['clave' => $seccion['clave']], $seccion);
        }

        /* ── Diapositivas del carrusel ── */
        $slides = [
            ['titulo' => 'Grupo Quetzalcóatl', 'descripcion' => 'Comprometidos con la excelencia y el desarrollo humano', 'icono' => '🦅', 'color' => '#1a0a0a'],
            ['titulo' => 'Nuestra Misión',     'descripcion' => 'Proveer soluciones eléctricas e hidráulicas de alta calidad con compromiso y profesionalismo', 'icono' => '🎯', 'color' => '#0a1a0a'],
            ['titulo' => 'Nuestra Visión',     'descripcion' => 'Ser el grupo empresarial líder en el sureste mexicano, reconocido por calidad e innovación', 'icono' => '🔭', 'color' => '#0a0a1a'],
        ];
        $this->sembrarItems(CulturaItem::TIPO_SLIDE, $slides);

        /* ── Valores ── */
        $valores = [
            ['titulo' => 'Innovación',             'descripcion' => 'Buscamos constantemente nuevas formas de mejorar nuestros procesos y servicios.', 'icono' => '💡'],
            ['titulo' => 'Compromiso',             'descripcion' => 'Cumplimos con nuestros clientes, colaboradores y la sociedad con responsabilidad y ética.', 'icono' => '🤝'],
            ['titulo' => 'Calidad',                'descripcion' => 'Mantenemos los más altos estándares en cada proyecto y servicio que ofrecemos.', 'icono' => '⭐'],
            ['titulo' => 'Desarrollo',             'descripcion' => 'Fomentamos el crecimiento personal y profesional de cada miembro del equipo.', 'icono' => '🌱'],
            ['titulo' => 'Integridad',             'descripcion' => 'Actuamos con honestidad y transparencia en todas nuestras relaciones.', 'icono' => '🛡️'],
            ['titulo' => 'Responsabilidad Social', 'descripcion' => 'Contribuimos positivamente al desarrollo de las comunidades donde operamos.', 'icono' => '🌍'],
        ];
        $this->sembrarItems(CulturaItem::TIPO_VALOR, $valores);

        /* ── Objetivos estratégicos ── */
        $objetivos = [
            ['titulo' => 'Expandir nuestra presencia en los estados del sureste mexicano'],
            ['titulo' => 'Incrementar la satisfacción del cliente mediante mejora continua'],
            ['titulo' => 'Desarrollar y retener al mejor talento humano de la región'],
            ['titulo' => 'Implementar tecnologías sostenibles y respetuosas con el medio ambiente'],
            ['titulo' => 'Fortalecer la cultura organizacional y el bienestar de nuestros colaboradores'],
            ['titulo' => 'Diversificar nuestra cartera de servicios y soluciones'],
        ];
        $this->sembrarItems(CulturaItem::TIPO_OBJETIVO, $objetivos);

        /* ── Empresas del grupo ── */
        $empresas = [
            ['titulo' => 'Eléctrica Hidráulica del Sureste S.A. de C.V.', 'descripcion' => 'Especializada en proyectos eléctricos e hidráulicos de mediana y alta tensión. Líder en instalaciones industriales en el sureste.', 'icono' => '⚡', 'color' => '#1a1a3a'],
            ['titulo' => 'Corporativo Maroher S.A. de C.V.',              'descripcion' => 'Gestión corporativa y administrativa del grupo. Coordinación estratégica de recursos y operaciones.', 'icono' => '🏢', 'color' => '#1a2a1a'],
            ['titulo' => 'Comercializadora EHS S.A. de C.V.',             'descripcion' => 'Comercialización de materiales y equipos eléctricos e hidráulicos. Distribución regional.', 'icono' => '🛒', 'color' => '#2a1a1a'],
            ['titulo' => 'Nexus Conciliación y Mediación S.A. de C.V.',   'descripcion' => 'Servicios jurídicos especializados en resolución de conflictos y mediación empresarial.', 'icono' => '⚖️', 'color' => '#1a1a2a'],
            ['titulo' => 'Rafael Aldair Azamar Ramírez',                  'descripcion' => 'Servicios profesionales especializados y consultoría técnica para proyectos de gran escala.', 'icono' => '👔', 'color' => '#2a2a1a'],
        ];
        $this->sembrarItems(CulturaItem::TIPO_EMPRESA, $empresas);
    }

    private function sembrarItems(string $tipo, array $items): void
    {
        foreach ($items as $indice => $item) {
            CulturaItem::firstOrCreate(
                ['tipo' => $tipo, 'titulo' => $item['titulo']],
                array_merge($item, [
                    'tipo'   => $tipo,
                    'orden'  => $indice + 1,
                    'activo' => true,
                ])
            );
        }
    }
}
