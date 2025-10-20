<?php

namespace matriz\Http\Controllers\AcSeguimiento;

//*******agregar esta linea******//
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

class ejecucionController extends Controller
{
    protected $tab_meta_financiera;

    public function __construct(tab_meta_financiera $tab_meta_financiera)
    {
        $this->middleware('auth');
        $this->tab_meta_financiera = $tab_meta_financiera;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista($id)
    {
                $lapso = tab_lapso::where('id', '=', $id)
        ->first();
        $data = json_encode(array("id_ejecutor" => Session::get('ejecutor')));
        return View::make('seguimiento.ac.ejecucion.lista')->with('data', $data)->with('lapso', $lapso);
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
            $variable = Input::get('id_ejecutor');
            $lapso = Input::get('id_tab_lapso');
            
            
    
             $tab_lapso = tab_lapso::where('id', '<=', $lapso)
             ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->get();  
             
              $i =  $tab_lapso->count();
              
//              var_dump($i);
//              exit();

            $tab_meta_financiera = tab_meta_financiera::select(
                'tx_nombre',
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'),
                DB::raw('sum(coalesce(mo_comprometido,0)) as mo_comprometido'),
                DB::raw('sum(coalesce(mo_causado,0)) as mo_causado'),
                DB::raw('sum(coalesce(mo_pagado,0)) as mo_pagado'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0)) -  sum(coalesce(mo_pagado,0)) as mo_financiera'),
                DB::raw('sum(coalesce(mo_presupuesto,0))/'.$i.' + sum(coalesce(mo_modificado_anual,0))-  sum(coalesce(mo_comprometido,0)) as mo_presupuestaria'),                    
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            //->join('mantenimiento.tab_partidas as t04', 't04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
            ->join('mantenimiento.tab_partidas as t04', function ($j) {
                $j->on('t04.co_partida', '=', 'ac_seguimiento.tab_meta_financiera.co_partida')
                  ->on('t04.id_tab_ejercicio_fiscal', '=', 't03.id_tab_ejercicio_fiscal');
            })
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t03.id_tab_lapso', '<=', $lapso)
            ->where('t03.in_activo', '=', true)
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')
            ->groupBy('tx_nombre');

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_meta_financiera->where('t03.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_meta_financiera->where('t03.id_ejecutor', '=', $variable);
                }

                $response['success']  = 'true';
                $response['total'] = $tab_meta_financiera->count();
                $tab_meta_financiera->skip($start)->take($limit);
                $response['data']  = $tab_meta_financiera->orderby('ac_seguimiento.tab_meta_financiera.co_partida', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_meta_financiera->count();
                $tab_meta_financiera->skip($start)->take($limit);
                $response['data']  = $tab_meta_financiera->orderby('ac_seguimiento.tab_meta_financiera.co_partida', 'ASC')->get()->toArray();
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
        $data = tab_meta_financiera::select(
                DB::raw('sum(coalesce(mo_presupuesto,0)) as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0)) + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'),
                DB::raw('sum(coalesce(mo_comprometido,0)) as mo_comprometido'),
                DB::raw('sum(coalesce(mo_causado,0)) as mo_causado'),
                DB::raw('sum(coalesce(mo_pagado,0)) as mo_pagado'),
            'ac_seguimiento.tab_meta_financiera.co_partida'
        )
        ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
        ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
        ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
        ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
        ->where('ac_seguimiento.tab_meta_financiera.in_activo', '=', true)
        ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', Input::get('codigo'))
        ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')->first();

        $rol_planificador = array(3, 8);
        if (in_array(Session::get('rol'), $rol_planificador)) {
            $data = tab_meta_financiera::select(
                DB::raw('sum(coalesce(mo_presupuesto,0)) as mo_presupuesto'),
                DB::raw('sum(coalesce(mo_modificado_anual,0)) as mo_modificado_anual'),
                DB::raw('sum(coalesce(mo_presupuesto,0)) + sum(coalesce(mo_modificado_anual,0)) as mo_actualizado_anual'),
                DB::raw('sum(coalesce(mo_comprometido,0)) as mo_comprometido'),
                DB::raw('sum(coalesce(mo_causado,0)) as mo_causado'),
                DB::raw('sum(coalesce(mo_pagado,0)) as mo_pagado'),
                'ac_seguimiento.tab_meta_financiera.co_partida'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_meta_financiera.id_tab_meta_fisica', '=', 't01.id')
            ->join('ac_seguimiento.tab_ac_ae as t02', 't01.id_tab_ac_ae', '=', 't02.id')
            ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
            ->where('t03.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('ac_seguimiento.tab_meta_financiera.in_activo', '=', true)
            ->where('ac_seguimiento.tab_meta_financiera.co_partida', '=', Input::get('codigo'))
            ->where('t03.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'))
            ->groupBy('ac_seguimiento.tab_meta_financiera.co_partida')->first();
        }

        return View::make('seguimiento.ac.ejecucion.detalle')->with('data', $data);
    }

}
