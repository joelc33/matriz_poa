<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_planes extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_planes';

    public static $validarCrear = array(
        "codigo" => "required|min:1|max:10",
        "objetivo_historico" => "min:1|max:10",
        "objetivo_nacional" => "min:1|max:10",
        "objetivo_estrategico" => "min:1|max:10",
        "objetivo_general" => "min:1|max:10",
        "nivel" => "required|numeric|min:1|max:10",
        "descripcion" => "required|min:1|max:1200"
    );

    public static $validarEditar = array(
        "codigo" => "required|min:1|max:10",
        "objetivo_historico" => "min:1|max:10",
        "objetivo_nacional" => "min:1|max:10",
        "objetivo_estrategico" => "min:1|max:10",
        "objetivo_general" => "min:1|max:10",
        "nivel" => "required|numeric|min:1|max:10",
        "descripcion" => "required|min:1|max:1200"
    );

}
