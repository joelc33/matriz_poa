<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_aplicacion extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_aplicacion';

    //public $incrementing = false;

    public static $validarCrear = array(
        "codigo" => "required|min:1|max:4|unique:tab_aplicacion,co_aplicacion",
        "aplicacion" => "required|min:1|max:1200|unique:tab_aplicacion,de_aplicacion"
    );

    public static $validarEditar = array(
        "codigo" => "required|min:1|max:4",
        "aplicacion" => "required|min:1|max:1200"
    );
}
