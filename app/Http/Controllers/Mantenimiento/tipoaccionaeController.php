<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_ac_ae_predefinida;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class tipoaccionaeController extends Controller
{
    protected $tab_ac_ae_predefinida;

    public function __construct(tab_ac_ae_predefinida $tab_ac_ae_predefinida)
    {
        $this->middleware('auth');
        $this->tab_ac_ae_predefinida = $tab_ac_ae_predefinida;
    }
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista($id)
    {
        $data = array("id" => $id);
        return View::make('mantenimiento.tipoaccion.ae.lista')->with('data', $data);
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
            $ac = Input::get('ac');

            $tab_ac_ae_predefinida = $this->tab_ac_ae_predefinida
            ->select('id', 'id_padre', 'nu_numero', 'de_nombre', 'in_activo')
            ->where('id_padre', '=', $ac);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ac_ae_predefinida->where('de_nombre', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ac_ae_predefinida->count();
                $tab_ac_ae_predefinida->skip($start)->take($limit);
                $response['data']  = $tab_ac_ae_predefinida->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ac_ae_predefinida->count();
                $tab_ac_ae_predefinida->skip($start)->take($limit);
                $response['data']  = $tab_ac_ae_predefinida->orderby('id', 'ASC')->get()->toArray();
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
    public function nuevo($id)
    {
        $data = json_encode(array("ac" => $id));
        return View::make('mantenimiento.tipoaccion.ae.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_ac_ae_predefinida::select('id', 'de_nombre', 'nu_numero', 'in_activo')
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.tipoaccion.ae.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_ac_ae_predefinida::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_ac_ae_predefinida::find($id);
                $tabla->de_nombre = Input::get("nombre");
                $tabla->nu_numero = Input::get("numero");
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
                $validator = Validator::make(Input::all(), tab_ac_ae_predefinida::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_ac_ae_predefinida();
                $tabla->id_padre = Input::get("ac");
                $tabla->de_nombre = Input::get("nombre");
                $tabla->nu_numero = Input::get("numero");
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
            $tabla = tab_ac_ae_predefinida::find(Input::get("id"));
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

}
