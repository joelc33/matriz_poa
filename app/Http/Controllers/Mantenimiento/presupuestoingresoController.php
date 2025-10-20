<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_presupuesto_ingreso;
use View;
use Validator;
use Input;
use Response;
use DB;
use Session;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class presupuestoingresoController extends Controller
{
    protected $tab_presupuesto_ingreso;

    public function __construct(tab_presupuesto_ingreso $tab_presupuesto_ingreso)
    {
        $this->middleware('auth');
        $this->tab_presupuesto_ingreso = $tab_presupuesto_ingreso;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.presupuestoingreso.lista');
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

            $tab_presupuesto_ingreso = $this->tab_presupuesto_ingreso
            ->select(
                'id',
                'id_tab_ejercicio_fiscal',
                'nu_partida',
                'de_partida',
                'mo_partida',
                'in_activo'
            )
            ->where('in_activo', '=', true)
            ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'));

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_presupuesto_ingreso->where('nu_partida', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_presupuesto_ingreso->count();
                $tab_presupuesto_ingreso->skip($start)->take($limit);
                $response['data']  = $tab_presupuesto_ingreso->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_presupuesto_ingreso->count();
                $tab_presupuesto_ingreso->skip($start)->take($limit);
                $response['data']  = $tab_presupuesto_ingreso->orderby('id', 'ASC')->get()->toArray();
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
        return View::make('mantenimiento.presupuestoingreso.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_presupuesto_ingreso::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'nu_partida',
            'de_partida',
            'mo_partida',
            'in_activo'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.presupuestoingreso.editar')->with('data', $data);
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
                    $validator= Validator::make(Input::all(), tab_presupuesto_ingreso::$validarEditar);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = tab_presupuesto_ingreso::find($id);
                    $tabla->nu_partida = Input::get("partida");
                    $tabla->de_partida = Input::get("denominacion");
                    $tabla->mo_partida = Input::get("monto");
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
                    $validator = Validator::make(Input::all(), tab_presupuesto_ingreso::$validarCrear);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = new tab_presupuesto_ingreso();
                    $tabla->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                    $tabla->nu_partida = Input::get("partida");
                    $tabla->de_partida = Input::get("denominacion");
                    $tabla->mo_partida = Input::get("monto");
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
                $tabla = tab_presupuesto_ingreso::find(Input::get("id"));
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
                $tabla = tab_presupuesto_ingreso::find(Input::get("id"));
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
