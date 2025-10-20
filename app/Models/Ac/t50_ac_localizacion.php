<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class t50_ac_localizacion extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't50_ac_localizacion';

    public $timestamps = false;

    public $incrementing = false;
}
