<?php

namespace matriz\Models\Autenticacion;

use Illuminate\Database\Eloquent\Model;

class tab_usuario_rol extends Model
{
    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'autenticacion.tab_usuario_rol';

    protected $primaryKey = 'id_tab_usuarios';
}
