<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_clasificador_tipo;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class clasificadortipoController extends Controller
{
    protected $tab_clasificador_tipo;

    public function __construct(tab_clasificador_tipo $tab_clasificador_tipo)
    {
        $this->middleware('auth');
        $this->tab_clasificador_tipo = $tab_clasificador_tipo;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.clasificadortipo.lista');
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

            $tab_clasificador_tipo = $this->tab_clasificador_tipo
            ->join('mantenimiento.tab_tipo_personal as t01', 't01.id', '=', 'mantenimiento.tab_clasificador_tipo.id_tab_tipo_personal')
            ->select(
                'mantenimiento.tab_clasificador_tipo.id',
                'id_tab_ejercicio_fiscal',
                'id_tab_tipo_personal',
                'nu_masculino',
                'nu_femenino',
                'mo_sueldo',
                'mo_compensacion',
                'mo_primas',
                'mantenimiento.tab_clasificador_tipo.in_activo',
                'nu_codigo',
                'de_tipo_personal'
            );

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_clasificador_tipo->where('de_tipo_personal', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_clasificador_tipo->count();
                $tab_clasificador_tipo->skip($start)->take($limit);
                $response['data']  = $tab_clasificador_tipo->orderby('mantenimiento.tab_clasificador_tipo.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_clasificador_tipo->count();
                $tab_clasificador_tipo->skip($start)->take($limit);
                $response['data']  = $tab_clasificador_tipo->orderby('mantenimiento.tab_clasificador_tipo.id', 'ASC')->get()->toArray();
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
        return View::make('mantenimiento.clasificadortipo.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_clasificador_tipo::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'id_tab_tipo_personal',
            'nu_masculino',
            'nu_femenino',
            'mo_sueldo',
            'mo_compensacion',
            'mo_primas',
            'in_activo'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.clasificadortipo.editar')->with('data', $data);
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
                    $validator= Validator::make(Input::all(), tab_clasificador_tipo::$validarEditar);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = tab_clasificador_tipo::find($id);
                    $tabla->id_tab_ejercicio_fiscal = Input::get("ejercicio_fiscal");
                    $tabla->id_tab_tipo_personal = Input::get("tipo_personal");
                    $tabla->nu_masculino = Input::get("masculino");
                    $tabla->nu_femenino = Input::get("femenino");
                    $tabla->mo_sueldo = Input::get("sueldo");
                    $tabla->mo_compensacion = Input::get("compensacion");
                    $tabla->mo_primas = Input::get("primas");
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
                    $validator = Validator::make(Input::all(), tab_clasificador_tipo::$validarCrear);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = new tab_clasificador_tipo();
                    $tabla->id_tab_ejercicio_fiscal = Input::get("ejercicio_fiscal");
                    $tabla->id_tab_tipo_personal = Input::get("tipo_personal");
                    $tabla->nu_masculino = Input::get("masculino");
                    $tabla->nu_femenino = Input::get("femenino");
                    $tabla->mo_sueldo = Input::get("sueldo");
                    $tabla->mo_compensacion = Input::get("compensacion");
                    $tabla->mo_primas = Input::get("primas");
                    $tabla->in_activo = true;
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
                $tabla = tab_clasificador_tipo::find(Input::get("id"));
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
                $tabla = tab_clasificador_tipo::find(Input::get("id"));
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
