<?php

namespace matriz\Http\Controllers\AcSeguimiento;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_ac;
use matriz\Models\AcSegto\tab_ac_ae;
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\Mantenimiento\tab_lapso;
use matriz\Models\Mantenimiento\tab_fuente_financiamiento;
use matriz\Models\AcSegto\tab_ac_ae_fuente;
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

class formatresController extends Controller
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
        
        return View::make('seguimiento.ac.003.lista')->with('data', $data);
    }
    
        public function listaCambio()
    {
        return View::make('seguimiento.ac.003.cambio.lista');
    }    
    
        public function listaCambioAe($id)
    {
            
        $data = tab_ac_ae::select(
            'tab_ac_ae.id',
            'nu_codigo',
            'de_nombre'
        )
        ->join('ac_seguimiento.tab_ac as t01', 'tab_ac_ae.id_tab_ac', '=', 't01.id')
        ->join('mantenimiento.tab_ac_ae_predefinida as t02', 'tab_ac_ae.id_tab_ac_ae_predefinida', '=', 't02.id')
        ->where('tab_ac_ae.id', '=', $id)
        ->first();
        
        return View::make('seguimiento.ac.003.cambio.listaAe')->with('data', $data);            
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
                inner join ac_seguimiento.tab_meta_financiera as t02 on (t.id = t02.id_tab_meta_fisica)
                where t02.in_enviado = false and t01.id_tab_ac =ac_seguimiento.tab_ac.id) as pend_enviar"),
                'nu_codigo',
                'de_ac',
                'de_lapso',
                'in_abierta',
                'in_003',
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
    
    public function storeListaCambio()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');
            $id_tab_tipo_periodo = Input::get('id_tab_tipo_periodo');

            $tab_forma_003 = tab_meta_financiera::join('ac_seguimiento.tab_meta_fisica as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't06.id')        
            ->join('ac_seguimiento.tab_ac_ae as t05', 't06.id_tab_ac_ae', '=', 't05.id')        
            ->join('ac_seguimiento.tab_ac as t01', 't05.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_ac_ae_predefinida as t07', 't05.id_tab_ac_ae_predefinida', '=', 't07.id')
            ->select(
                'tx_ejecutor_ac',
                't01.id as id_ac',    
                't02.in_activo',
                'id_tab_ac_ae',
                'nu_codigo',
                'de_ac',
                'de_lapso',
                't01.id_ejecutor',
                'de_nombre',
                't05.in_003' 
            )
            ->where('t01.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t01.in_activo', '=', true)
            ->where('in_enviado', '=', true)
                    ->groupBy('t01.id')
            ->groupBy('tx_ejecutor_ac')
                    ->groupBy('t02.in_activo')
                   ->groupBy('t05.in_003')
                    ->groupBy('id_tab_ac_ae')
                    ->groupBy('nu_codigo')
                    ->groupBy('de_ac')
                    ->groupBy('de_lapso')
                    ->groupBy('t01.id_ejecutor')
                    ->groupBy('de_nombre')
                    ;

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_forma_003->where('t01.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_forma_003->where('tx_ejecutor_ac', 'ILIKE', "%$variable%");
                }
                
                if($id_tab_tipo_periodo!="") {
                    $tab_forma_003->where('id_tab_tipo_periodo', '=', $id_tab_tipo_periodo);
                }                   

                $response['success']  = 'true';
                $response['total'] = $tab_forma_003->get()->count();
                $tab_forma_003->skip($start)->take($limit);
                $response['data']  = $tab_forma_003->orderby('t01.id_ejecutor', 'ASC')->orderby('nu_codigo', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_forma_003->get()->count();
                $tab_forma_003->skip($start)->take($limit);

                $response['data']  = $tab_forma_003->orderby('t01.id_ejecutor', 'ASC')->orderby('nu_codigo', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 500);
        }
    }    

    
    public function storeListaCambioAe()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');
            
            $tab_forma_003 = tab_meta_financiera::select(
                'ac_seguimiento.tab_meta_financiera.id',
                'id_tab_meta_fisica',
                'ac_seguimiento.tab_meta_financiera.id_tab_municipio_detalle',
                'ac_seguimiento.tab_meta_financiera.id_tab_parroquia_detalle',
                'mo_presupuesto',
                'co_partida',
                'id_tab_fuente_financiamiento',
                'ac_seguimiento.tab_meta_financiera.in_activo',
                'ac_seguimiento.tab_meta_financiera.in_cargado',
                'codigo',
                'nb_meta',
                'de_fuente_financiamiento',
                'nu_numero',
                'nu_original',
                'co_sector',
                'ac_seguimiento.tab_meta_financiera.id_tab_estatus',
                'de_estatus'
            )
             ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
             ->join('mantenimiento.tab_fuente_financiamiento as t02', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't02.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 't01.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')
             ->join('mantenimiento.tab_estatus as t08', 't08.id', '=', 'ac_seguimiento.tab_meta_financiera.id_tab_estatus')       
            ->where('t05.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('id_tab_ac_ae', '=', Input::get('ac_ae'))
            ->where('t05.in_activo', '=', true)
            ->where('in_enviado', '=', true)
            ->where('ac_seguimiento.tab_meta_financiera.in_cargado', '=', true);           

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_forma_003->where('t05.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_forma_003->where('nb_meta', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_forma_003->count();
                $tab_forma_003->skip($start)->take($limit);
                $response['data']  = $tab_forma_003->orderby('ac_seguimiento.tab_meta_financiera.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_forma_003->count();
                $tab_forma_003->skip($start)->take($limit);
                $response['data']  = $tab_forma_003->orderby('ac_seguimiento.tab_meta_financiera.id', 'ASC')->get()->toArray();
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

        return View::make('seguimiento.ac.003.detalle')->with('data', $data);
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

        return View::make('seguimiento.ac.003.datos.lista')->with('data', $data);
    }

    public function datosCambio($id)
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
            'codigo',
            'nb_meta',
            'de_fuente_financiamiento',
            'nu_numero',
            'nu_original',
            'co_sector',
            'mo_modificado_anual',
            'mo_actualizado_anual',
            'mo_comprometido',
            'mo_causado',
            'mo_pagado',
            't01.id_tab_origen',
            'ac_seguimiento.tab_meta_financiera.id_tab_estatus',
            DB::raw("to_char(t01.fecha_inicio, 'dd-mm-YYYY') as fecha_inicio"),
            DB::raw("to_char(t01.fecha_fin, 'dd-mm-YYYY') as fecha_fin")
        )
         ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
         ->join('mantenimiento.tab_fuente_financiamiento as t02', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't02.id')
         ->join('ac_seguimiento.tab_ac_ae as t03', 't01.id_tab_ac_ae', '=', 't03.id')
         ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
         ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
         ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
         ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')
        ->where('ac_seguimiento.tab_meta_financiera.id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.003.cambio.editar')->with('data', $data);
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
        return View::make('seguimiento.ac.003.actividad.lista')->with('data', $data);
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

            $tab_meta_financiera = tab_meta_financiera::select(
                'ac_seguimiento.tab_meta_financiera.id',
                'id_tab_meta_fisica',
                'ac_seguimiento.tab_meta_financiera.id_tab_municipio_detalle',
                'ac_seguimiento.tab_meta_financiera.id_tab_parroquia_detalle',
                'mo_presupuesto',
                'co_partida',
                'id_tab_fuente_financiamiento',
                'ac_seguimiento.tab_meta_financiera.in_activo',
                'ac_seguimiento.tab_meta_financiera.in_cargado',
                'codigo',
                'nb_meta',
                'de_fuente_financiamiento',
                'nu_numero',
                'nu_original',
                'co_sector',
                'in_enviado'
            )
             ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
             ->join('mantenimiento.tab_fuente_financiamiento as t02', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't02.id')
             ->join('ac_seguimiento.tab_ac_ae as t03', 't01.id_tab_ac_ae', '=', 't03.id')
             ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
             ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
             ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
             ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')
             ->where('id_tab_ac_ae', '=', Input::get('ac_ae'));

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_meta_financiera->where('nb_meta', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_meta_financiera->count();
                $tab_meta_financiera->skip($start)->take($limit);
                $response['data']  = $tab_meta_financiera->orderby('t01.id', 'ASC')->orderby('tab_meta_financiera.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_meta_financiera->count();
                $tab_meta_financiera->skip($start)->take($limit);
                $response['data']  = $tab_meta_financiera->orderby('t01.id', 'ASC')->orderby('tab_meta_financiera.id', 'ASC')->get()->toArray();
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
    public function editarActividad($id)
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
            'codigo',
            'nb_meta',
            'de_fuente_financiamiento',
            'nu_numero',
            'nu_original',
            'co_sector',
            'mo_modificado_anual',
            'mo_actualizado_anual',
            'mo_comprometido',
            'mo_causado',
            'mo_pagado',
            'mo_modificado',
            DB::raw('coalesce(mo_presupuesto,0) + coalesce(mo_modificado,0) as mo_presupuesto_nuevo'),
            't01.id_tab_origen',
            'ac_seguimiento.tab_meta_financiera.in_enviado',
            DB::raw("to_char(t01.fecha_inicio, 'dd-mm-YYYY') as fecha_inicio"),
            DB::raw("to_char(t01.fecha_fin, 'dd-mm-YYYY') as fecha_fin"),
            't08.id_tab_tipo_periodo'
        )
         ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
         ->join('mantenimiento.tab_fuente_financiamiento as t02', 'ac_seguimiento.tab_meta_financiera.id_tab_fuente_financiamiento', '=', 't02.id')
         ->join('ac_seguimiento.tab_ac_ae as t03', 't01.id_tab_ac_ae', '=', 't03.id')
         ->join('mantenimiento.tab_ac_ae_predefinida as t04', 't03.id_tab_ac_ae_predefinida', '=', 't04.id')
         ->join('ac_seguimiento.tab_ac as t05', 't03.id_tab_ac', '=', 't05.id')
         ->join('mantenimiento.tab_lapso as t08', 't05.id_tab_lapso', '=', 't08.id')
         ->join('mantenimiento.tab_ac_predefinida as t06', 't05.id_tab_ac_predefinida', '=', 't06.id')
         ->join('mantenimiento.tab_sectores as t07', 't05.id_tab_sectores', '=', 't07.id')
        ->where('ac_seguimiento.tab_meta_financiera.id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.003.actividad.editar')->with('data', $data);
    }
    public function editarAc($id)
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
            'nu_po_beneficiada',
            'nu_em_generado', 
            'tx_pr_programado as producto_programado',
            'tx_re_esperado',
            'in_activo',
            'id_tab_lapso',
            'in_bloquear_001',
            'de_observacion_001',
            'de_observacion_003'
        )
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.003.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_meta_financiera::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                
                
                $data1 = tab_meta_fisica::select(
                't02.nu_codigo','t02.id_tab_ejercicio_fiscal','ac_seguimiento.tab_meta_fisica.codigo','t01.id_tab_ac_ae_predefinida','t03.co_partida','t03.id_tab_fuente_financiamiento','t02.id_tab_lapso'
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_meta_fisica.id_tab_ac_ae', '=', 't01.id')
                ->join('ac_seguimiento.tab_ac as t02', 't01.id_tab_ac', '=', 't02.id')
                ->join('ac_seguimiento.tab_meta_financiera as t03', 't03.id_tab_meta_fisica', '=', 'ac_seguimiento.tab_meta_fisica.id')
                ->where('t03.id', '=', $id)
                ->first();
                
                
                
                $data2 = tab_ac::select(
                 DB::raw("coalesce(sum(mo_comprometido),0) as mo_comprometido"),DB::raw("coalesce(sum(mo_causado),0) as mo_causado"),DB::raw("coalesce(sum(mo_pagado),0) as mo_pagado"),DB::raw("coalesce(sum(mo_modificado_anual),0) as mo_modificado_anual")
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_ac.id', '=', 't01.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t02', 't01.id', '=', 't02.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t03', 't03.id_tab_meta_fisica', '=', 't02.id')
                ->join('mantenimiento.tab_lapso as t04', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't04.id')
                ->where('ac_seguimiento.tab_ac.nu_codigo', '=', $data1->nu_codigo)
                ->where('t02.codigo', '=', $data1->codigo)
                ->where('t01.id_tab_ac_ae_predefinida', '=', $data1->id_tab_ac_ae_predefinida)
                ->where('t03.co_partida', '=', $data1->co_partida)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
                ->where('t03.id_tab_fuente_financiamiento', '=', $data1->id_tab_fuente_financiamiento)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $data1->id_tab_ejercicio_fiscal)
                ->whereNotIn('t03.id', [$id])
                ->first();  
               
                
                $data3 = tab_ac::select(
                DB::raw("coalesce(sum(mo_presupuesto),0) as mo_presupuesto")
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_ac.id', '=', 't01.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t02', 't01.id', '=', 't02.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t03', 't03.id_tab_meta_fisica', '=', 't02.id')
                ->join('mantenimiento.tab_lapso as t04', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't04.id')
                ->where('ac_seguimiento.tab_ac.nu_codigo', '=', $data1->nu_codigo)
                ->where('t02.codigo', '=', $data1->codigo)
                ->where('t01.id_tab_ac_ae_predefinida', '=', $data1->id_tab_ac_ae_predefinida)
                ->where('t03.co_partida', '=', $data1->co_partida)
                ->where('t03.id_tab_fuente_financiamiento', '=', $data1->id_tab_fuente_financiamiento)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $data1->id_tab_ejercicio_fiscal)
                ->where('ac_seguimiento.tab_ac.id_tab_lapso', '=', $data1->id_tab_lapso)
                ->first();                
                

                
                if((round($data2->mo_comprometido+Input::get("comprometido"),2))>round($data3->mo_presupuesto+$data2->mo_modificado_anual+Input::get("modificado_anual"),2)){

                return Response::json(array(
                  'success' => false,
                  'msg' => 'La suma del presupuesto comprometido excede el monto del presupuesto actualizado, verifique!'
                ));
                
                } 
                
                if((round($data2->mo_causado+Input::get("causado"),2))>(round($data2->mo_comprometido+Input::get("comprometido"),2))){
                    
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La suma del presupuesto causado excede el monto del presupuesto comprometido, verifique!'
                ));
                
                }

                if((round($data2->mo_pagado+Input::get("pagado"),2))>(round($data2->mo_causado+Input::get("causado"),2))){
                
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La suma del presupuesto pagado excede el monto del presupuesto causado, verifique!'
                ));
                
                }                
                
                $tabla = tab_meta_financiera::find($id);
                $tabla->mo_modificado_anual = Input::get("modificado_anual");
                $tabla->mo_actualizado_anual = Input::get("actualizado_anual");
                $tabla->mo_comprometido = Input::get("comprometido");
                $tabla->mo_causado = Input::get("causado");
                $tabla->mo_pagado = Input::get("pagado");
                $tabla->in_cargado = true;
                $tabla->id_tab_estatus = 5;
                $tabla->save();
                
                $data = tab_meta_fisica::join('ac_seguimiento.tab_ac_ae as t05', 'tab_meta_fisica.id_tab_ac_ae', '=', 't05.id')
                ->join('ac_seguimiento.tab_ac as t01', 't05.id_tab_ac', '=', 't01.id')
                ->select(
                    't01.id',
                    'id_tab_ac_ae'    
                )
                ->where('tab_meta_fisica.id', '=', $tabla->id_tab_meta_fisica)
                ->first();        
                
                $cant = tab_meta_fisica::join('ac_seguimiento.tab_meta_financiera as t01', 't01.id_tab_meta_fisica', '=', 'tab_meta_fisica.id')
                ->where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
                ->where('t01.in_cargado', '=', false)
                ->count(); 
                
