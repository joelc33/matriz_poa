<?php
//*****Modelo en Base al ORM eloquent********//
class tab_ac extends Illuminate\Database\Eloquent\Model {
	protected $table = 't46_acciones_centralizadas';
	protected $primaryKey = 'id';
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

	public static $cerrarAc = array(
		"id"  => "required|numeric|exists:principal.public.t46_acciones_centralizadas,id",
		"valido" => "integer|in:1"
	);

	public static $validarCrear = array(
		'ejercicio_ac' => "required|numeric|min:2015|max:2100",
		'accion_ac' => "required|integer|in:1,2,3,4,5,6",
		'descripcion_ac' => "required",
		'ejecutor_ac' => "required",
		'mision_ac' => "required",
		'vision_ac' => "required",
		'objetivo_ac' => "required",
		'sector_ac' => "required",
		'subsector_ac' => "required",
		'fecha_ini_ac' => "required|date_format:d/m/Y|before:fecha_fin_ac",
		'fecha_fin_ac' => "required|date_format:d/m/Y|after:fecha_ini_ac",
		'sit_presupuesto_ac' => "required|numeric",
		'monto_ac' => "required|numeric|min:0",
		'poblacion_ac' => "required|numeric",
		'empleo_ac' => "required|numeric",
		'producto_ac' => "required",
		'resultado_ac' => "required",
		'id_tab_ejecutor' => "required|numeric"
	);

}
?>
