<?php
//*****Modelo en Base al ORM eloquent********//
class vista_ac_partida_base extends Illuminate\Database\Eloquent\Model {
	protected $table = 'vista_ac_partida_base';
	protected $primaryKey = 'ac';
	public $timestamps = false;
	public $incrementing = false;
}
?>
