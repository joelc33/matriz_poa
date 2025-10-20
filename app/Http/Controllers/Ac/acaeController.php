<?php

namespace matriz\Http\Controllers\Ac;

//*******agregar esta linea******//
use matriz\Models\Ac\tab_ac_ae;
use View;
use Validator;
use Input;
use Response;
use DB;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class acaeController extends Controller
{
    protected $tab_ac_ae;

    public function __construct(tab_ac_ae $tab_ac_ae)
    {
        $this->middleware('auth');
        $this->tab_ac_ae = $tab_ac_ae;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('ac.ae.lista');
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

            $tab_ac_ae = $this->tab_ac_ae
            ->join('mantenimiento.tab_ejecutores as t01', 'public.t47_ac_accion_especifica.id_ejecutor', '=', 't01.id_ejecutor')
            ->join('mantenimiento.tab_ac_ae_predefinida as t02', 'public.t47_ac_accion_especifica.id_accion', '=', 't02.id')
            ->join('mantenimiento.tab_unidad_medida as t03', 'public.t47_ac_accion_especifica.id_unidad_medida', '=', 't03.id')
            ->select(
                'id_accion_centralizada',
                'id_accion',
                'public.t47_ac_accion_especifica.id_ejecutor',
                'bien_servicio',
                'id_unidad_medida',
                'meta',
                'monto',
                'monto_calc',
                'edo_reg',
                'tx_ejecutor',
                'nu_numero as numero',
                'objetivo_institucional',
                'de_nombre as nombre',
                'de_unidad_medida as tx_unidades_medida',
                DB::raw("to_char(public.t47_ac_accion_especifica.fecha_inicio, 'dd-mm-YYYY') as fecha_inicio"),
                DB::raw("to_char(public.t47_ac_accion_especifica.fecha_fin, 'dd-mm-YYYY') as fecha_fin"),
                DB::raw("(SELECT count(id_accion) FROM public.t54_ac_ae_partidas WHERE id_accion_centralizada = public.t47_ac_accion_especifica.id_accion_centralizada AND id_accion = public.t47_ac_accion_especifica.id_accion) as npartidas")
            )
             ->where('id_accion_centralizada', '=', Input::get('id'))
             ->where('public.t47_ac_accion_especifica.edo_reg', '=', true);

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ac_ae->where('de_aplicacion', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ac_ae->count();
                $tab_ac_ae->skip($start)->take($limit);
                $response['data']  = $tab_ac_ae->orderby('id_accion', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ac_ae->count();
                $tab_ac_ae->skip($start)->take($limit);
                $response['data']  = $tab_ac_ae->orderby('id_accion', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 200);
        }
    }
}
