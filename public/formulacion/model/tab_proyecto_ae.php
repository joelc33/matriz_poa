<?php
//*****Modelo en Base al ORM eloquent********//
class tab_proyecto_ae extends Illuminate\Database\Eloquent\Model {
	protected $table = 't39_proyecto_acc_espec';
	protected $primaryKey = 'co_proyecto_acc_espec';
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

	public static $validarCrear = array(
		"usuario" => "required|min:5|max:50|unique:el_users,el_username",
		"contraseña"  => "required|min:6|max:30|confirmed",
		"contraseña_confirmation" => "required|min:6|max:30",
		"email"  => "required|email|confirmed|unique:el_users,el_email",
		"email_confirmation" => "required|email",
		"captcha"  => "required"
	);

	public static $validarEditar = array(
		"email"  => "required|email|confirmed|unique:el_users,el_username",
		"email_confirmation" => "required|email"
	);

	public static $cerrarAe = array(
		"id"  => "required|numeric|exists:principal.public.t39_proyecto_acc_espec,co_proyecto_acc_espec",
		"valido" => "integer|in:1"
	);

	public static $ordenarAe = array(
		"proyecto"  => "required|alpha_num|exists:principal.public.t39_proyecto_acc_espec,id_proyecto"
	);
}
?>
