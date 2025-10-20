<?php
//*****Modelo en Base al ORM eloquent********//
class vista_pr_partida_meta extends Illuminate\Database\Eloquent\Model {
	protected $table = 'vista_pr_partida_meta';
	protected $primaryKey = 'co_proyecto_acc_espec';
	public $timestamps = false;
	public $incrementing = false;

	public static $cerrarAe = array(
		"base"  => "required|confirmed|numeric",
		"base_confirmation" => "required|numeric"
	);
}
?>
