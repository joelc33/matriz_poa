<?php

namespace matriz\Models\Autenticacion;

//*******agregar esta linea******//
use DB;
//*******************************//
use Illuminate\Database\Eloquent\Model;

class tab_menu extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'autenticacion.tab_menu';

    public static function sp_catidad_menu_hijo($id_padre, $id_tab_rol)
    {
        return DB::select('SELECT autenticacion.sp_catidad_menu_hijo(?, ?)', array($id_padre, $id_tab_rol));
    }

    public static function sp_catidad_menu_privilegio($id_padre, $id_tab_rol)
    {
        return DB::select('SELECT autenticacion.sp_catidad_menu_privilegio(?, ?)', array($id_padre, $id_tab_rol));
    }
}
