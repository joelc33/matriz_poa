<?php
//*****Modelo en Base al ORM eloquent********//
class tab_login_acceso extends Illuminate\Database\Eloquent\Model {
	protected $table = 'auditoria.tab_login_acceso';
	public $incrementing = true;

	const UPDATED_AT = null;
}
?>
