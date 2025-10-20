<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_apertura_ef extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_apertura_ef';

    public static $validarCrear = array(
        "periodo" => "required|numeric",
        "fecha_apertura" => "required|date_format:d/m/Y|before:fecha_cierre",
        "fecha_cierre" => "required|date_format:d/m/Y|after:fecha_apertura",
        "descripcion" => "required|min:2|max:300"
    );

    public static $validarEditar = array(
        "fecha_apertura" => "required|date_format:d/m/Y|before:fecha_cierre",
        "fecha_cierre" => "required|date_format:d/m/Y|after:fecha_apertura",
        "descripcion" => "required|min:2|max:300"
    );

}
