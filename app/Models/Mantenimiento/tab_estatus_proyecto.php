<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_estatus_proyecto extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_estatus_proyecto';
}
