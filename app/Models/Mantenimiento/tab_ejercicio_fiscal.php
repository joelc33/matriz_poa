<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_ejercicio_fiscal extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_ejercicio_fiscal';

    public static $seleccionar = array(
        "ejercicio" => "required|numeric|min:2015|max:3000"
    );

    public static $validarCrear = array(
        "periodo" => "required|numeric|min:2015|max:3000|unique:tab_ejercicio_fiscal,id"
    );
}
