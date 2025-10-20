<?php

namespace matriz\Models\AcSegto;

use Illuminate\Database\Eloquent\Model;

class tab_forma_001 extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'ac_seguimiento.tab_forma_001';
}
