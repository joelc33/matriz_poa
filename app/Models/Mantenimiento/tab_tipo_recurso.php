<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_tipo_recurso extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_tipo_recurso';

    public static $validarCrear = array(
        "codigo" => "required|min:1|max:20|unique:tab_tipo_recurso,de_codigo_recurso",
        "recurso" => "required|min:1|max:1200"
    );

    public static $validarEditar = array(
        "codigo" => "required|min:1|max:20",
        "recurso" => "required|min:1|max:1200"
    );
}
