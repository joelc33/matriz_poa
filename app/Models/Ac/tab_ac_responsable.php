<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class tab_ac_responsable extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't48_ac_responsables';

    protected $primaryKey = 'id_accion_centralizada';

    public $incrementing = false;

    /**
     * The name of the "created at" column.
     */
    public const CREATED_AT = 'fecha_creacion';

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = 'fecha_actualizacion';
}
