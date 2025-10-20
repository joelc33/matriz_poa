<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_ejecutores;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class ejecutorController extends Controller
{
    protected $tab_ejecutores;

    public function __construct(tab_ejecutores $tab_ejecutores)
    {
        //$this->middleware('poa');
        $this->middleware('auth');
        $this->tab_ejecutores = $tab_ejecutores;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.ejecutor.lista');
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

            $tab_ejecutores = $this->tab_ejecutores
            ->select(
                'id',
                'id_ejecutor',
                'tx_ejecutor',
                'car_01',
                'car_02',
                'car_03',
                'car_04',
                'id_tab_tipo_ejecutor',
                'id_tab_ambito_ejecutor',
                'codigo_01',
                'codigo_eje',
                'in_activo',
                'de_correo',
                'de_telefono',
                'in_verificado'
            );

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ejecutores->where('tx_ejecutor', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ejecutores->count();
                $tab_ejecutores->skip($start)->take($limit);
                $response['data']  = $tab_ejecutores->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ejecutores->count();
                $tab_ejecutores->skip($start)->take($limit);
                $response['data']  = $tab_ejecutores->orderby('id', 'ASC')->get()->toArray();
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
        $data = json_encode(array("id" => "", "id_ejecutor" => ""));
        return View::make('mantenimiento.ejecutor.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_ejecutores::select(
            'id',
            'id_ejecutor',
            'tx_ejecutor',
            'car_01',
            'car_02',
            'car_03',
            'car_04',
            'id_tab_tipo_ejecutor',
            'id_tab_ambito_ejecutor',
            'codigo_01',
            'codigo_eje',
            'in_activo',
            'de_correo',
            'de_telefono',
            'in_verificado'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.ejecutor.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_ejecutores::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_ejecutores::find($id);
                $tabla->tx_ejecutor = Input::get("nombre");
                $tabla->car_01 = Input::get("car_01");
                $tabla->car_02 = Input::get("car_02");
                $tabla->car_03 = Input::get("car_03");
                $tabla->car_04 = Input::get("car_04");
                $tabla->id_tab_tipo_ejecutor = Input::get("tipo");
                $tabla->id_tab_ambito_ejecutor = Input::get("ambito");
                $tabla->codigo_01 = Input::get("codigo_01");
                $tabla->codigo_eje = Input::get("codigo_eje");
                $tabla->de_correo = Input::get("correo");
                $tabla->de_telefono = Input::get("telefono");
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
                $validator = Validator::make(Input::all(), tab_ejecutores::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_ejecutores();
                $tabla->id_ejecutor = Input::get("codigo");
                $tabla->tx_ejecutor = Input::get("nombre");
                $tabla->car_01 = Input::get("car_01");
                $tabla->car_02 = Input::get("car_02");
                $tabla->car_03 = Input::get("car_03");
                $tabla->car_04 = Input::get("car_04");
                $tabla->id_tab_tipo_ejecutor = Input::get("tipo");
                $tabla->id_tab_ambito_ejecutor = Input::get("ambito");
                $tabla->codigo_01 = Input::get("codigo_01");
                $tabla->codigo_eje = Input::get("codigo_eje");
                $tabla->de_correo = Input::get("correo");
                $tabla->de_telefono = Input::get("telefono");
                $tabla->in_activo = 'TRUE';
                $tabla->in_verificado = 'FALSE';
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
            $tabla = tab_ejecutores::find(Input::get("id"));
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
            $tabla = tab_ejecutores::find(Input::get("id"));
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
