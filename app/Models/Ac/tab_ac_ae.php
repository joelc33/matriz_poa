<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class tab_ac_ae extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't47_ac_accion_especifica';

    //public $incrementing = false;

    //protected $primaryKey = ['id_accion_centralizada', 'id_accion'];
    protected $primaryKey = 'id_accion_centralizada';

    /**
     * The name of the "created at" column.
     */
    public const CREATED_AT = 'fecha_creacion';

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = ['id_accion_centralizada', 'id_accion', 'id_ejecutor', 'id_tipo_fondo'];
}
