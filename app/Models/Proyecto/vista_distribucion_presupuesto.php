<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class vista_distribucion_presupuesto extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'vista_distribucion_presupuesto';
}
