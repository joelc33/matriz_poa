<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class tab_proyecto_localizacion extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't33_proyecto_localizacion';

    public static $cerrarProyecto = array(
        'proyecto_localizacion'  => 'required|exists:t26_proyectos,id_proyecto,edo_reg,true,co_estatus,1',
        'ambito_localizacion' => "required|integer"
    );
}
