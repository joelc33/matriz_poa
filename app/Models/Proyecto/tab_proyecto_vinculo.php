<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class tab_proyecto_vinculo extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't32_proyecto_vinculos';

    public static $cerrarProyecto = array(
      'proyecto_vinculo'  => 'required|exists:t26_proyectos,id_proyecto,edo_reg,true,co_estatus,1',
      'obj_historico_vinculo' => "required",
      'obj_nacional_vinculo' => "required",
      'ob_estrategico_vinculo' => "required",
      'obj_general_vinculo' => "required",
      'area_estrategica_vinculo' => "required",
      'ambito_estado_vinculo' => "required",
      'objetivo_estado_vinculo' => "required",
      'macroproblema_vinculo' => "required",
      'nodo_vinculo' => "required"
    );

}
