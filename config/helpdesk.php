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

    /*
    |--------------------------------------------------------------------------
    | Avisos de Mesa de Ayuda por Telegram
    |--------------------------------------------------------------------------
    | chat_id: ID del grupo de Telegram de Sistemas que recibe los avisos de
    |          tickets nuevos. Los grupos tienen ID negativo (supergrupos
    |          empiezan con -100). Obtenlo con: php artisan telegram:chats
    |          Si se deja vacío, no se envía nada por Telegram.
    |
    | max_descripcion: caracteres de la descripción que se incluyen en el
    |          mensaje. El detalle completo se consulta en el ERP.
    */

    'telegram' => [
        'chat_id'         => env('HELPDESK_TELEGRAM_CHAT_ID'),
        'max_descripcion' => (int) env('HELPDESK_TELEGRAM_MAX_DESCRIPCION', 300),
    ],

];
