<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_distribucion_municipio extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_distribucion_municipio';

    public static $validarCrear = array(
        "partida" => "required|numeric",
        "base_censo" => "required|numeric",
        "superficie_km" => "required|numeric"
    );

    public static $validarEditar = array(
        "partida" => "required|numeric",
        "base_censo" => "required|numeric",
        "factor_poblacion" => "required|numeric",
        "cuatrocinco_ppi" => "required|numeric",
        "cincocero_fpp" => "required|numeric",
        "superficie_km" => "required|numeric",
        "superficie_factor" => "required|numeric",
        "extension_territorio" => "required|numeric"
    );
}
