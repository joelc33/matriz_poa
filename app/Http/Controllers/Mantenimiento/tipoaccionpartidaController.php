<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_ac_ae_partida;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class tipoaccionpartidaController extends Controller
{
    protected $tab_ac_ae_partida;

    public function __construct(tab_ac_ae_partida $tab_ac_ae_partida)
    {
        $this->middleware('auth');
        $this->tab_ac_ae_partida = $tab_ac_ae_partida;
    }
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista($id)
    {
        $data = array("id" => $id);
        return View::make('mantenimiento.tipoaccion.partida.lista')->with('data', $data);
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
            $limit  = Input::get('limit', 15);
            $variable = Input::get('variable');
            $ac = Input::get('ac');

            $tab_ac_ae_partida = $this->tab_ac_ae_partida
            ->join('mantenimiento.tab_ac_ae_predefinida as t01', 't01.id', '=', 'mantenimiento.tab_ac_ae_partida.id_tab_ac_ae_predefinida')
            ->select(
                'mantenimiento.tab_ac_ae_partida.id',
                'id_tab_ac_predefinida',
                'id_tab_ac_ae_predefinida',
                'nu_partida',
                'de_partida',
                'mantenimiento.tab_ac_ae_partida.in_activo',
                'nu_numero',
                'de_nombre'
            )
            ->where('id_tab_ac_predefinida', '=', $ac);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ac_ae_partida->where('de_partida', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ac_ae_partida->count();
                $tab_ac_ae_partida->skip($start)->take($limit);
                $response['data']  = $tab_ac_ae_partida->orderby('id', 'DESC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ac_ae_partida->count();
                $tab_ac_ae_partida->skip($start)->take($limit);
                $response['data']  = $tab_ac_ae_partida
                ->orderby('nu_numero', 'DESC')
                ->orderby('nu_partida', 'DESC')
                ->get()->toArray();
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
        $data = json_encode(array("id_tab_ac_predefinida" => $id));
        return View::make('mantenimiento.tipoaccion.partida.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_ac_ae_partida::select('id', 'id_tab_ac_predefinida', 'id_tab_ac_ae_predefinida', 'nu_partida', 'de_partida', 'in_activo')
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.tipoaccion.partida.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_ac_ae_partida::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_ac_ae_partida::find($id);
                $tabla->id_tab_ac_ae_predefinida = Input::get("ae");
                $tabla->nu_partida = Input::get("partida");
                $tabla->de_partida = Input::get("denominacion");
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
                $validator = Validator::make(Input::all(), tab_ac_ae_partida::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_ac_ae_partida();
                $tabla->id_tab_ac_predefinida = Input::get("ac");
                $tabla->id_tab_ac_ae_predefinida = Input::get("ae");
                $tabla->nu_partida = Input::get("partida");
                $tabla->de_partida = Input::get("denominacion");
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
            $tabla = tab_ac_ae_partida::find(Input::get("id"));
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
