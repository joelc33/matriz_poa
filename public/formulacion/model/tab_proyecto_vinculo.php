<?php
//*****Modelo en Base al ORM eloquent********//
class tab_proyecto_vinculo extends Illuminate\Database\Eloquent\Model {
	protected $table = 't32_proyecto_vinculos';
	protected $primaryKey = 'co_proyecto_vinculos';
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
		'proyecto_vinculo'  => 'required|exists:principal.public.t26_proyectos,id_proyecto,edo_reg,true,co_estatus,1',
		'obj_historico_vinculo' => "required",
		'obj_nacional_vinculo' => "required",
		'ob_estrategico_vinculo' => "required",
		'obj_general_vinculo' => "required",
		'area_estrategica_vinculo' => "required",
		'ambito_estado_vinculo' => "required",
		'objetivo_estado_vinculo' => "required",
		'macroproblema_vinculo' => "required",
		'nodo_vinculo' => "required"
	);
}
?>
