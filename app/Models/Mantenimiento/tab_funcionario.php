<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_funcionario extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_funcionario';

    public static $validarCrear = array(
        "documenton"    => "required|integer|in:1,2,3",
        "cedula" => "required|integer|min:1000000|max:99999999",
        "nombre" => "required|min:2|max:50",
        "apellido" => "required|min:2|max:50",
        "cargo"    => "required|integer",
        "ejecutor"    => "required|integer",
        "correo_funcionario"    => "required|email",
        "telefono_funcionario"    => "required|regex:/^([0-9]{4})([-]{1})([0-9]{7}$)/"
    );

    public static $validarEditarFuncionario = array(
        "documenton"    => "required|integer|in:1,2,3",
        "cedula" => "required|integer|min:1000000|max:99999999",
        "nombre" => "required|min:2|max:50",
        "apellido" => "required|min:2|max:50",
        "cargo"    => "required|integer",
        "ejecutor"    => "required|integer",
        "correo_funcionario"    => "required|email",
        "telefono_funcionario"    => "required|regex:/^([0-9]{4})([-]{1})([0-9]{7}$)/"
    );

    public static $validarEditar = array(
        "documenton"    => "required|integer|in:1,2,3",
        "cedula" => "required|integer|min:1000000|max:99999999",
        "nombre" => "required|min:2|max:50",
        "apellido" => "required|min:2|max:50",
        "cargo"    => "required|integer",
        "correo_funcionario"    => "required|email",
        "telefono_funcionario"    => "required|regex:/^([0-9]{4})([-]{1})([0-9]{7}$)/",
        "correo"    => "required|email",
        "telefono"    => "required"
    );

}
