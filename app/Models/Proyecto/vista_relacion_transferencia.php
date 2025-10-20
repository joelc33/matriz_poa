<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class vista_relacion_transferencia extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'vista_relacion_transferencia';
}
