<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class tmp_proyecto_ae_partida extends Model
{
    protected $table = 't43_acc_espec_partida_tmp';
    protected $primaryKey = 'co_partida_tmp';
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
