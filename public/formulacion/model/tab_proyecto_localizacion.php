<?php
//*****Modelo en Base al ORM eloquent********//
class tab_proyecto_localizacion extends Illuminate\Database\Eloquent\Model {
	protected $table = 't33_proyecto_localizacion';
	protected $primaryKey = 'co_proyecto_localizacion';
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
		'proyecto_localizacion'  => 'required|exists:principal.public.t26_proyectos,id_proyecto,edo_reg,true,co_estatus,1',
		'ambito_localizacion' => "required|integer"
	);
}
?>
