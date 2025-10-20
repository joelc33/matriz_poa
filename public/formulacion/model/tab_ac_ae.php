<?php
//*****Modelo en Base al ORM eloquent********//
class tab_ac_ae extends Illuminate\Database\Eloquent\Model {
	protected $table = 't47_ac_accion_especifica';
	protected $primaryKey = 'id_accion_centralizada';
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

	public static $cerrarAe = array(
		"id"  => "required|numeric|exists:principal.public.t47_ac_accion_especifica,id_accion_centralizada",
		"valido" => "integer|in:1"
	);
}
?>
