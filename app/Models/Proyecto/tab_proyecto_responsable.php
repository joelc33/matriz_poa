<?php

namespace matriz\Models\Proyecto;

use Illuminate\Database\Eloquent\Model;

class tab_proyecto_responsable extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't37_proyecto_responsables';

    //public $incrementing = false;

    //protected $primaryKey = ['co_partida_acc_espec', 'id_accion'];
    protected $primaryKey = 'co_proyecto_responsables';

    /**
     * The name of the "created at" column.
     */
    public const CREATED_AT = 'fecha_creacion';

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = 'fecha_actualizacion';

    public static $cerrarProyecto = array(
        'proyecto_responsable'  => 'required|exists:t26_proyectos,id_proyecto,edo_reg,true,co_estatus,1',
        'responsable_nombres' => "required",
        'reponsable_cedula' => "required",
        'responsable_correo' => "required|email",
        'responsable_telefono' => "required",
        'tecnico_nombres' => "required",
        'tecnico_cedula' => "required",
        'tecnico_correo' => "required|email",
        'tecnico_telefono' => "required",
        'tecnico_unidad' => "required",
        'registrador_nombres' => "required",
        'registrador_cedula' => "required",
        'registrador_correo' => "required|email",
        'registrador_telefono' => "required",
        'administrador_nombres' => "required",
        'administrador_cedula' => "required",
        'administrador_correo' => "required|email",
        'administrador_telefono' => "required",
        'administrador_unidad' => "required"
    );
}
