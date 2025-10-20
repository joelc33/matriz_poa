<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_distribucion_municipio;
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

class distribucionController extends Controller
{
    protected $tab_distribucion_municipio;

    public function __construct(tab_distribucion_municipio $tab_distribucion_municipio)
    {
        $this->middleware('auth');
        $this->tab_distribucion_municipio = $tab_distribucion_municipio;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.distribucion.lista');
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

            $tab_distribucion_municipio = $this->tab_distribucion_municipio
            ->join('mantenimiento.tab_municipio as t01', 't01.id', '=', 'mantenimiento.tab_distribucion_municipio.id_tab_municipio')
            ->select(
                'mantenimiento.tab_distribucion_municipio.id',
                'id_tab_ejercicio_fiscal',
                'id_tab_municipio',
                'co_partida',
                'nu_base_censo',
                'nu_factor_poblacion',
                'cuatrocinco_ppi',
                'cincocero_fpp',
                'superficie_km',
                'superficie_factor',
                'extension_territorio',
                'mo_total',
                'de_municipio'
            )
             ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
             ->where('mantenimiento.tab_distribucion_municipio.in_activo', '=', true);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_distribucion_municipio->where('de_municipio', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_distribucion_municipio->count();
                $tab_distribucion_municipio->skip($start)->take($limit);
                $response['data']  = $tab_distribucion_municipio->orderby('mantenimiento.tab_distribucion_municipio.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_distribucion_municipio->count();
                $tab_distribucion_municipio->skip($start)->take($limit);
                $response['data']  = $tab_distribucion_municipio->orderby('mantenimiento.tab_distribucion_municipio.id', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 500);
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
        return View::make('mantenimiento.distribucion.nuevo')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_distribucion_municipio::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'id_tab_municipio',
            'co_partida',
            'nu_base_censo',
            'nu_factor_poblacion',
            'cuatrocinco_ppi',
            'cincocero_fpp',
            'superficie_km',
            'superficie_factor',
            'extension_territorio',
            'mo_total'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.distribucion.editar')->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function guardar($id = null)
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

            $factor_poblacion = Input::get("base_censo")/$data->nu_total_poblacion;
            $cuatrocinco_ppi = round($data->cuatrocinco_ppi/21, 0);
            $cincocero_fpp = round($data->cincocero_fpp*$factor_poblacion, 0);
            $superficie_factor = Input::get("superficie_km")/$data->nu_superficie;
            $extension_territorio = round($data->nu_extension_territorio*$superficie_factor, 0);
            $mo_total = round($cuatrocinco_ppi+$cincocero_fpp+$extension_territorio, 0);

        } else {

            return Response::json(array(
              'success' => false,
              'msg' => array('uno'=>'Debe configurar primero los parametros iniciales.')
            ));

        }
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_distribucion_municipio::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_distribucion_municipio::find($id);
                $tabla->co_partida = Input::get("partida");
                $tabla->nu_base_censo = Input::get("base_censo");
                $tabla->superficie_km = Input::get("superficie_km");
                /*$tabla->nu_factor_poblacion = $factor_poblacion;
                $tabla->cuatrocinco_ppi = $cuatrocinco_ppi;
                $tabla->cincocero_fpp = $cincocero_fpp;
                $tabla->superficie_factor = $superficie_factor;
                $tabla->extension_territorio = $extension_territorio;*/
                $tabla->nu_factor_poblacion = Input::get("factor_poblacion");
                $tabla->cuatrocinco_ppi = Input::get("cuatrocinco_ppi");
                $tabla->cincocero_fpp = Input::get("cincocero_fpp");
                $tabla->superficie_factor = Input::get("superficie_factor");
                $tabla->extension_territorio = Input::get("extension_territorio");
                $tabla->mo_total = Input::get("cuatrocinco_ppi")+Input::get("cincocero_fpp")+Input::get("extension_territorio");
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
                $validator = Validator::make(Input::all(), tab_distribucion_municipio::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_distribucion_municipio();
                $tabla->id_tab_ejercicio_fiscal = Session::get('ejercicio');
                $tabla->id_tab_municipio = Input::get("municipio");
                $tabla->co_partida = Input::get("partida");
                $tabla->nu_base_censo = Input::get("base_censo");
                $tabla->superficie_km = Input::get("superficie_km");
                $tabla->nu_factor_poblacion = $factor_poblacion;
                $tabla->cuatrocinco_ppi = $cuatrocinco_ppi;
                $tabla->cincocero_fpp = $cincocero_fpp;
                $tabla->superficie_factor = $superficie_factor;
                $tabla->extension_territorio = $extension_territorio;
                $tabla->mo_total = $mo_total;
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
    
            public function eliminar()
        {
            DB::beginTransaction();
            try {
                $tabla = tab_distribucion_municipio::find(Input::get("id"));
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
        
        public function habilitar()
        {
            DB::beginTransaction();
            try {
                $tabla = tab_distribucion_municipio::find(Input::get("id"));
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
