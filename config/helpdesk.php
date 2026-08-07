<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Departamento que atiende la Mesa de Ayuda
    |--------------------------------------------------------------------------
    | Determina qué usuarios aparecen como técnicos asignables en un ticket.
    | Se resuelve primero por clave; si no hay coincidencia, por nombre.
    */

    'departamento_soporte' => [
        'clave'  => env('HELPDESK_DEPTO_CLAVE', 'SIS'),
        'nombre' => env('HELPDESK_DEPTO_NOMBRE', 'Sistemas'),
    ],

];