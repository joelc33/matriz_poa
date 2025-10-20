<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_objetivo_sectorial extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_objetivo_sectorial';

    public static $validarCrear = array(
          "id_tab_ejercicio_fiscal" => "required|numeric",
      "id_tab_sectores" => "required|numeric|composite_unique:tab_objetivo_sectorial,id_tab_sectores,id_tab_ejercicio_fiscal",
          "objetivo" => "required",
    );

    public static $validarEditar = array(
      "id_tab_sectores" => "required|numeric",
          "objetivo" => "required"
    );
}
