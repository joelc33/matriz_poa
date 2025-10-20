<?php

namespace matriz\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'auxiliar/objetivo/nacional',
        'auxiliar/objetivo/estrategico',
        'auxiliar/objetivo/general',
        'auxiliar/plan/ambito',
        'auxiliar/plan/objetivo',
        'auxiliar/plan/macroproblema',
        'auxiliar/plan/nudo',
        'ac/ae/partida/storeLista',
        'ac/ae/partida/masivo',
        'proyecto/ae/partida/masivo',
        'proyecto/ae/partida/individual',
        'ac/ae/storeLista',
        'auxiliar/poa/subsector',
        'ac/guardar',
        'proyecto/storeLista',
        'ac/storeLista',
        'ac/ae/partida/cargar',
        'proyecto/cerrar',
        'ac/ae/partida/desagregado/storeLista',
        'proyecto/ae/partida/desagregado',
        'proyecto/ae/partida/desagregado/lista',
        'ac/cerrar',
    ];
}
