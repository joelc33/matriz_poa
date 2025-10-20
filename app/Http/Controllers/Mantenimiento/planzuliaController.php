<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_planes_zulia;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class planzuliaController extends Controller
{
    protected $tab_planes_zulia;

    public function __construct(tab_planes_zulia $tab_planes_zulia)
    {
        $this->middleware('auth');
        $this->tab_planes_zulia = $tab_planes_zulia;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.plan.lista');
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

            $tab_planes_zulia = $this->tab_planes_zulia
            ->select(
                'id',
                'co_ambito_zulia',
                'co_objetivo_zulia',
                'co_macroproblema',
                'co_nodo',
                'nu_nivel',
                'tx_descripcion',
                'co_area_estrategica',
                'in_activo'
            );

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_planes_zulia->where('tx_descripcion', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_planes_zulia->count();
                $tab_planes_zulia->skip($start)->take($limit);
                $response['data']  = $tab_planes_zulia->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_planes_zulia->count();
                $tab_planes_zulia->skip($start)->take($limit);
                $response['data']  = $tab_planes_zulia->orderby('id', 'ASC')->get()->toArray();
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
        return View::make('mantenimiento.plan.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_planes_zulia::select(
            'id',
            'co_ambito_zulia',
            'co_objetivo_zulia',
            'co_macroproblema',
            'co_nodo',
            'nu_nivel',
            'tx_descripcion',
            'co_area_estrategica',
            'in_activo'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.plan.editar')->with('data', $data);
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
                    $validator= Validator::make(Input::all(), tab_planes_zulia::$validarEditar);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = tab_planes_zulia::find($id);
                    $tabla->co_ambito_zulia = Input::get("ambito");
                    $tabla->co_objetivo_zulia = Input::get("objetivo");
                    $tabla->co_macroproblema = Input::get("macroproblema");
                    $tabla->co_nodo = Input::get("nodo");
                    $tabla->nu_nivel = Input::get("nivel");
                    $tabla->tx_descripcion = Input::get("descripcion");
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
                    $validator = Validator::make(Input::all(), tab_planes_zulia::$validarCrear);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = new tab_planes_zulia();
                    $tabla->co_ambito_zulia = Input::get("ambito");
                    $tabla->co_objetivo_zulia = Input::get("objetivo");
                    $tabla->co_macroproblema = Input::get("macroproblema");
                    $tabla->co_nodo = Input::get("nodo");
                    $tabla->nu_nivel = Input::get("nivel");
                    $tabla->tx_descripcion = Input::get("descripcion");
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
                $tabla = tab_planes_zulia::find(Input::get("id"));
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
                $tabla = tab_planes_zulia::find(Input::get("id"));
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
