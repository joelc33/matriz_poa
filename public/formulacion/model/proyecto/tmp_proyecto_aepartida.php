<?php
//*****Modelo en Base al ORM eloquent********//
class tmp_proyecto_aepartida extends Illuminate\Database\Eloquent\Model {

	protected $table = 't43_acc_espec_partida_tmp';
	protected $primaryKey = 'co_partida_tmp';
	public $incrementing = true;

	/**
	 * The name of the "created at" column.
	 */
	const CREATED_AT = 'fecha_creacion';

	/**
	 * The name of the "updated at" column.
	 */
	const UPDATED_AT = 'fecha_actualizacion';

	public static $importar_archivo = array(
		'proyecto'  => 'required|exists:principal.public.t26_proyectos,id_proyecto,edo_reg,true,co_estatus,1',
		"archivo"  => "required",
		"extension" => "required|in:xls,xlsx"
	);

	public static $importar_archivo_foraneo = array(
		'proyecto'  => 'required|exists:principal.public.t26_proyectos,id_proyecto,edo_reg,true',
		"archivo"  => "required",
		"extension" => "required|in:xls,xlsx"
	);

	public static $validar_campo = array(
		"monto" => "numeric|min:0"
	);
}
?>
