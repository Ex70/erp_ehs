<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cantidad de notificaciones mostradas en el dropdown de la campana
    |--------------------------------------------------------------------------
    */
    'dropdown_limite' => env('NOTIF_DROPDOWN_LIMITE', 6),

    /*
    |--------------------------------------------------------------------------
    | Registros por página en la bandeja de notificaciones
    |--------------------------------------------------------------------------
    */
    'por_pagina' => env('NOTIF_POR_PAGINA', 15),

    /*
    |--------------------------------------------------------------------------
    | Intervalo (segundos) del refresco automático del contador.
    | Usa 0 para desactivar el polling.
    |--------------------------------------------------------------------------
    */
    'intervalo_refresco' => env('NOTIF_INTERVALO_REFRESCO', 60),

    /*
    |--------------------------------------------------------------------------
    | Estilos por tipo de notificación (coincidencia parcial sobre la clase)
    |--------------------------------------------------------------------------
    */
    'estilos' => [
        'Comunicado'    => ['icono' => 'far fa-newspaper',  'color' => 'info'],
        'Ticket'        => ['icono' => 'fas fa-life-ring',  'color' => 'warning'],
        'Adquisicion'   => ['icono' => 'fas fa-shopping-cart', 'color' => 'success'],
        'Solvencia'     => ['icono' => 'fas fa-file-invoice-dollar', 'color' => 'primary'],
        'Normatividad'  => ['icono' => 'fas fa-gavel',      'color' => 'secondary'],
        'Red'           => ['icono' => 'fas fa-network-wired', 'color' => 'dark'],
    ],

    'estilo_por_defecto' => ['icono' => 'far fa-bell', 'color' => 'primary'],
];