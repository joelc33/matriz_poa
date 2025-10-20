<?php

namespace matriz\Models\Autenticacion;

use Illuminate\Database\Eloquent\Model;

class tab_rol extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'autenticacion.tab_rol';

    public static $validarCrear = array(
        "nombre" => "required|min:2|max:50|unique:local.autenticacion.tab_rol,de_rol"
    );

    public static $validarEditar = array(
        "nombre" => "required|min:2|max:50"
    );
}
