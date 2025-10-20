<?php

namespace matriz\Models\ProySegto;

use Illuminate\Database\Eloquent\Model;

class tab_proyecto extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'proyecto_seguimiento.tab_proyecto';

    public static $validarCrear = array(
        "ejercicio" => "required|numeric",
        "proyecto" => "required|numeric"
    );

    public static $validarEditar = array(
        "objetivo" => "required",
        "descripcion" => "required",
        "observacion" => "required"
    );

    public static $validarEditar005 = array(
        "programado_anual" => "required",
        "tipo_indicador" => "required",
        "nombre_indicador" => "required",
        "valor_objetivo" => "required",
        "valor_obtenido" => "required",
        "cumplimiento" => "required",
        "indicador" => "required",
        "formula" => "required"
    );

}
