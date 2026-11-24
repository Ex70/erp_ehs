<?php

namespace App\Services\Solvencias;

use RuntimeException;

/**
 * Regla de la cadena de custodia que impide una acción. El mensaje se
 * muestra tal cual al usuario.
 */
class AccionNoPermitida extends RuntimeException
{
}
