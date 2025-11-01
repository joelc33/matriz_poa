<?php

namespace matriz\Models\Ac;

use Illuminate\Database\Eloquent\Model;

class tab_ac extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 't46_acciones_centralizadas';

    protected $primaryKey = 'id';
    //public $timestamps = false;
    public $incrementing = true;

    /**
     * The name of the "created at" column.
     */
    public const CREATED_AT = 'fecha_creacion';

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = 'fecha_actualizacion';

    public static $validarCrear = array(
        "id_accion" => "required|integer|composite_unique:t46_acciones_centralizadas,id_ejercicio,id_ejecutor,id_accion",
//        "descripcion" => "required|min:1|max:1200",
        "id_ejecutor" => "required|min:1|max:4",
        "id_ejercicio" => "required|numeric",
        "inst_mision" => "required|min:1|max:3000",
        "inst_vision" => "required|min:1|max:3000",
        "inst_objetivos" => "required|min:1|max:6000",
        "id_co_sector" => "required",
//        "id_subsector" => "required",
        "fecha_inicio" => "required|date_format:d-m-Y|before:fecha_fin",
        "fecha_fin" => "required|date_format:d-m-Y|after:fecha_inicio",
        "co_situacion_presupuestaria" => "required|integer",
        "monto" => "required|numeric|min:0",
        "nu_po_beneficiar" => "required|numeric|min:0",
        "nu_em_previsto" => "required|numeric|min:0",
//        "tx_pr_objetivo" => "required|min:1|max:1200",
//        "tx_re_esperado" => "required|min:1|max:1200"
    );

    public static $validarEditar = array(
        "id_accion" => "required|integer",
//        "descripcion" => "required|min:1|max:1200",
        "id_ejecutor" => "required|min:1|max:4",
        "id_ejercicio" => "required|numeric",
        "inst_mision" => "required|min:1|max:3000",
        "inst_vision" => "required|min:1|max:3000",
        "inst_objetivos" => "required|min:1|max:6000",
        "id_co_sector" => "required",
//        "id_subsector" => "required",
        "fecha_inicio" => "required|date_format:d-m-Y|before:fecha_fin",
        "fecha_fin" => "required|date_format:d-m-Y|after:fecha_inicio",
        "co_situacion_presupuestaria" => "required|integer",
        "monto" => "required|numeric|min:0",
        "nu_po_beneficiar" => "required|numeric|min:0",
        "nu_em_previsto" => "required|numeric|min:0",
//        "tx_pr_objetivo" => "required|min:1|max:1200",
//        "tx_re_esperado" => "required|min:1|max:1200"
    );

    public static $cerrarAc = array(
        "id"  => "required|numeric|exists:t46_acciones_centralizadas,id",
        "valido" => "integer|in:1"
    );

}
