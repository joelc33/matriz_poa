<?php
//*****Modelo en Base al ORM eloquent********//
class tab_proyecto extends Illuminate\Database\Eloquent\Model {
	protected $table = 't26_proyectos';
	protected $primaryKey = 'co_proyectos';
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

	public static $ordenarProyecto = array(
		"ejecutor"  => 'required|exists:principal.public.t26_proyectos,id_ejecutor,edo_reg,true,co_estatus,3',
		"valido" => "integer|in:1"
	);

	public static $validarCrear = array(
		'ejercicio_proyecto' => "required|numeric|min:2015|max:2100",
		'ejecutor_proyecto' => "required",
		'nombre_proyecto' => "required",
		'status_proyecto' => "required|integer|in:1,2,3",
		'fecha_ini_proyecto' => "required|date_format:d/m/Y|before:fecha_fin_proyecto",
		'fecha_fin_proyecto' => "required|date_format:d/m/Y|after:fecha_ini_proyecto",
		'objetivo_proyecto' => "required",
		'descripcion_proyecto' => "required",
		'sit_presupuesto_proyecto' => "required|integer",
		'monto_proyecto' => "required|numeric|min:0",
		'clase_sector_proyecto' => "required",
		'clase_subsector_proyecto' => "required",
		'plan_operativo_proyecto' => "required|integer",
		'id_tab_ejecutor' => "required|numeric"
	);

	public static $cerrarProyecto = array(
		'proyecto_proyecto'  => 'required|exists:principal.public.t26_proyectos,id_proyecto,edo_reg,true,co_estatus,1',
		'ejercicio_proyecto' => "required|numeric|min:2015|max:2100",
		'ejecutor_proyecto' => "required",
		'nombre_proyecto' => "required",
		'status_proyecto' => "required|integer|in:1,2,3",
		'fecha_ini_proyecto' => "required|date_format:Y-m-d|before:fecha_fin_proyecto",
		'fecha_fin_proyecto' => "required|date_format:Y-m-d|after:fecha_ini_proyecto",
		'objetivo_proyecto' => "required",
		'descripcion_proyecto' => "required",
		'sit_presupuesto_proyecto' => "required|integer",
		'monto_proyecto' => "required|numeric|min:0",
		'clase_sector_proyecto' => "required",
		'clase_subsector_proyecto' => "required",
		'plan_operativo_proyecto' => "required|integer",
		'co_estatus_proyecto' => "required|integer|in:1",
		'ae_cuadra' => "integer|in:1"
	);
}
?>
