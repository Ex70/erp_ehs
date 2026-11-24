<?php

/*
|--------------------------------------------------------------------------
| Solvencias económicas · Trazabilidad (cadena de custodia)
|--------------------------------------------------------------------------
| Reglas del negocio del módulo. Los flujos (quién elabora, revisa, valida y
| autoriza en cada departamento) NO viven aquí: se configuran por persona en
| "Personas y flujos" y quedan en base de datos.
*/

return [

    /*
    | Zona horaria con la que se MUESTRAN las fechas del módulo. La app guarda
    | en UTC (config/app.php); aquí solo se convierte para la vista.
    */
    'zona_horaria' => env('SOLVENCIAS_ZONA_HORARIA', 'America/Mexico_City'),

    /*
    | Horas sin acuse a partir de las cuales una solvencia se marca en rojo.
    */
    'horas_alerta_acuse' => env('SOLVENCIAS_HORAS_ALERTA', 24),

    /*
    | Mínimos de texto para justificar acciones.
    */
    'min_observacion' => 20,   // documento que no se anexa
    'min_motivo'      => 10,   // devolución o cancelación

    /*
    | Responsabilidades posibles en una etapa. La primera etapa siempre es
    | "Elabora" y la última siempre "Autoriza"; las intermedias se eligen
    | de "verbos_intermedios".
    */
    'verbos' => ['Elabora', 'Revisa', 'Revisa y valida', 'Valida', 'Autoriza'],
    'verbos_intermedios' => ['Revisa', 'Revisa y valida', 'Valida'],

    // Verbos que dan autoridad para cancelar dentro del flujo del expediente
    'verbos_autoridad' => ['Revisa y valida', 'Valida', 'Autoriza'],

    'etapa_de' => [
        'Elabora'         => 'Elaboración',
        'Revisa'          => 'Revisión',
        'Revisa y valida' => 'Revisión y validación',
        'Valida'          => 'Validación',
        'Autoriza'        => 'Autorización',
    ],

    'pasado' => [
        'Elabora'         => 'Elaboró',
        'Revisa'          => 'Revisó',
        'Revisa y valida' => 'Revisó y validó',
        'Valida'          => 'Validó',
        'Autoriza'        => 'Autorizó',
    ],

    /*
    | Folios: {PREFIJO}-{AÑO}-{0000}, consecutivo por prefijo y año.
    | El prefijo se define por flujo (por defecto la clave del departamento;
    | Sistemas usa SIST para continuar la serie del formato anterior).
    */
    'folio' => [
        'digitos' => 4,
    ],

    /*
    | Archivos: disco privado, servidos por controlador.
    */
    'archivo' => [
        'disco'      => 'local',
        'directorio' => 'solvencias',
        'max_kb'     => env('SOLVENCIAS_MAX_KB', 10240), // 10 MB
    ],

    /*
    | Catálogo de documentos del expediente.
    |   exceptuable: si se permite "No se anexa" con observación
    |   extensiones: extensiones aceptadas al cargar
    */
    'documentos' => [
        'formato' => [
            'nombre'      => 'Formato de solvencia (con partidas)',
            'exceptuable' => false,
            'extensiones' => ['xlsx', 'xlsm', 'xls'],
        ],
        'cot_cliente' => [
            'nombre'      => 'Cotización enviada al cliente',
            'exceptuable' => true,
            'extensiones' => ['pdf', 'xlsx', 'xls', 'docx', 'doc', 'jpg', 'jpeg', 'png'],
        ],
        'cot_prov' => [
            'nombre'      => 'Cotización del proveedor',
            'exceptuable' => true,
            'extensiones' => ['pdf', 'xlsx', 'xls', 'docx', 'doc', 'jpg', 'jpeg', 'png'],
        ],
        'csf' => [
            'nombre'      => 'Constancia de situación fiscal',
            'exceptuable' => true,
            'extensiones' => ['pdf', 'jpg', 'jpeg', 'png'],
        ],
        'opinion' => [
            'nombre'      => 'Opinión de cumplimiento',
            'exceptuable' => true,
            'extensiones' => ['pdf', 'jpg', 'jpeg', 'png'],
        ],
        'caratula' => [
            'nombre'      => 'Carátula bancaria',
            'exceptuable' => true,
            'extensiones' => ['pdf', 'jpg', 'jpeg', 'png'],
        ],
        'domicilio' => [
            'nombre'      => 'Comprobante de domicilio',
            'exceptuable' => true,
            'extensiones' => ['pdf', 'jpg', 'jpeg', 'png'],
        ],
        'web' => [
            'nombre'      => 'Verificación de sitio web (Sistemas)',
            'exceptuable' => true,
            'extensiones' => ['pdf', 'jpg', 'jpeg', 'png', 'docx'],
        ],
        'comprobante' => [
            'nombre'      => 'Comprobante de pago',
            'exceptuable' => false,
            'extensiones' => ['pdf', 'jpg', 'jpeg', 'png', 'xml'],
        ],
    ],

    /*
    | Checklist obligatorio según el tipo de proveedor.
    */
    'requeridos' => [
        'validado' => ['formato', 'cot_cliente', 'cot_prov'],
        'nuevo'    => ['formato', 'cot_cliente', 'cot_prov', 'csf', 'opinion', 'caratula', 'domicilio', 'web'],
    ],

    /*
    | Notificaciones por evento (sincronizadas con la campana del ERP y el
    | correo encolado). "database" = campana; "mail" = correo.
    | Destinatarios (los resuelve ExpedienteService):
    |   enviada      → quien la recibe
    |   acuse        → quien la envió
    |   recordatorio → quien la tiene sin acuse
    |   devuelta     → quien la elaboró
    |   autorizada   → Contabilidad y Finanzas + quien la elaboró
    |   pagada       → quien la elaboró
    |   cancelada    → quien la elaboró y quien la tenía
    */
    'notificaciones' => [
        'enviada'      => ['database', 'mail'],
        'acuse'        => ['database'],
        'recordatorio' => ['database', 'mail'],
        'devuelta'     => ['database', 'mail'],
        'autorizada'   => ['database', 'mail'],
        'pagada'       => ['database', 'mail'],
        'cancelada'    => ['database'],
    ],

];
