<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class tab_meta_financiera extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't68_metas_detalle';

    //public $incrementing = false;

    //protected $primaryKey = ['co_partida_acc_espec', 'id_accion'];
    protected $primaryKey = 'co_metas_detalle';
}
