<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class tab_proyecto_ae_partida extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't42_proyecto_acc_espec_partida';

    //public $incrementing = false;

    //protected $primaryKey = ['co_partida_acc_espec', 'id_accion'];
    protected $primaryKey = 'co_partida_acc_espec';

    /**
     * The name of the "created at" column.
     */
    public const CREATED_AT = 'fecha_creacion';

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = 'fecha_actualizacion';

    public static $validar_campo = array(
        "proyecto" => "required",
        //"aplicacion" => "required|exists:tab_aplicacion,co_aplicacion",
        "partida" => "required|numeric|exists:tab_partidas,co_partida",
        "monto" => "numeric|min:1"
    );
}
