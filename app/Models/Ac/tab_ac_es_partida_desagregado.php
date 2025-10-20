<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class tab_ac_es_partida_desagregado extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'tab_ac_es_partida_desagregado';

    public static $validarDesagregado = array(
      "ac" => "required",
      "ae" => "required",
      "pa" => "required",
      "ge" => "required",
      "es" => "required",
      "se" => "required",
      "sse" => "required|min:3|max:3",
      "partida" => "required",
      "aplicacion" => "required|exists:tab_aplicacion,co_aplicacion",
      "denominacion" => "required",
      "monto" => "numeric|min:1"
    );
}
