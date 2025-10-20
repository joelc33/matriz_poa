<?php

namespace matriz\Models\ProySegto;

use Illuminate\Database\Eloquent\Model;

class tab_proyecto_ae extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'proyecto_seguimiento.tab_proyecto_ae';
}
