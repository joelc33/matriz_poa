<?php

namespace matriz\Http\Controllers\Autenticacion;

//*******agregar esta linea******//
use matriz\Models\Autenticacion\tab_rol;
use matriz\Models\Autenticacion\tab_menu;
use matriz\Models\Autenticacion\tab_rol_menu;
use matriz\Models\Autenticacion\tab_privilegio_menu;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class rolController extends Controller
{
    /**
       * Create a new controller instance.
       *
       * @return void
       */

    protected $tab_rol;
    protected $tab_privilegio_menu;

    public function __construct(tab_rol $tab_rol, tab_privilegio_menu $tab_privilegio_menu)
    {
        $this->middleware('auth');
        $this->tab_rol = $tab_rol;
        $this->tab_privilegio_menu = $tab_privilegio_menu;
    }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function lista()
  {
      return View::make('autenticar.rol.lista');
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function storeLista()
  {
      try {
          $start  = Input::get('start', 0);
          $limit  = Input::get('limit', 20);
          $variable = Input::get('variable');

          $tab_rol = $this->tab_rol
          ->select('id', 'de_rol');

          if (Input::get("BuscarBy")=="true") {

              if($variable!="") {
                  $tab_rol->where('de_rol', 'ILIKE', "%$variable%");
              }

              $response['success']  = 'true';
              $response['total'] = $tab_rol->count();
              $tab_rol->skip($start)->take($limit);
              $response['data']  = $tab_rol->orderby('id', 'ASC')->get()->toArray();
          } else {
              $response['success']  = 'true';
              $response['total'] = $tab_rol->count();
              $tab_rol->skip($start)->take($limit);
              $response['data']  = $tab_rol->orderby('id', 'ASC')->get()->toArray();
          }

          return Response::json($response, 200);
      } catch (\Illuminate\Database\QueryException $e) {
          return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 200);
      }
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function nuevo()
  {
      $data = json_encode(array("id" => ""));

      return View::make('autenticar.rol.editar')->with('data', $data);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function guardar($id = null)
  {
      DB::beginTransaction();
      if($id!=''||$id!=null) {

          try {
              $validator = Validator::make(Input::all(), tab_rol::$validarEditar);
              if ($validator->fails()) {
                  return Response::json(array(
                    'success' => false,
                    'msg' => $validator->getMessageBag()->toArray()
                  ));
              }

              $rol = tab_rol::find($id);
              $rol->de_rol = Input::get("nombre");
              $rol->save();

              DB::commit();
              return Response::json(array(
                'success' => true,
                'msg' => 'Rol Editado con Exito!'
              ));

          } catch (\Illuminate\Database\QueryException $e) {
              DB::rollback();
              return Response::json(array(
                'success' => false,
                'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
              ));
          }

      } else {

          try {
              $validator = Validator::make(Input::all(), tab_rol::$validarCrear);
              if ($validator->fails()) {
                  return Response::json(array(
                    'success' => false,
                    'msg' => $validator->getMessageBag()->toArray()
                  ));
              }

              $rol = new tab_rol();
              $rol->de_rol = Input::get("nombre");
              $rol->in_estatus = "true";
              $rol->save();

              DB::commit();
              return Response::json(array(
                'success' => true,
                'msg' => 'Rol Guardado con Exito!'
              ));

          } catch (\Illuminate\Database\QueryException $e) {
              DB::rollback();
              return Response::json(array(
                'success' => false,
                'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
              ));
          }
      }
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function privilegio(Request $request)
  {
      $rol = tab_rol::select('id as co_rol', 'de_rol as tx_rol')
      ->where('id', '=', $request->id)
      ->first();

      $co_rol=$request->id;

      $menu = tab_menu::select('id_tab_menu', 'de_menu')
        ->join('autenticacion.tab_rol_menu as t04', 'autenticacion.tab_menu.id', '=', 't04.id_tab_menu')
        ->where('id_padre', '=', 0)
        ->where('t04.id_tab_rol', '=', $co_rol)
        ->orderBy('nu_orden', 'ASC')
        ->get();

      $arbol = '';
      foreach($menu as $item) {
          $cantidad = tab_menu::sp_catidad_menu_privilegio($item->id_tab_menu, $co_rol);
          if($cantidad[0]->sp_catidad_menu_privilegio > 0) {
              $arbol.= "{
                         text:'".$item->de_menu."',
             cls:'forum-ct',
             iconCls:'forum-parent',
                        // expanded:true,
                         children:[".self::ArmaSubmenu($item->id_tab_menu, $co_rol)."]
                        },";
          }
      }
      return View::make('autenticar.rol.privilegio')
          ->with('data', $rol)
          ->with('menu', $arbol);
  }

  public static function ArmaSubmenu($co_padre, $co_rol)
  {

      $menu = tab_menu::select('t04.id', 'id_tab_menu', 'de_menu', 'de_icono', 't04.in_estatus')
      ->join('autenticacion.tab_rol_menu as t04', 'autenticacion.tab_menu.id', '=', 't04.id_tab_menu')
      ->where('id_padre', '=', $co_padre)
      ->where('t04.id_tab_rol', '=', $co_rol)
      ->orderBy('nu_orden', 'ASC')->get();

      $submenu = '';
      foreach($menu as $items) {
          $cantidad = tab_menu::sp_catidad_menu_privilegio($items->id_tab_menu, $co_rol);
          if($cantidad[0]->sp_catidad_menu_privilegio > 0) {
              if($items->in_estatus=='t') {
                  $in_estatus='true';
              } else {
                  $in_estatus='false';
              }
              $submenu.= "{
                       text:'".$items->de_menu."',
                       id:'".$items->id."',
                 checked : ".$in_estatus.",
                       children:[".self::ArmaSubmenu($items->id_tab_menu, $co_rol)."]
                       },";
          } else {
              if($items->in_estatus=='t') {
                  $in_estatus='true';
              } else {
                  $in_estatus='false';
              }
              $submenu.= "{
                            text:'".$items->de_menu."',
                            id:'".$items->id."',
                            iconCls:'".$items->de_icono."',
                qtip : 'Seleccione Opcion',
                            leaf:true,
                checked: ".$in_estatus." },";
          }
      }
      return  $submenu;
  }

  /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function guardarPrivilegio(Request $request)
    {
        //Actualizar todos los registros a false en tab_rol_menu
        $tab_rol_menu = tab_rol_menu::where('id_tab_rol', '=', $request->co_rol)->update(array('in_estatus' => false));
        //Actualizar las opciones seleccionadas en tab_rol_menu
        $menu = json_decode($request->seleccion, true);
        foreach ($menu as $lista) {
            $rol_menu = tab_rol_menu::find($lista['id_menu']);
            $rol_menu->in_estatus = 'TRUE';
            $rol_menu->save();
        }
        return Response::json(array(
            'success' => true,
            'msg' => 'Accesos editados con Exito!'
        ));
    }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function opcion()
  {
      $data = array("rol" => Input::get("codigo"));

      return View::make('autenticar.rol.opcion')->with('data', $data);
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function opcionStoreLista()
  {
      try {
          $start  = Input::get('start', 0);
          $limit  = Input::get('limit', 40);
          $variable = Input::get('variable');

          $tab_privilegio_menu = tab_privilegio_menu::join('autenticacion.tab_privilegio as t01', 't01.id', '=', 'autenticacion.tab_privilegio_menu.id_tab_privilegio')
          ->join('autenticacion.tab_menu as t02', 't02.id', '=', 't01.id_tab_menu')
          ->join('autenticacion.tab_rol_menu as t03', 't03.id', '=', 'autenticacion.tab_privilegio_menu.id_tab_rol_menu')
          ->select(
              'autenticacion.tab_privilegio_menu.id',
              'de_menu',
              'de_privilegio',
              'nu_orden',
              DB::raw("autenticacion.tab_privilegio_menu.in_estatus as in_habilitado")
          )
          ->where('id_tab_rol', '=', Input::get('rol'));

          if (Input::get("BuscarBy")=="true") {

              if($variable!="") {
                  $tab_privilegio_menu->where('de_privilegio', 'ILIKE', "%$variable%");
              }

              $response['success']  = 'true';
              $response['total'] = $tab_privilegio_menu->count();
              $tab_privilegio_menu->skip($start)->take($limit);
              $response['data']  = $tab_privilegio_menu->orderby('nu_orden', 'ASC')->orderBy('de_privilegio', 'ASC')->get()->toArray();
          } else {
              $response['success']  = 'true';
              $response['total'] = $tab_privilegio_menu->count();
              $tab_privilegio_menu->skip($start)->take($limit);
              $response['data']  = $tab_privilegio_menu->orderby('nu_orden', 'ASC')->orderBy('de_privilegio', 'ASC')->get()->toArray();
          }

          return Response::json($response, 200);
      } catch (\Illuminate\Database\QueryException $e) {
          return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 200);
      }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function opcionSi()
  {
      DB::beginTransaction();
      try {
          $tabla = tab_privilegio_menu::find(Input::get("id"));
          $tabla->in_estatus = 'TRUE';
          $tabla->save();
          DB::commit();

          $response['success']  = 'true';
          $response['msg']  = 'Registro Habilitado con Exito!';
          return Response::json($response, 200);

      } catch (\Illuminate\Database\QueryException $e) {
          DB::rollback();

          $response['success']  = 'false';
          $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
          return Response::json($response, 200);
      }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function opcionNo()
  {
      DB::beginTransaction();
      try {
          $tabla = tab_privilegio_menu::find(Input::get("id"));
          $tabla->in_estatus = 'FALSE';
          $tabla->save();
          DB::commit();

          $response['success']  = 'true';
          $response['msg']  = 'Registro Deshabilitado con Exito!';
          return Response::json($response, 200);

      } catch (\Illuminate\Database\QueryException $e) {
          DB::rollback();

          $response['success']  = 'false';
          $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
          return Response::json($response, 200);
      }
  }

}
