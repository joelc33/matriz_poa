<?php
//*****Modelo en Base al ORM eloquent********//
class tab_usuarios extends Illuminate\Database\Eloquent\Model {
	protected $table = 'autenticacion.tab_usuarios';
	public $incrementing = true;

	public static $validar = array(
		"usuario"  => "required|exists:principal.autenticacion.tab_usuarios,da_login",
		"contrasena"  => "required|exists:principal.autenticacion.tab_usuarios,da_password",
		"codigoseg" => "required|alpha_dash|min:4|max:4|confirmed",
		"codigoseg_confirmation" => "required|alpha_dash|min:4|max:4"
	);

	public static $validarCaptcha = array(
		"codigoseg" => "required|alpha_dash|min:4|max:4|confirmed",
		"codigoseg_confirmation" => "required|alpha_dash|min:4|max:4"
	);
}
?>
