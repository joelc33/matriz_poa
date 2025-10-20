<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class t49_ac_planes extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't49_ac_planes';

    /**
     * The name of the "created at" column.
     */
    public const CREATED_AT = 'fecha_creacion';

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = 'fecha_actualizacion';

    public $incrementing = false;
}
