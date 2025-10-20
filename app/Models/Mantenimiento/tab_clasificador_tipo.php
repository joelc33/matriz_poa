<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_clasificador_tipo extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_clasificador_tipo';

    public static $validarCrear = array(
        "ejercicio_fiscal" => "required|numeric|min:2015|max:3000",
        "tipo_personal" => "required|integer",
        "masculino" => "required|integer",
        "femenino" => "required|integer",
        "sueldo" => "required|numeric|min:0",
        "compensacion" => "required|numeric|min:0",
        "primas" => "required|numeric|min:0",
    );

    public static $validarEditar = array(
        "ejercicio_fiscal" => "required|numeric|min:2015|max:3000",
        "tipo_personal" => "required|integer",
        "masculino" => "required|integer",
        "femenino" => "required|integer",
        "sueldo" => "required|numeric|min:0",
        "compensacion" => "required|numeric|min:0",
        "primas" => "required|numeric|min:0",
    );
}
