<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_ejecutores;
use matriz\Models\Mantenimiento\tab_ejecutores_pr;
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
    
    public function listaPr($id)
    {
        $data = array("id" => $id);
        return View::make('mantenimiento.ejecutor.pr.lista')->with('data', $data);
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
                'in_verificado',
                'id_tab_ac_predefinida'
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
    
    public function storeListaPr()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');
            $id_tab_ejecutores = Input::get('id_tab_ejecutores');

            $tab_ejecutores_pr = tab_ejecutores_pr::join('mantenimiento.tab_ac_predefinida as t01', 'mantenimiento.tab_ejecutores_pr.id_tab_ac_predefinida', '=', 't01.id')
            ->join('mantenimiento.tab_sectores as t02', 't01.id_tab_sectores', '=', 't02.id')
            ->select('mantenimiento.tab_ejecutores_pr.id', 'nu_descripcion', 'nu_original', 'de_nombre')
                    
            ->where('id_tab_ejecutores', '=', $id_tab_ejecutores);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ejecutores_pr->where('de_nombre', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ejecutores_pr->count();
                $tab_ejecutores_pr->skip($start)->take($limit);
                $response['data']  = $tab_ejecutores_pr->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ejecutores_pr->count();
                $tab_ejecutores_pr->skip($start)->take($limit);
                $response['data']  = $tab_ejecutores_pr->orderby('id', 'ASC')->get()->toArray();
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
    
    public function nuevoPr($id)
    {
        $data = json_encode(array("id_tab_ejecutores" => $id));
        return View::make('mantenimiento.ejecutor.pr.editar')->with('data', $data);
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
            'in_verificado',
            'id_tab_ac_predefinida'
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
                $tabla->id_tab_ac_predefinida = Input::get("id_tab_ac_predefinida");
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
                $tabla->id_tab_ac_predefinida = Input::get("id_tab_ac_predefinida");
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
    
    public function guardarPr($id = null)
    {
        DB::beginTransaction();
            try {
                
            $data = tab_ejecutores_pr::where('id_tab_ejecutores', '=', Input::get("id_tab_ejecutores"))
            ->where('id_tab_ac_predefinida', '=', Input::get("id_tab_ac_predefinida"))
            ->first();                
                
                if ($data) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => 'El Programa seleccionado ya se encuenta asociado a esta unidad ejecutora'
                    ));
                }                

                $tabla = new tab_ejecutores_pr();
                $tabla->id_tab_ejecutores = Input::get("id_tab_ejecutores");
                $tabla->id_tab_ac_predefinida = Input::get("id_tab_ac_predefinida");
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
    
    public function eliminarPr()
    {
        DB::beginTransaction();
        try {
            $tabla = tab_ejecutores_pr::find(Input::get("id"));
            $tabla->delete();
            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Registro Eliminado con Exito!';
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
