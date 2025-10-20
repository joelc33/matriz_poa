<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_distribucion_parametro;
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

class distribucionparametroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function nuevo()
    {
        $data = json_encode(array("id" => ""));
        return View::make('mantenimiento.distribucion.parametro.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar()
    {
        if (tab_distribucion_parametro::where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))->exists()) {

            $data = tab_distribucion_parametro::select(
                'id',
                'id_tab_ejercicio_fiscal',
                'nu_total_poblacion',
                'cuatrocinco_ppi',
                'cincocero_fpp',
                'nu_superficie',
                'nu_extension_territorio'
            )
            ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->first();

        } else {

            $data = json_encode(array("id" => ""));

        }

        return View::make('mantenimiento.distribucion.parametro.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_distribucion_parametro::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_distribucion_parametro::find($id);
                $tabla->nu_total_poblacion = Input::get("poblacion");
                $tabla->cuatrocinco_ppi = Input::get("parte_igual");
                $tabla->cincocero_fpp = Input::get("parte_proporcional");
                $tabla->nu_superficie = Input::get("total_superficie");
                $tabla->nu_extension_territorio = Input::get("extension_territorio");
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
                $validator = Validator::make(Input::all(), tab_distribucion_parametro::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_distribucion_parametro();
                $tabla->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                $tabla->nu_total_poblacion = Input::get("poblacion");
                $tabla->cuatrocinco_ppi = Input::get("parte_igual");
                $tabla->cincocero_fpp = Input::get("parte_proporcional");
                $tabla->nu_superficie = Input::get("total_superficie");
                $tabla->nu_extension_territorio = Input::get("extension_territorio");
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
}
