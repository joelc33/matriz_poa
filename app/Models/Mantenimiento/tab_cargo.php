<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_cargo extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_cargo';

    public static $validarCrear = array(
        "cargo" => "required|min:1|max:1200|unique:tab_cargo,de_cargo"
    );

    public static $validarEditar = array(
        "cargo" => "required|min:1|max:1200"
    );
}
