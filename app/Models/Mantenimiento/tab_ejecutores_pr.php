<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_ejecutores_pr extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_ejecutores_pr';

    public static $validarCrear = array(
        "ac" => "required|numeric",
        "numero" => "required|min:1|max:10",
        "nombre" => "required|min:1|max:1200"
    );

    public static $validarEditar = array(
        "numero" => "required|min:1|max:10",
        "nombre" => "required|min:1|max:1200"
    );
}
