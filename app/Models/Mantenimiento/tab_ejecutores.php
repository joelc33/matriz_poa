<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_ejecutores extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_ejecutores';

    public static $datosEjecutor = array(
        "ejercicio" => "required|numeric|min:2015|max:3000",
        "correo"    => "required|email",
        "telefono" => "required|regex:/^([0-9]{4})([-]{1})([0-9]{7}$)/",
        "documenton"    => "required|integer|in:1,2,3",
        "cedula" => "required|integer|min:1000000|max:99999999",
        "nombre" => "required|min:2|max:50",
        "apellido" => "required|min:2|max:50",
        "correo_funcionario"    => "required|email",
        "telefono_funcionario"    => "required|regex:/^([0-9]{4})([-]{1})([0-9]{7}$)/"
    );

    public static $validarCrear = array(
        "codigo" => "required|min:4|max:4|unique:tab_ejecutores,id_ejecutor",
        "nombre" => "required|min:1|max:800",
        "car_01" => "required|min:1|max:10",
        "car_02" => "required|min:1|max:10",
        "car_03" => "required|min:1|max:10",
        "car_04" => "required|min:1|max:10",
        "tipo" => "required|numeric",
        "ambito" => "required|numeric",
        "codigo_01" => "required|min:1|max:10",
        "codigo_eje" => "required|min:1|max:10",
        "correo" => "required|email",
        "telefono" => "required|regex:/^([0-9]{4})([-]{1})([0-9]{7}$)/"
    );

    public static $validarEditar = array(
        "codigo" => "required|min:4|max:4",
        "nombre" => "required|min:1|max:800",
        "car_01" => "required|min:1|max:10",
        "car_02" => "required|min:1|max:10",
        "car_03" => "required|min:1|max:10",
        "car_04" => "required|min:1|max:10",
        "tipo" => "required|numeric",
        "ambito" => "required|numeric",
        "codigo_01" => "required|min:1|max:10",
        "codigo_eje" => "required|min:1|max:10",
        "correo" => "required|email",
        "telefono" => "required|regex:/^([0-9]{4})([-]{1})([0-9]{7}$)/"
    );

}
