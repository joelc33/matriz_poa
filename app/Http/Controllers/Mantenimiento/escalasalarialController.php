<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_escala_salarial;
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

class escalasalarialController extends Controller
{
    protected $tab_escala_salarial;

    public function __construct(tab_escala_salarial $tab_escala_salarial)
    {
        $this->middleware('auth');
        $this->tab_escala_salarial = $tab_escala_salarial;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.escalasalarial.lista');
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

            $tab_escala_salarial = $this->tab_escala_salarial
            ->join('mantenimiento.tab_tipo_empleado as t01', 't01.id', '=', 'mantenimiento.tab_escala_salarial.id_tab_tipo_empleado')
            ->select(
                'mantenimiento.tab_escala_salarial.id',
                'id_tab_ejercicio_fiscal',
                'id_tab_tipo_empleado',
                'de_grupo',
                'de_escala_salarial',
                'nu_masculino',
                'nu_femenino',
                'mo_escala_salarial',
                'mantenimiento.tab_escala_salarial.in_activo',
                'de_tipo_empleado'
            )
             ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'));

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_escala_salarial->where('de_escala_salarial', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_escala_salarial->count();
                $tab_escala_salarial->skip($start)->take($limit);
                $response['data']  = $tab_escala_salarial->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_escala_salarial->count();
                $tab_escala_salarial->skip($start)->take($limit);
                $response['data']  = $tab_escala_salarial->orderby('id', 'ASC')->get()->toArray();
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
        return View::make('mantenimiento.escalasalarial.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_escala_salarial::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'id_tab_tipo_empleado',
            'de_grupo',
            'de_escala_salarial',
            'nu_masculino',
            'nu_femenino',
            'mo_escala_salarial',
            'in_activo'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.escalasalarial.editar')->with('data', $data);
    }

        /**
         * Update the specified resource in storage.
         *
         * @param  int  $id
         * @return Response
         */
        public function guardar(Request $request, $id = null)
        {
            DB::beginTransaction();
            if($id!=''||$id!=null) {

                try {
                    $validator= Validator::make($request->all(), tab_escala_salarial::$validarEditar);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = tab_escala_salarial::find($id);
                    $tabla->id_tab_tipo_empleado = $request->get("tipo_empleado");
                    $tabla->de_grupo = $request->get("grupo");
                    $tabla->de_escala_salarial = $request->get("escala_salarial");
                    $tabla->nu_masculino = (float)$request->get("masculino");
                    $tabla->nu_femenino = (float)$request->get("femenino");
                    $tabla->mo_escala_salarial = (float)$request->get("sueldo");
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
                    $validator = Validator::make($request->all(), tab_escala_salarial::$validarCrear);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = new tab_escala_salarial();
                    $tabla->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                    $tabla->id_tab_tipo_empleado = $request->get("tipo_empleado");
                    $tabla->de_grupo = $request->get("grupo");
                    $tabla->de_escala_salarial = $request->get("escala_salarial");
                    $tabla->nu_masculino = (float)$request->get("masculino");
                    $tabla->nu_femenino = (float)$request->get("femenino");
                    $tabla->mo_escala_salarial = (float)$request->get("sueldo");
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
                $tabla = tab_escala_salarial::find(Input::get("id"));
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
                $tabla = tab_escala_salarial::find(Input::get("id"));
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
