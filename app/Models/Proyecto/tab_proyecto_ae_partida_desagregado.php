<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class tab_proyecto_ae_partida_desagregado extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'tab_proyecto_ae_partida_desagregado';

    public static $validarDesagregado = array(
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
