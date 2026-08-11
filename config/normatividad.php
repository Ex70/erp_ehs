<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Categorías de documentos normativos
    |--------------------------------------------------------------------------
    | La clave se guarda en la columna `categoria`. Agregar una categoría nueva
    | aquí la hace aparecer automáticamente en las pestañas y en el selector del
    | formulario, sin tocar migraciones ni vistas.
    */
    'categorias' => [
        'politicas' => [
            'nombre'   => 'Políticas',
            'singular' => 'Política',
            'icono'    => '📌',
        ],
        'protocolos' => [
            'nombre'   => 'Protocolos',
            'singular' => 'Protocolo',
            'icono'    => '🔧',
        ],
        'reglamentos' => [
            'nombre'   => 'Reglamentos',
            'singular' => 'Reglamento',
            'icono'    => '📋',
        ],
        'nom' => [
            'nombre'   => 'NOMs',
            'singular' => 'NOM',
            'icono'    => '🏛️',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Aviso de vencimiento
    |--------------------------------------------------------------------------
    | Días de anticipación con los que un documento se marca como "Por vencer".
    */
    'dias_aviso_vencimiento' => env('NORMATIVIDAD_DIAS_AVISO', 30),

    /*
    |--------------------------------------------------------------------------
    | Archivos adjuntos
    |--------------------------------------------------------------------------
    | Se almacenan en el disco privado `local` (storage/app/normatividad) y se
    | sirven a través del controlador, nunca por URL pública directa.
    */
    'archivo' => [
        'disco'      => 'local',
        'directorio' => 'normatividad',
        'max_kb'     => env('NORMATIVIDAD_MAX_KB', 10240), // 10 MB
        'mimes'      => 'pdf,doc,docx,xls,xlsx,ppt,pptx',
    ],

];
