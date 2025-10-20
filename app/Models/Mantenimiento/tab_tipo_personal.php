<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_tipo_personal extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_tipo_personal';

    public static $validarCrear = array(
        "padre" => "required|numeric",
        "codigo" => "required",
        "descripcion" => "required|min:1|max:1200"
    );

    public static $validarEditar = array(
        "padre" => "required|numeric",
        "codigo" => "required",
        "descripcion" => "required|min:1|max:1200"
    );
}
