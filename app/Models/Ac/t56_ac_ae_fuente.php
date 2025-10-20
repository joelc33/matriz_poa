<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class t56_ac_ae_fuente extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't56_ac_ae_fuente';

    public $timestamps = false;

    public $incrementing = false;
}
