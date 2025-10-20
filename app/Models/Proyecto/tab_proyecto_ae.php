<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class tab_proyecto_ae extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't39_proyecto_acc_espec';

    //public $incrementing = false;

    //protected $primaryKey = ['co_proyecto_acc_espec', 'id_accion'];
    protected $primaryKey = 'co_proyecto_acc_espec';

    /**
     * The name of the "created at" column.
     */
    public const CREATED_AT = 'fecha_creacion';

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = 'fecha_actualizacion';
}