//                        var_dump($cant);
//        exit();
                
                if($cant==0){
                    
//                $tabla = tab_ac::find($data->id);
//                $tabla->in_003 = true;
//                $tabla->save();
                
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
                $validator = Validator::make(Input::all(), tab_meta_financiera::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_meta_financiera();
                $tabla->mo_modificado_anual = Input::get("modificado_anual");
                $tabla->mo_actualizado_anual = Input::get("actualizado_anual");
                $tabla->mo_comprometido = Input::get("comprometido");
                $tabla->mo_causado = Input::get("causado");
                $tabla->mo_pagado = Input::get("pagado");
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

    public function guardarEditarAc($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                
                $tabla = tab_ac::find($id);
                $tabla->de_observacion_003 = str_replace('"', '', Input::get("observaciones"));
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

        }
    }
    
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
                
                
             $tab_meta_financiera = tab_meta_financiera::where('id_tab_meta_fisica', '=', Input::get("meta_fisica"))
             ->where('co_partida', '=', Input::get("partida"))
             ->where('id_tab_fuente_financiamiento', '=', Input::get("fuente_financiamiento"))
             ->get();         
             if($tab_meta_financiera->count()>0){
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La actividad ya tiene una meta financiera con la partida y fuente seleccionada, verifique!'
                ));
             }                
                
                $data1 = tab_meta_fisica::select(
                    't01.mo_ae','t01.id'
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_meta_fisica.id_tab_ac_ae', '=', 't01.id')
                ->where('ac_seguimiento.tab_meta_fisica.id', '=', Input::get("meta_fisica"))
                ->first();   
                
                $data2 = tab_meta_fisica::select(
                 DB::raw("coalesce(sum(mo_presupuesto),0) + coalesce(sum(mo_modificado_anual),0) + coalesce(sum(mo_modificado),0) as mo_presupuesto")
                )
                ->join('ac_seguimiento.tab_meta_financiera as t02', 'ac_seguimiento.tab_meta_fisica.id', '=', 't02.id_tab_meta_fisica')
                ->where('id_tab_ac_ae', '=', $data1->id)
                ->first();
                               
                $mo_ae = $data1->mo_ae;
                $mo_presupuesto = $data2->mo_presupuesto;

                $mo_actividad = Input::get("presupuesto");
                
                $mo_actividad_nuevo = ($mo_presupuesto+$mo_actividad);
                
                
                if(round($mo_actividad_nuevo,2)>round($mo_ae,2)){
                
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La suma de las actividades excede el monto de la accion especifica, verifique!'
                ));
                
                }                
                
                $tabla = new tab_meta_financiera();
                $tabla->id_tab_meta_fisica = Input::get("meta_fisica");
                $tabla->id_tab_municipio_detalle = Input::get("municipio");
                $tabla->id_tab_parroquia_detalle = Input::get("parroquia");
                $tabla->mo_presupuesto = 0;
                $tabla->mo_modificado_anual = Input::get("presupuesto");             
                $tabla->co_partida = Input::get("partida");
                $tabla->id_tab_fuente_financiamiento = Input::get("fuente_financiamiento");
                $tabla->id_tab_origen = 2;
                $tabla->in_cargado = false;
                $tabla->in_activo = true;
                $tabla->save();
                
                $data4 = tab_meta_fisica::select(
                 DB::raw("coalesce(sum(mo_presupuesto),0) + coalesce(sum(mo_modificado_anual),0) + coalesce(sum(mo_modificado),0) as mo_fondo"),'id_tab_fuente_financiamiento'
                )
                ->join('ac_seguimiento.tab_meta_financiera as t02', 'ac_seguimiento.tab_meta_fisica.id', '=', 't02.id_tab_meta_fisica')
                ->where('id_tab_ac_ae', '=', $data1->id)
                ->groupBy('id_tab_fuente_financiamiento')
                ->get();
                

                
                foreach($data4 as $item) {
                    
                $data3 = tab_ac_ae_fuente::select(
                 DB::raw("coalesce(sum(mo_fondo),0) as mo_fondo"),'de_fuente_financiamiento'
                )
                ->join('mantenimiento.tab_fuente_financiamiento as t01', 'tab_ac_ae_fuente.id_tab_tipo_fondo', '=', 't01.id_tab_tipo_fondo')
                ->where('id_tab_ac_ae', '=', $data1->id)
                ->where('t01.id', '=', $item->id_tab_fuente_financiamiento)
                ->groupBy('de_fuente_financiamiento')
                ->first();  

                if($data3){

                if($item->mo_fondo>$data3->mo_fondo){
                
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La suma del monto por la fuente '.$data3->de_fuente_financiamiento.' es mayor que el cargado en la accion especifica, verifique!'
                ));
                
                } 

                }                
                else{
                $data6 = tab_fuente_financiamiento::where('id', '=', $item->id_tab_fuente_financiamiento)
                ->first();
                
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La fuente '.$data6->de_fuente_financiamiento.' no se encuentra en la lista de fuentes de la Ae, verifique!'
                ));                
                
                }
                }                

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
    
    public function negar($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_meta_financiera::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_meta_financiera::find($id);
                $tabla->in_cargado = false; 
                $tabla->in_enviado = false; 
                $tabla->id_tab_estatus = 7;
                $tabla->save();

                DB::commit();
                return Response::json(array(
                  'success' => true,
                  'msg' => 'Solicitud Negada con Exito!'
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
    
    
    public function aprobar($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_meta_financiera::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }

                
                $tabla_meta = tab_meta_financiera::find($id);
                $tabla_meta->id_tab_estatus = 6;
                $tabla_meta->save();                
                
                    $data = tab_meta_financiera::join('ac_seguimiento.tab_meta_fisica as t06', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't06.id')        
                ->join('ac_seguimiento.tab_ac_ae as t05', 't06.id_tab_ac_ae', '=', 't05.id')
                ->select(
                    't05.id_tab_ac',
                    't06.id_tab_ac_ae'
                )
                ->where('ac_seguimiento.tab_meta_financiera.id', '=', $id)
                ->first();         
                
                
                $cant = tab_ac_ae::join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_ac_ae.id', '=', 't01.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t02', 't01.id', '=', 't02.id_tab_meta_fisica')         
                ->where('ac_seguimiento.tab_ac_ae.id', '=', $data->id_tab_ac_ae)
                ->whereNotIn('t02.id_tab_estatus', [6])
                ->count();
                
//        var_dump($cant);
//        exit();
                if($cant==0){
                
                $tabla_ae = tab_ac_ae::find($data->id_tab_ac_ae);
                $tabla_ae->in_003 = true;
                $tabla_ae->save();                
                
                }
                
                $cant1 = tab_ac::join('ac_seguimiento.tab_ac_ae as t03', 'ac_seguimiento.tab_ac.id', '=', 't03.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t01', 't03.id', '=', 't01.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t02', 't01.id', '=', 't02.id_tab_meta_fisica')         
                ->where('tab_ac.id', '=', $data->id_tab_ac)
                ->whereNotIn('t02.id_tab_estatus', [6])
                ->count(); 
                
                if($cant1==0){
                
                $tabla_ac = tab_ac::find($data->id_tab_ac);
                $tabla_ac->in_003 = true;
                $tabla_ac->save();                
                
                }                
        
                DB::commit();
                return Response::json(array(
                  'success' => true,
                  'msg' => 'Datos aprobados con Exito!'
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
    
    public function cargar()
    {
        DB::beginTransaction();
        try {
            
                $cant = tab_ac_ae::join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_ac_ae.id', '=', 't01.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t02', 't01.id', '=', 't02.id_tab_meta_fisica')         
                ->where('tab_ac_ae.id', '=', Input::get("id"))
                ->where('t02.in_cargado','=', false)
                ->count();            
                
                if($cant>0){
             $response['success']  = 'true';
            $response['msg']  = 'Tiene Actividades Pendientes por cargar, verifique!';
            return Response::json($response, 200);                   
                }else{
                    
                $cant1 = tab_ac_ae::join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_ac_ae.id', '=', 't01.id_tab_ac_ae')
                ->join('ac_seguimiento.tab_meta_financiera as t02', 't01.id', '=', 't02.id_tab_meta_fisica')         
                ->where('tab_ac_ae.id', '=', Input::get("id"))
                ->where('t02.in_enviado','=', false)
                ->count();                                     
                 
             if($cant1>0){  
                 
                 
                $data1 = tab_ac_ae::select(
                    'mo_ae'
                )
                ->where('id', '=', Input::get("id"))
                ->first();   
                
                $data2 = tab_meta_fisica::select(
                 DB::raw("coalesce(sum(mo_presupuesto),0) + coalesce(sum(mo_modificado_anual),0) + coalesce(sum(mo_modificado),0) as mo_presupuesto")
                )
                ->join('ac_seguimiento.tab_meta_financiera as t02', 'ac_seguimiento.tab_meta_fisica.id', '=', 't02.id_tab_meta_fisica')
                ->where('id_tab_ac_ae', '=', Input::get("id"))
                ->first();
                               

                $mo_ae = $data1->mo_ae;
                $mo_presupuesto = $data2->mo_presupuesto;
                

                
                if($mo_presupuesto!=$mo_ae){
            $response['success']  = 'true';
            $response['msg']  = 'La suma de las actividades no es igual al monto de la accion especifica, verifique!';
            return Response::json($response, 200); 
                
                }
                
                $data4 = tab_meta_fisica::select(
                 DB::raw("coalesce(sum(mo_presupuesto),0) + coalesce(sum(mo_modificado_anual),0) + coalesce(sum(mo_modificado),0) as mo_fondo"),'id_tab_fuente_financiamiento'
                )
                ->join('ac_seguimiento.tab_meta_financiera as t02', 'ac_seguimiento.tab_meta_fisica.id', '=', 't02.id_tab_meta_fisica')
                ->where('id_tab_ac_ae', '=', Input::get("id"))
                ->groupBy('id_tab_fuente_financiamiento')
                ->get();
                

                
                foreach($data4 as $item) {
                    
                $data3 = tab_ac_ae_fuente::select(
                 DB::raw("coalesce(sum(mo_fondo),0) as mo_fondo"),'de_fuente_financiamiento'
                )
                ->join('mantenimiento.tab_fuente_financiamiento as t01', 'tab_ac_ae_fuente.id_tab_tipo_fondo', '=', 't01.id_tab_tipo_fondo')
                ->where('id_tab_ac_ae', '=', Input::get("id"))
                ->where('t01.id', '=', $item->id_tab_fuente_financiamiento)
                ->groupBy('de_fuente_financiamiento')
                ->first();  
                
     

                if($item->mo_fondo!=$data3->mo_fondo){
                
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La suma del monto por la fuente '.$data3->de_fuente_financiamiento.' es distinto al cargado en la accion especifica, verifique!'
                ));
                
                }                 
                 
                }
              
            $data = tab_meta_financiera::select(
                'ac_seguimiento.tab_meta_financiera.id'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->where('id_tab_ac_ae', '=', Input::get("id"))
            ->get();                        
                

                
                foreach ($data as $lista){   

            $tabla = tab_meta_financiera::find($lista->id);
            $tabla->in_enviado = true;
            $tabla->save();  
                      
                } 
                
            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Actividades enviadas con Exito!';
            return Response::json($response, 200);                 
                 
             }else{
                    
              $response['success']  = 'true';
            $response['msg']  = 'No Tiene Actividades Pendientes por validar!';
            return Response::json($response, 200); 
            
             }
                }
            


        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 200);
        }
    }    
    
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

        return View::make('seguimiento.ac.003.actividad.nuevo')
        ->with('data', $data)
        ->with('fecha', $limite);
    }

    public function editarFinanciera($id)
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
            'nu_meta_modificada',
            DB::raw("tx_prog_anual::numeric +  nu_meta_modificada as nu_meta_actualizada"),
            'nu_obtenido',
            'nu_corte',
            'id_tab_municipio_detalle',
            'id_tab_parroquia_detalle',
            'in_cargado',
            DB::raw("EXTRACT(year FROM fecha_inicio::DATE) as id_tab_ejercicio_fiscal")
        )
        ->where('id', '=', $id)
        ->first();

        $fechaI = '01-01-'.($data->id_tab_ejercicio_fiscal);
        $fechaF = '31-12-'.($data->id_tab_ejercicio_fiscal);

        $limite = json_encode(array('fe_ini' => $fechaI, 'fe_fin' => $fechaF ));

        //$data = json_encode(array_merge( $data->toArray(), $limite ));

        return View::make('seguimiento.ac.003.actividad.editarFinanciera')
        ->with('data', $data)
        ->with('fecha', $limite);
    }    

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
                'de_fuente_financiamiento',
                'id_tab_origen'
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
    
    public function nuevoFinanciera($id)
    {

        $data = tab_meta_fisica::select('ac_seguimiento.tab_meta_fisica.id as id_tab_meta_fisica', 'id_tab_ac_ae','id_tab_ac_ae_predefinida')
        ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_meta_fisica.id_tab_ac_ae', '=', 't01.id')         
        ->where('ac_seguimiento.tab_meta_fisica.id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.003.actividad.nuevoFinanciera')
        ->with('data', $data);
    }    
    
}
