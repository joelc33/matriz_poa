<?php
//*****Modelo en Base al ORM eloquent********//
class tab_proyecto_responsable extends Illuminate\Database\Eloquent\Model {
	protected $table = 't37_proyecto_responsables';
	protected $primaryKey = 'co_proyecto_responsables';
	//public $timestamps = false;
	public $incrementing = true;

	/**
	 * The name of the "created at" column.
	 */
	const CREATED_AT = 'fecha_creacion';
	 
	/**
	 * The name of the "updated at" column.
	 */
	const UPDATED_AT = 'fecha_actualizacion';

	public static $cerrarProyecto = array(
		'proyecto_responsable'  => 'required|exists:principal.public.t26_proyectos,id_proyecto,edo_reg,true,co_estatus,1',
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
?>
