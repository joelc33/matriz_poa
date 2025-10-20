<?php

namespace matriz\Http\Controllers\AcSeguimiento;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_ac;
use matriz\Models\AcSegto\tab_ac_ae;
use matriz\Models\AcSegto\tab_ac_ae_partida;
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\Mantenimiento\tab_lapso;
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

class formacuatroController extends Controller
{
    protected $tab_ac;

    public function __construct(tab_ac $tab_ac)
    {
        $this->middleware('auth');
        $this->tab_ac = $tab_ac;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista($id)
    {
        $data = tab_lapso::where('id', '=', $id)
        ->first();
        
        return View::make('seguimiento.ac.004.lista')->with('data', $data);
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
            $id_lapso = Input::get('id_lapso');

            $tab_ac = $this->tab_ac
            ->join('mantenimiento.tab_ejecutores as t01', 'ac_seguimiento.tab_ac.id_tab_ejecutores', '=', 't01.id')
            ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')
            ->select(
                'ac_seguimiento.tab_ac.id',
                'tx_ejecutor_ac',
                'ac_seguimiento.tab_ac.id_tab_ejecutores',
                'ac_seguimiento.tab_ac.in_activo',
                DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
                DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
                DB::raw("NOW() between t02.fe_inicio and t02.fe_fin as activo"),
                DB::raw("(select count(*) from ac_seguimiento.tab_meta_fisica t
                inner join ac_seguimiento.tab_ac_ae t01 on  (t01.id = t.id_tab_ac_ae)
                inner join ac_seguimiento.tab_meta_financiera t02 on  (t02.id_tab_meta_fisica = t.id)
                where (t.nu_meta_modificada != 0 or t02.mo_modificado_anual != 0) and de_desvio is null
                and t01.id_tab_ac =ac_seguimiento.tab_ac.id) as pend_desvio"),
                'nu_codigo',
                'de_ac',
                'de_lapso',
                'in_004',
                'in_abierta',
                'ac_seguimiento.tab_ac.id_ejecutor'
            )
            ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t02.id', '=', $id_lapso)
            ->where('ac_seguimiento.tab_ac.in_activo', '=', true);

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_ac->where('ac_seguimiento.tab_ac.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ac->where('tx_ejecutor_ac', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ac->count();
                $tab_ac->skip($start)->take($limit);
                $response['data']  = $tab_ac->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_ac.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ac->count();
                $tab_ac->skip($start)->take($limit);
                $response['data']  = $tab_ac->orderby('ac_seguimiento.tab_ac.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_ac.id', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 500);
        }
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function detalle()
    {
        $data = tab_ac::join('mantenimiento.tab_ejecutores as t01', 'ac_seguimiento.tab_ac.id_tab_ejecutores', '=', 't01.id')
        ->join('mantenimiento.tab_lapso as t02', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't02.id')
        ->select(
            'ac_seguimiento.tab_ac.id',
            'tx_ejecutor_ac',
            'ac_seguimiento.tab_ac.id_tab_ejecutores',
            'ac_seguimiento.tab_ac.in_activo',
            DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            'nu_codigo',
            'de_ac'
        )
        ->where('ac_seguimiento.tab_ac.id', '=', Input::get('codigo'))
        ->first();

        return View::make('seguimiento.ac.004.detalle')->with('data', $data);
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function datos($id)
    {
        $data = tab_ac::select(
            'id',
            'nu_codigo',
            'id_tab_ejecutores',
            'id_tab_ejercicio_fiscal',
            'id_tab_ac_predefinida',
            'id_tab_sectores',
            'id_tab_estatus',
            'id_tab_situacion_presupuestaria',
            'id_tab_tipo_registro',
            'co_new_etapa',
            'de_ac',
            'mo_ac',
            'mo_calculado',
            'fe_inicio',
            'fe_fin',
            'inst_mision',
            'inst_vision',
            'inst_objetivos',
            'nu_po_beneficiar',
            'nu_em_previsto',
            'tx_re_esperado',
            'in_activo',
            'id_tab_lapso'
        )
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.004.datos.lista')->with('data', $data);
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function datosstoreLista()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');

            $tab_ac = tab_ac_ae::select(
                'ac_seguimiento.tab_ac_ae.id',
                'id_tab_ac',
                'id_tab_ac_ae_predefinida',
                'ac_seguimiento.tab_ac_ae.id_tab_ejecutores',
                'bien_servicio',
                'id_tab_unidad_medida',
                'meta',
                'ponderacion',
                'id_tab_tipo_fondo',
                'mo_ae',
                'mo_ae_calculado',
                'ac_seguimiento.tab_ac_ae.in_activo',
                'nu_numero',
                'de_nombre',
                'de_unidad_medida',
                'tx_ejecutor_ac',
                't03.id_ejecutor',
                DB::raw("to_char(fecha_inicio, 'dd-mm-YYYY') as fecha_inicio"),
                DB::raw("to_char(fecha_fin, 'dd-mm-YYYY') as fecha_fin")
            )
            ->join('mantenimiento.tab_ac_ae_predefinida as t01', 'ac_seguimiento.tab_ac_ae.id_tab_ac_ae_predefinida', '=', 't01.id')
            ->join('mantenimiento.tab_unidad_medida as t02', 'ac_seguimiento.tab_ac_ae.id_tab_unidad_medida', '=', 't02.id')
            ->join('mantenimiento.tab_ejecutores as t03', 'ac_seguimiento.tab_ac_ae.id_tab_ejecutores', '=', 't03.id')
            ->join('ac_seguimiento.tab_ac as t04', 'ac_seguimiento.tab_ac_ae.id_tab_ac', '=', 't04.id')
            ->where('id_tab_ac', '=', Input::get('ac'))
            ->where('ac_seguimiento.tab_ac_ae.in_activo', '=', true);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ac->where('de_nombre', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ac->count();
                $tab_ac->skip($start)->take($limit);
                $response['data']  = $tab_ac->orderby('ac_seguimiento.tab_ac_ae.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ac->count();
                $tab_ac->skip($start)->take($limit);
                $response['data']  = $tab_ac->orderby('ac_seguimiento.tab_ac_ae.id', 'ASC')->get()->toArray();
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
    public function editar($id)
    {
        $data = tab_ac_ae::select(
            'id',
            'id_tab_ac',
            'id_tab_ac_ae_predefinida',
            'id_tab_ejecutores',
            'bien_servicio',
            'id_tab_unidad_medida',
            'meta',
            'ponderacion',
            'id_tab_tipo_fondo',
            'mo_ae',
            'mo_ae_calculado',
            'fecha_inicio',
            'fecha_fin',
            'in_activo'
        )
        ->where('id', '=', $id)
        ->first();
        return View::make('seguimiento.ac.004.actividad.lista')->with('data', $data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function actividadstoreLista()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');

            $tab_meta_fisica = tab_meta_fisica::select(
                'ac_seguimiento.tab_meta_fisica.id',
                'id_tab_ac_ae',
                'codigo',
                'nb_meta',
                'id_tab_unidad_medida',
                'tx_prog_anual',
                'fecha_inicio',
                'fecha_fin',
                'nb_responsable',
                'ac_seguimiento.tab_meta_fisica.in_activo',
                'de_unidad_medida',
                'ac_seguimiento.tab_meta_fisica.in_cargado',
                'in_004',
                DB::raw("to_char(fecha_inicio, 'dd-mm-YYYY') as fecha_inicio"),
                DB::raw("to_char(fecha_fin, 'dd-mm-YYYY') as fecha_fin")
            )
             ->join('mantenimiento.tab_unidad_medida as t01', 'ac_seguimiento.tab_meta_fisica.id_tab_unidad_medida', '=', 't01.id')
             ->join('ac_seguimiento.tab_meta_financiera as t02', 'ac_seguimiento.tab_meta_fisica.id', '=', 't02.id_tab_meta_fisica')
             ->where(function ($query) {
             $query->orWhere('nu_meta_modificada', '!=', 0)
             ->orWhere('mo_modificado_anual', '!=', 0);
             })
              ->where('id_tab_ac_ae', '=', Input::get('ac_ae'))
             ->where('ac_seguimiento.tab_meta_fisica.in_activo', '=', true);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_meta_fisica->where('nb_meta', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_meta_fisica->distinct()->get()->count();
                $tab_meta_fisica->skip($start)->take($limit);
                $response['data']  = $tab_meta_fisica->orderby('ac_seguimiento.tab_meta_fisica.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_meta_fisica->distinct()->get()->count();
                $tab_meta_fisica->skip($start)->take($limit);
                $response['data']  = $tab_meta_fisica->orderby('ac_seguimiento.tab_meta_fisica.id', 'ASC')->get()->toArray();
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
    public function nuevoActividad($id)
    {
        $fechaI = '01-01-'.Session::get('ejercicio');
        $fechaF = '31-12-'.Session::get('ejercicio');

        $data = json_encode(array(
          "id_tab_ac_ae" => $id,
          "fe_ini" => $fechaI,
          "fe_fin" => $fechaF
        ));

        $limite = json_encode(array('fe_ini' => $fechaI, 'fe_fin' => $fechaF ));

        return View::make('seguimiento.ac.004.actividad.editar')
        ->with('data', $data)
        ->with('fecha', $limite);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editarActividad($id)
    {
        $data = tab_meta_fisica::select(
            'id',
            'id_tab_ac_ae',
            'codigo',
            'nb_meta',
            'id_tab_unidad_medida',
            'tx_prog_anual',
            'fecha_inicio',
            'fecha_fin',
            'nb_responsable',
            'in_activo',
            'created_at',
            'updated_at',
            DB::raw("nu_meta_modificada  + nu_meta_modificada_periodo as nu_meta_modificada"),
            DB::raw("tx_prog_anual::numeric +  nu_meta_modificada  + nu_meta_modificada_periodo as nu_meta_actualizada"),
            'nu_obtenido',
            'nu_corte',
            'id_tab_municipio_detalle',
            'id_tab_parroquia_detalle',
            'in_cargado',
            'de_desvio',
            DB::raw("EXTRACT(year FROM fecha_inicio::DATE) as id_tab_ejercicio_fiscal")
        )
        ->where('id', '=', $id)
        ->first();

        $fechaI = '01-01-'.($data->id_tab_ejercicio_fiscal);
        $fechaF = '31-12-'.($data->id_tab_ejercicio_fiscal);

        $limite = json_encode(array('fe_ini' => $fechaI, 'fe_fin' => $fechaF ));

        //$data = json_encode(array_merge( $data->toArray(), $limite ));

        return View::make('seguimiento.ac.004.actividad.editar')
        ->with('data', $data)
        ->with('fecha', $limite);
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
                $validator= Validator::make(Input::all(), tab_meta_fisica::$validarEditarDesvio);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_meta_fisica::find($id);
                $tabla->nb_meta = Input::get("actividad");
                $tabla->id_tab_unidad_medida = Input::get("unidad_medida");
                $tabla->tx_prog_anual = Input::get("programado_anual");
                $tabla->fecha_inicio = Input::get("fecha_inicio");
                $tabla->fecha_fin = Input::get("fecha_culminacion");
                $tabla->nb_responsable = Input::get("responsable");
                $tabla->de_desvio = str_replace('"', '', Input::get("desvio"));
                $tabla->in_004 = true;
                $tabla->save();
                
                
                $data = tab_meta_fisica::join('ac_seguimiento.tab_ac_ae as t05', 'tab_meta_fisica.id_tab_ac_ae', '=', 't05.id')
                ->join('ac_seguimiento.tab_ac as t01', 't05.id_tab_ac', '=', 't01.id')
                ->select(
                    't01.id',
                    'id_tab_ac_ae'    
                )
                ->where('tab_meta_fisica.id', '=', $id)
                ->first();                

                $cant = tab_meta_fisica::join('ac_seguimiento.tab_meta_financiera as t01', 't01.id_tab_meta_fisica', '=', 'tab_meta_fisica.id')
                ->where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
                ->where('de_desvio', '=', null)
                ->count();   
                
//                        var_dump($cant);
//        exit();
                
                if($cant==0){
                    
                $tabla = tab_ac::find($data->id);
                $tabla->in_004 = true;
                $tabla->save();
                
                }                
                
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
                $validator = Validator::make(Input::all(), tab_meta_fisica::$validarCrearMeta);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_meta_fisica();
                $tabla->id_tab_ac_ae = Input::get("ac_ae");
                $tabla->nb_meta = Input::get("actividad");
                $tabla->id_tab_unidad_medida = Input::get("unidad_medida");
                $tabla->tx_prog_anual = Input::get("programado_anual");
                $tabla->fecha_inicio = Input::get("fecha_inicio");
                $tabla->fecha_fin = Input::get("fecha_culminacion");
                $tabla->nb_responsable = Input::get("responsable");
                $tabla->de_desvio = str_replace('"', '', Input::get("desvio"));
                $tabla->id_tab_origen = 2;
                $tabla->in_cargado = false;
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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function financierastoreLista($id)
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');

            $tab_meta_financiera = tab_meta_financiera::select(
                'ac_seguimiento.tab_meta_financiera.id',
                'id_tab_meta_fisica',
                'ac_seguimiento.tab_meta_financiera.id_tab_municipio_detalle',
                'ac_seguimiento.tab_meta_financiera.id_tab_parroquia_detalle',
                DB::raw('coalesce(mo_presupuesto,0) + coalesce(mo_modificado_anual,0) + coalesce(mo_modificado,0) as mo_presupuesto'),
                'co_partida',
                'id_tab_fuente_financiamiento',
                'ac_seguimiento.tab_meta_financiera.in_activo',
                'ac_seguimiento.tab_meta_financiera.in_cargado',
                'de_fuente_financiamiento'
            )
             ->join('mantenimiento.tab_fuente_financiamiento as t02', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't02.id')
             ->where('id_tab_meta_fisica', '=', $id)
             ->where('ac_seguimiento.tab_meta_financiera.in_activo', '=', true);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_meta_financiera->where('de_fuente_financiamiento', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_meta_financiera->count();
                $tab_meta_financiera->skip($start)->take($limit);
                $response['data']  = $tab_meta_financiera->orderby('ac_seguimiento.tab_meta_financiera.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_meta_financiera->count();
                $tab_meta_financiera->skip($start)->take($limit);
                $response['data']  = $tab_meta_financiera->orderby('ac_seguimiento.tab_meta_financiera.id', 'ASC')->get()->toArray();
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
    public function nuevoFinanciera($id)
    {

        $data = tab_meta_fisica::select('id as id_tab_meta_fisica', 'id_tab_ac_ae')
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.004.financiera.editar')
        ->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editarFinanciera($id)
    {

        $data = tab_meta_financiera::select(
            'ac_seguimiento.tab_meta_financiera.id',
            'id_tab_meta_fisica',
            'ac_seguimiento.tab_meta_financiera.id_tab_municipio_detalle',
            'ac_seguimiento.tab_meta_financiera.id_tab_parroquia_detalle',
            'mo_presupuesto',
            'co_partida',
            'id_tab_fuente_financiamiento',
            'ac_seguimiento.tab_meta_financiera.in_activo',
            'ac_seguimiento.tab_meta_financiera.in_cargado',
            'mo_modificado_anual',
            'mo_actualizado_anual',
            'mo_comprometido',
            'mo_causado',
            'mo_pagado',
            'id_tab_estado',
            'id_tab_ac_ae'
        )
        ->join('mantenimiento.tab_municipio_detalle as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_municipio_detalle', '=', 't01.id')
        ->join('ac_seguimiento.tab_meta_fisica as t02', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't02.id')
        ->where('ac_seguimiento.tab_meta_financiera.id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.004.financiera.editar')
        ->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function partida()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ac_ae_partida::select(DB::raw('left(co_partida, 3) as co_partida'))
        ->where('id_tab_ac_ae', '=', Input::get('ac_ae'))
        ->where('in_activo', '=', true)
        ->groupBy(DB::raw('1'))
        ->orderby('co_partida', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function guardarFinanciera($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_meta_financiera::$validarEditarMeta);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_meta_financiera::find($id);
                $tabla->id_tab_municipio_detalle = Input::get("municipio");
                $tabla->id_tab_parroquia_detalle = Input::get("parroquia");
                $tabla->mo_presupuesto = Input::get("presupuesto");
                $tabla->co_partida = Input::get("partida");
                $tabla->id_tab_fuente_financiamiento = Input::get("fuente_financiamiento");
                //$tabla->in_cargado = true;
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
                $validator = Validator::make(Input::all(), tab_meta_financiera::$validarCrearMeta);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_meta_financiera();
                $tabla->id_tab_meta_fisica = Input::get("meta_fisica");
                $tabla->id_tab_municipio_detalle = Input::get("municipio");
                $tabla->id_tab_parroquia_detalle = Input::get("parroquia");
                $tabla->mo_presupuesto = Input::get("presupuesto");
                $tabla->co_partida = Input::get("partida");
                $tabla->id_tab_fuente_financiamiento = Input::get("fuente_financiamiento");
                $tabla->id_tab_origen = 2;
                $tabla->in_cargado = false;
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
            $tabla = tab_meta_fisica::find(Input::get("id"));
            $tabla->in_activo = false;
            $tabla->save();

            $tabla_meta_financiera = tab_meta_financiera::where('id_tab_meta_fisica', '=', Input::get('id'))
            ->update(array('in_activo' => false));

            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Registro borrado con Exito!';
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
    public function eliminarFinanciera()
    {
        DB::beginTransaction();
        try {
            $tabla = tab_meta_financiera::find(Input::get("id"));
            $tabla->delete();

            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Registro borrado con Exito!';
            return Response::json($response, 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 200);
        }
    }

}
