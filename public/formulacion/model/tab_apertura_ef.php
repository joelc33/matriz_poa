<?php
//*****Modelo en Base al ORM eloquent********//
class tab_apertura_ef extends Illuminate\Database\Eloquent\Model {

	//Todos los modelos deben extender la clase Eloquent
	protected $table = 'mantenimiento.tab_apertura_ef';

	public static $validarCrear = array(
		"periodo_fiscal" => "required|numeric",
		"fecha_apertura" => "required|date_format:d/m/Y|before:fecha_cierre",
		"fecha_cierre" => "required|date_format:d/m/Y|after:fecha_apertura",
		"descripcion" => "required|min:2|max:300"
	);

	public static $validarEditar = array(
		"fecha_apertura" => "required|date_format:d/m/Y|before:fecha_cierre",
		"fecha_cierre" => "required|date_format:d/m/Y|after:fecha_apertura",
		"descripcion" => "required|min:2|max:300"
	);

}
?>
