<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_partidas;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class partidaController extends Controller
{
    protected $tab_partidas;

    public function __construct(tab_partidas $tab_partidas)
    {
        $this->middleware('auth');
        $this->tab_partidas = $tab_partidas;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.partida.lista');
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

            $tab_partidas = $this->tab_partidas
            ->select(
                'id',
                'id_tab_ejercicio_fiscal',
                'co_partida',
                'tx_nombre',
                'tx_abreviacion',
                'ace_mov',
                'in_activo'
            );

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    //$tab_partidas->where('tx_nombre', 'ILIKE', "%$variable%");
                    $tab_partidas->whereRaw("co_partida||tx_nombre  ILIKE '%".$variable."%'");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_partidas->count();
                $tab_partidas->skip($start)->take($limit);
                $response['data']  = $tab_partidas->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_partidas->count();
                $tab_partidas->skip($start)->take($limit);
                $response['data']  = $tab_partidas->orderby('id', 'ASC')->get()->toArray();
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
        return View::make('mantenimiento.partida.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_partidas::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'co_partida',
            'tx_nombre',
            'tx_abreviacion',
            'ace_mov',
            'in_activo'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.partida.editar')->with('data', $data);
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
                    $validator= Validator::make(Input::all(), tab_partidas::$validarEditar);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = tab_partidas::find($id);
                    $tabla->id_tab_ejercicio_fiscal = Input::get("ejercicio_fiscal");
                    $tabla->co_partida = Input::get("partida");
                    $tabla->tx_nombre = Input::get("nombre");
                    $tabla->ace_mov = Input::get('ace_mov', false);
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
                    $validator = Validator::make(Input::all(), tab_partidas::$validarCrear);
                    if ($validator->fails()) {
                        return Response::json(array(
                          'success' => false,
                          'msg' => $validator->getMessageBag()->toArray()
                        ));
                    }
                    $tabla = new tab_partidas();
                    $tabla->id_tab_ejercicio_fiscal = Input::get("ejercicio_fiscal");
                    $tabla->co_partida = Input::get("partida");
                    $tabla->tx_nombre = Input::get("nombre");
                    $tabla->ace_mov = Input::get('ace_mov', false);
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
                $tabla = tab_partidas::find(Input::get("id"));
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
                $tabla = tab_partidas::find(Input::get("id"));
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
