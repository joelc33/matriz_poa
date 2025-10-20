<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class tab_meta_fisica extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't69_metas_ac';

    protected $primaryKey = 'co_metas';

    public $incrementing = true;

    /**
     * The name of the "created at" column.
     */
    public const CREATED_AT = 'fecha_creacion';

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = 'fecha_actualizacion';
}
