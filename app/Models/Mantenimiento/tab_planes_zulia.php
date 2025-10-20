<?php

namespace matriz\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Model;

class tab_planes_zulia extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'mantenimiento.tab_planes_zulia';

    public function nullIfBlank($field)
    {
        return trim($field) !== '' ? $field : null;
    }

    public function setCoAmbitoZuliaAttribute($target)
    {
        $this->attributes['co_ambito_zulia'] = $this->nullIfBlank($target);
    }

    public function setCoObjetivoZuliaAttribute($target)
    {
        $this->attributes['co_objetivo_zulia'] = $this->nullIfBlank($target);
    }

    public function setCoNodoAttribute($target)
    {
        $this->attributes['co_nodo'] = $this->nullIfBlank($target);
    }

    public function setCoMacroproblemaAttribute($target)
    {
        $this->attributes['co_macroproblema'] = $this->nullIfBlank($target);
    }


    public static $validarCrear = array(
        "ambito" => "numeric|min:0|max:15",
        "objetivo" => "numeric|min:0|max:10",
        "macroproblema" => "numeric|min:0|max:10",
        "nodo" => "numeric|min:0|max:10",
        "nivel" => "required|numeric|min:0|max:10",
        "descripcion" => "required|min:1|max:1200"
    );

    public static $validarEditar = array(
        "ambito" => "numeric|min:0|max:15",
        "objetivo" => "numeric|min:0|max:10",
        "macroproblema" => "numeric|min:0|max:10",
        "nodo" => "numeric|min:0|max:10",
        "nivel" => "required|numeric|min:0|max:10",
        "descripcion" => "required|min:1|max:1200"
    );
}
