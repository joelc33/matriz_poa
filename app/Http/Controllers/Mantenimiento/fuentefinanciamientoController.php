<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_fuente_financiamiento;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class fuentefinanciamientoController extends Controller
{
    protected $tab_fuente_financiamiento;

    public function __construct(tab_fuente_financiamiento $tab_fuente_financiamiento)
    {
        $this->middleware('auth');
        $this->tab_fuente_financiamiento = $tab_fuente_financiamiento;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.fuentefinanciamiento.lista');
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

            $tab_fuente_financiamiento = $this->tab_fuente_financiamiento
            ->join('mantenimiento.tab_tipo_fondo as t01', 't01.id', '=', 'mantenimiento.tab_fuente_financiamiento.id_tab_tipo_fondo')
            ->select(
                'mantenimiento.tab_fuente_financiamiento.id',
                'de_fuente_financiamiento',
                'mantenimiento.tab_fuente_financiamiento.in_activo',
                'id_tab_tipo_fondo',
                'de_tipo_fondo'
            );

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_fuente_financiamiento->where('de_fuente_financiamiento', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_fuente_financiamiento->count();
                $tab_fuente_financiamiento->skip($start)->take($limit);
                $response['data']  = $tab_fuente_financiamiento->orderby('mantenimiento.tab_fuente_financiamiento.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_fuente_financiamiento->count();
                $tab_fuente_financiamiento->skip($start)->take($limit);
                $response['data']  = $tab_fuente_financiamiento->orderby('mantenimiento.tab_fuente_financiamiento.id', 'ASC')->get()->toArray();
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
        return View::make('mantenimiento.fuentefinanciamiento.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_fuente_financiamiento::select('id', 'de_fuente_financiamiento', 'in_activo', 'id_tab_tipo_fondo')
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.fuentefinanciamiento.editar')->with('data', $data);
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
                    $validator= Validator::make(Input::all(), tab_fuente_financiamiento::$validarEditar);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = tab_fuente_financiamiento::find($id);
                    $tabla->id_tab_tipo_fondo = Input::get("fondo");
                    $tabla->de_fuente_financiamiento = Input::get("fuente");
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
                    $validator = Validator::make(Input::all(), tab_fuente_financiamiento::$validarCrear);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = new tab_fuente_financiamiento();
                    $tabla->id_tab_tipo_fondo = Input::get("fondo");
                    $tabla->de_fuente_financiamiento = Input::get("fuente");
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
                $tabla = tab_fuente_financiamiento::find(Input::get("id"));
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
                $tabla = tab_fuente_financiamiento::find(Input::get("id"));
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
