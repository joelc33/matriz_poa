<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class tab_ac_ae_partida extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't54_ac_ae_partidas';

    /**
     * The name of the "created at" column.
     */
    public const CREATED_AT = 'fecha_creacion';

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = 'fecha_actualizacion';

    public $incrementing = false;

    public static $validar_campo = array(
        "accion_centralizada" => "required|integer",
        "accion_especifica" => "required|integer",
        //"aplicacion" => "required|exists:tab_aplicacion,co_aplicacion",
        "partida" => "required|numeric|exists:tab_partidas,co_partida",
        "monto" => "numeric|min:1"
    );

    public static $validarDesagregado = array(
        "ac" => "required",
        "ae" => "required",
        "pa" => "required",
        "ge" => "required",
        "es" => "required",
        "se" => "required",
        "sse" => "required",
        "partida" => "required",
        "aplicacion" => "required|exists:tab_aplicacion,co_aplicacion",
        "denominacion" => "required",
        "monto" => "numeric|min:1"
    );
}
