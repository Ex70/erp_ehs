<?php

/*
|--------------------------------------------------------------------------
| Logística y Entregas (submódulo de Adquisiciones)
|--------------------------------------------------------------------------
| Valores de negocio migrados del prototipo SISREM v24. Lo que antes estaba
| fijo en el HTML vive aquí para ajustarlo sin tocar código.
*/

return [

    /*
    | Departamento cuyos colaboradores activos aparecen como responsables
    | (entrega, elaboró, reportó, destinatario del recurso).
    | Se busca primero por clave; si no hay clave, por nombre.
    */
    'departamento_responsables' => [
        'clave'  => env('LOGISTICA_DEPTO_CLAVE'),
        'nombre' => env('LOGISTICA_DEPTO_NOMBRE', 'Adquisiciones'),
    ],

    /*
    | Catálogo de unidades vehiculares
    */
    'combustibles' => [
        'gasolina' => 'Gasolina',
        'diesel'   => 'Diésel',
    ],

    /*
    | Notas de remisión — configuración por clave de la tabla `empresas`.
    | Comodines del folio: {ddmmaaaa}  {init} (3 letras)  {seq} (consecutivo anual, 3 dígitos)
    | init_de_producto: true = {init} sale de la 1a partida; false = del cliente.
    */
    'remisiones' => [
        'empresas' => [
            'AZA' => [
                'formato_folio'    => 'AZR/{ddmmaaaa}/{init}',
                'init_de_producto' => true,
                'responsables'     => false,
                'num_remision'     => false,
                'cargo_recibe'     => false,
                'imagenes'         => false,
                'color'            => '#ff7a3c',
                'razon_social'     => 'L.A. Rafael Aldair Azamar Ramírez',
                'rfc'              => 'AARR940808EG6',
                'direccion'        => 'Calle Luis Hidalgo Monroy No. 33, Col. Rafael Lucio, Xalapa, Veracruz. C.P. 91110',
                'telefono'         => '22-84-21-41-30',
                'email'            => 'aldairazamar@gmail.com',
            ],
            'CEHS' => [
                'formato_folio'    => 'CEHS-{seq}',
                'init_de_producto' => false,
                'responsables'     => true,
                'num_remision'     => true,
                'cargo_recibe'     => false,
                'imagenes'         => false,
                'color'            => '#888888',
                'razon_social'     => 'COMERCIALIZADORA EHS S.A. DE C.V.',
                'rfc'              => null,
                'direccion'        => 'CALLE MONTE VERDE NO.6 COLONIA MÁRTIRES DE CHICAGO ENTRE CALLE MONTES DE XALAPA, XALAPA, VERACRUZ. C.P. 91094',
                'telefono'         => '22-88-55-67-74',
                'email'            => 'comercializadoraehs@hotmail.com',
            ],
            'EHS' => [
                'formato_folio'    => 'EHS{ddmmaaaa}{seq}',
                'init_de_producto' => false,
                'responsables'     => true,
                'num_remision'     => false,
                'cargo_recibe'     => true,
                'imagenes'         => true,
                'color'            => '#ff5722',
                'razon_social'     => 'ELÉCTRICA HIDRÁULICA DEL SURESTE S.A. DE C.V.',
                'rfc'              => 'EHS 150529ME8',
                'direccion'        => 'ALABASTRO #22. DIAMANTE, XALAPA, VER. C.P. 91196',
                'telefono'         => '22-93-64-15-78',
                'email'            => 'ehsdireccion@hotmail.com',
            ],
            'MHR' => [
                'formato_folio'    => 'MHR-{ddmmaaaa}-{init}',
                'init_de_producto' => true,
                'responsables'     => true,
                'num_remision'     => false,
                'cargo_recibe'     => false,
                'imagenes'         => true,
                'color'            => '#4caf50',
                'razon_social'     => 'CORPORATIVO MAROHER, S.A DE C.V.',
                'rfc'              => 'CMA-110105BN0',
                'direccion'        => 'CALLE CAÑADA #20, LOMAS DEL TEJAR, C.P. 91065 XALAPA, VER.',
                'telefono'         => '(228)857-70-66',
                'email'            => 'dir.maroher@hotmail.com',
            ],
        ],
    ],

    /*
    | Solicitud de combustible
    */
    'combustible' => [
        'prefijo_folio'    => 'SOL-COMB',
        'empresa_clave'    => 'MHR',
        'cliente'          => 'GRUPO QUETZALCOATL',
        'cotizacion'       => 'GASOLINA SEMANAL',
        'montos_sugeridos' => [600, 700, 1000],
        'banco'            => 'FERCHE GAS',
        'cuenta'           => 'N/A',
        'forma_pago'       => 'MONEDERO',
        'firmantes' => [
            'reviso_1' => ['nombre' => 'JULIAN MENDEZ ABURTO',         'cargo' => 'JEFE DE ADQUISICIONES'],
            'reviso_2' => ['nombre' => 'ISAAC JORDAN LOPEZ HERNANDEZ', 'cargo' => 'GERENTE ADMINISTRATIVO Y FINANCIERO'],
            'autorizo' => ['nombre' => 'OLIVIA BAUTISTA CASTILLO',     'cargo' => 'SUBDIRECCIÓN DE ADMINISTRACIÓN Y FINANZAS'],
        ],
    ],

    /*
    | Mantenimiento programado (preventivos / alineación y balanceo)
    | Los intervalos son el valor inicial de cada unidad nueva; cada unidad
    | puede tener los suyos (p. ej. TORNADO cada 6,000 km).
    */
    'mantenimiento' => [
        'dias_aviso'   => (int) env('LOGISTICA_MANT_DIAS_AVISO', 15),
        'km_aviso'     => (int) env('LOGISTICA_MANT_KM_AVISO', 1000),
        'dias_urgente' => 3,
        'km_urgente'   => 200,
        'intervalos' => [
            'preventivo' => ['meses' => 6, 'km' => 10000],
            'alineacion' => ['meses' => 6, 'km' => null],
        ],
    ],

    /*
    | Servicios correctivos
    */
    'correctivos' => [
        'taller_default' => 'TALLER INTERNO',
        'estatus' => [
            'reportado'   => ['label' => 'Reportado',      'badge' => 'badge-danger'],
            'diagnostico' => ['label' => 'En diagnóstico', 'badge' => 'bg-orange'],
            'reparacion'  => ['label' => 'En reparación',  'badge' => 'badge-warning'],
            'garantia'    => ['label' => 'En garantía',    'badge' => 'badge-info'],
            'completado'  => ['label' => 'Completado',     'badge' => 'badge-success'],
        ],
        'sistemas' => [
            'MOTOR', 'TRANSMISIÓN', 'FRENOS', 'SUSPENSIÓN', 'DIRECCIÓN',
            'ELÉCTRICO', 'ENFRIAMIENTO', 'COMBUSTIBLE', 'ESCAPE',
            'AIRE ACONDICIONADO', 'LLANTAS', 'CARROCERÍA', 'INTERIOR', 'OTRO',
        ],
    ],
];