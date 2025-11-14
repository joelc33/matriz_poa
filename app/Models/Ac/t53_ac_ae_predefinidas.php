<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class t53_ac_ae_predefinidas extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't53_ac_ae_predefinidas';

    public $timestamps = false;

    public $incrementing = false;
}
