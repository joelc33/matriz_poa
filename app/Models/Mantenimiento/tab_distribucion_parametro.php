<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_distribucion_parametro extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_distribucion_parametro';

    public static $validarCrear = array(
        "poblacion" => "required|numeric",
        "parte_igual" => "required|numeric",
        "parte_proporcional" => "required|numeric",
    "total_superficie" => "required|numeric",
    "extension_territorio" => "required|numeric"
    );

    public static $validarEditar = array(
    "poblacion" => "required|numeric",
        "parte_igual" => "required|numeric",
        "parte_proporcional" => "required|numeric",
    "total_superficie" => "required|numeric",
    "extension_territorio" => "required|numeric"
    );
}
