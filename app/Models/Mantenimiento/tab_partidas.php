<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_partidas extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_partidas';

    public static $validarBusqueda = array(
        'partida' => 'required|numeric|exists:tab_partidas,co_partida,id_tab_ejercicio_fiscal,2018'
    );

    public static function validarBusqueda($id=0, $merge=[])
    {
        return array_merge([
            'partida' => 'required|numeric|exists:tab_partidas,co_partida,id_tab_ejercicio_fiscal,'.$id
            ], $merge);
    }

    public static $validarCrear = array(
        "ejercicio_fiscal" => "numeric|min:2015|max:3000",
        //"partida" => "numeric|min:0|max:999999999999",
        "partida" => "required|min:1|max:12",
        //"ace_mov" => "boolean",
        "nombre" => "required|min:1|max:1200"
    );

    public static $validarEditar = array(
        "ejercicio_fiscal" => "numeric|min:2015|max:3000",
        //"partida" => "numeric|min:0|max:999999999999",
        "partida" => "required|min:1|max:12",
        //"ace_mov" => "boolean",
        "nombre" => "required|min:1|max:1200"
    );

}
