<?php
//*****Modelo en Base al ORM eloquent********//
class vista_pr_partida_base extends Illuminate\Database\Eloquent\Model {
	protected $table = 'vista_pr_partida_base';
	protected $primaryKey = 'co_proyecto_acc_espec';
	public $timestamps = false;
	public $incrementing = false;
}
?>
