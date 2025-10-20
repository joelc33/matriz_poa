<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_lapso extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_lapso';

    public static $validarCrear = array(
        "ejercicio" => "required|numeric",
        "periodo" => "required|numeric",
        "descripcion" => "required",
        'fecha_inicio' => "required|date_format:d/m/Y|before:fecha_cierre",
        'fecha_cierre' => "required|date_format:d/m/Y|after:fecha_inicio",
    );

    public static $validarEditar = array(
        "ejercicio" => "required|numeric",
        "periodo" => "required|numeric",
        "descripcion" => "required",
        'fecha_inicio' => "required|date_format:d/m/Y|before:fecha_cierre",
        'fecha_cierre' => "required|date_format:d/m/Y|after:fecha_inicio",
    );

}
