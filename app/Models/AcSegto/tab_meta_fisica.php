<?php

namespace matriz\Models\AcSegto;

use Illuminate\Database\Eloquent\Model;

class tab_meta_fisica extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'ac_seguimiento.tab_meta_fisica';

    public static $validarCrear = array(
//        "codigo" => "required|min:1|max:4|unique:tab_aplicacion,co_aplicacion",
//        "aplicacion" => "required|min:1|max:1200|unique:tab_aplicacion,de_aplicacion"
    );

    public static $validarEditar = array(
        "meta_modificada" => "required|numeric",
//        "meta_actualizada" => "required|numeric",
        "obtenido" => "required|numeric",
//        "corte" => "required|numeric",
        "responsable" => "required",
        "municipio" => "required|numeric",
        "parroquia" => "required|numeric"
    );

    public static $validarCrearMeta = array(
        "actividad" => "required",
        "unidad_medida" => "required|numeric",
        "fecha_inicio" => "required|date_format:d/m/Y|before:fecha_culminacion",
        "fecha_culminacion" => "required|date_format:d/m/Y|after:fecha_inicio",
        "responsable" => "required"
    );

    public static $validarEditarMeta = array(
        "actividad" => "required",
        "unidad_medida" => "required|numeric",
        "programado_anual" => "required|numeric",
        "fecha_inicio" => "required|date_format:d/m/Y|before:fecha_culminacion",
        "fecha_culminacion" => "required|date_format:d/m/Y|after:fecha_inicio",
        "responsable" => "required",
        "desvio" => "required"
    );

    public static $validarEditarDesvio = array(
        "actividad" => "required",
        "unidad_medida" => "required|numeric",
        "programado_anual" => "required|numeric",
        "fecha_inicio" => "required|date_format:d/m/Y|before:fecha_culminacion",
        "fecha_culminacion" => "required|date_format:d/m/Y|after:fecha_inicio",
        "responsable" => "required",
        "desvio" => "required"
    );    
    
}
