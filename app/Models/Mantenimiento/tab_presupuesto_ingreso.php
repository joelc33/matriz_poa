<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_presupuesto_ingreso extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_presupuesto_ingreso';

    public static $validarCrear = array(
          "partida" => "required|min:1|max:12",
          "denominacion" => "required|min:1|max:1200",
      "monto" => "required|numeric",
      );

    public static $validarEditar = array(
    "partida" => "required|min:1|max:12",
        "denominacion" => "required|min:1|max:1200",
    "monto" => "required|numeric",
    );
}
