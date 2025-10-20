<?php

namespace matriz\Models\AcSegto;

use Illuminate\Database\Eloquent\Model;

class tab_meta_financiera extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'ac_seguimiento.tab_meta_financiera';

    public static $validarEditar = array(
        "modificado_anual" => "required|numeric",
        "comprometido" => "required|numeric",
        "causado" => "required|numeric",
        "pagado" => "required|numeric"
    );

    public static $validarCrearMeta = array(
        "municipio" => "required|numeric",
        "parroquia" => "required|numeric",
        "presupuesto" => "required|numeric",
        "partida" => "required",
        "fuente_financiamiento" => "required|numeric"
    );

    public static $validarEditarMeta = array(
        "municipio" => "required|numeric",
        "parroquia" => "required|numeric",
        "presupuesto" => "required|numeric",
        "partida" => "required",
        "fuente_financiamiento" => "required|numeric"
    );

}
