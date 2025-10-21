<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_sectores extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_sectores';

    public static $validarCrear = array(
        "sector" => "required|min:2|max:2",
//        "sub_sector" => "min:2|max:2",
//        "nivel" => "required|numeric|min:1|max:10",
        "descripcion" => "required|min:1|max:1200"
    );

    public static $validarEditar = array(
        "sector" => "required|min:2|max:2",
//        "sub_sector" => "min:2|max:2",
//        "nivel" => "required|numeric|min:1|max:10",
        "descripcion" => "required|min:1|max:1200"
    );
}
