<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_unidad_medida extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_unidad_medida';

    public static $validarCrear = array(
        "descripcion" => "required|min:1|max:300|unique:tab_unidad_medida,de_unidad_medida"
    );

    public static $validarEditar = array(
        "descripcion" => "required|min:1|max:300"
    );
}
