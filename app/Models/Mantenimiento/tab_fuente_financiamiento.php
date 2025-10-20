<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_fuente_financiamiento extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_fuente_financiamiento';

    public static $validarCrear = array(
        "fondo" => "required|numeric",
        "fuente" => "required|min:1|max:1200"
    );

    public static $validarEditar = array(
        "fondo" => "required|numeric",
        "fuente" => "required|min:1|max:1200"
    );

}
