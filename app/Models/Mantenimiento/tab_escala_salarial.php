<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_escala_salarial extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_escala_salarial';

    public static $validarCrear = array(
        "tipo_empleado" => "required|integer",
        "grupo" => "required|min:1|max:10",
        "escala_salarial" => "required|min:1|max:600",
        //"masculino" => "required|integer",
        //"femenino" => "required|integer",
        //"sueldo" => "required|numeric|min:0",
    );

    public static $validarEditar = array(
        "tipo_empleado" => "required|integer",
        "grupo" => "required|min:1|max:10",
        "escala_salarial" => "required|min:1|max:600",
        //"masculino" => "required|integer",
        //"femenino" => "required|integer",
        //"sueldo" => "required|numeric|min:0",
    );
}
