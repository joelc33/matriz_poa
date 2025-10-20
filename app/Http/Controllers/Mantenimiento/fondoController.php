<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_tipo_fondo;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class fondoController extends Controller
{
    protected $tab_tipo_fondo;

    public function __construct(tab_tipo_fondo $tab_tipo_fondo)
    {
        $this->middleware('auth');
        $this->tab_tipo_fondo = $tab_tipo_fondo;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.fondo.lista');
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

            $tab_tipo_fondo = $this->tab_tipo_fondo
            ->join('mantenimiento.tab_tipo_recurso as t01', 't01.id', '=', 'mantenimiento.tab_tipo_fondo.id_tab_tipo_recurso')
            ->select('mantenimiento.tab_tipo_fondo.id', 'id_tab_tipo_recurso', 'de_tipo_fondo', 'mantenimiento.tab_tipo_fondo.in_activo', 'de_tipo_recurso');

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_tipo_fondo->where('de_tipo_fondo', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_tipo_fondo->count();
                $tab_tipo_fondo->skip($start)->take($limit);
                $response['data']  = $tab_tipo_fondo->orderby('mantenimiento.tab_tipo_fondo.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_tipo_fondo->count();
                $tab_tipo_fondo->skip($start)->take($limit);
                $response['data']  = $tab_tipo_fondo->orderby('mantenimiento.tab_tipo_fondo.id', 'ASC')->get()->toArray();
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
    public function nuevo()
    {
        $data = json_encode(array("id" => ""));
        return View::make('mantenimiento.fondo.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_tipo_fondo::select('id', 'id_tab_tipo_recurso', 'de_tipo_fondo', 'in_activo')
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.fondo.editar')->with('data', $data);
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
                    $validator= Validator::make(Input::all(), tab_tipo_fondo::$validarEditar);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = tab_tipo_fondo::find($id);
                    $tabla->id_tab_tipo_recurso = Input::get("recurso");
                    $tabla->de_tipo_fondo = Input::get("fondo");
                    $tabla->save();

                    DB::commit();
                    return Response::json(array(
                      'success' => true,
                      'msg' => 'Registro Editado con Exito!'
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
                    $validator = Validator::make(Input::all(), tab_tipo_fondo::$validarCrear);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = new tab_tipo_fondo();
                    $tabla->id_tab_tipo_recurso = Input::get("recurso");
                    $tabla->de_tipo_fondo = Input::get("fondo");
                    $tabla->in_activo = 'TRUE';
                    $tabla->save();

                    DB::commit();
                    return Response::json(array(
                      'success' => true,
                      'msg' => 'Registro Guardado con Exito!'
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
         * Show the form for creating a new resource.
         *
         * @return Response
         */
        public function eliminar()
        {
            DB::beginTransaction();
            try {
                $tabla = tab_tipo_fondo::find(Input::get("id"));
                $tabla->in_activo = 'FALSE';
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

        /**
         * Show the form for creating a new resource.
         *
         * @return Response
         */
        public function habilitar()
        {
            DB::beginTransaction();
            try {
                $tabla = tab_tipo_fondo::find(Input::get("id"));
                $tabla->in_activo = 'TRUE';
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

}
