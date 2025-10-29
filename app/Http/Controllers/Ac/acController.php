<?php

namespace matriz\Http\Controllers\Ac;

//*******agregar esta linea******//
use matriz\Models\Ac\tab_ac;
use matriz\Models\Ac\tab_ac_ae_partida;
use matriz\Models\Mantenimiento\tab_ejecutores;
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

class acController extends Controller
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
    public function storeLista()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 10);
            $variable = Input::get('variable');

            $tab_ac = $this->tab_ac
            ->join('mantenimiento.tab_ejecutores as t01', 'public.t46_acciones_centralizadas.id_ejecutor', '=', 't01.id_ejecutor')
            ->join('mantenimiento.tab_estatus as t02', 'public.t46_acciones_centralizadas.id_estatus', '=', 't02.id')
            ->join('mantenimiento.tab_ac_predefinida as t03', 'public.t46_acciones_centralizadas.id_accion', '=', 't03.id')
            ->select(
                'public.t46_acciones_centralizadas.id',
                'de_nombre as nombre',
                'monto',
                'tx_ejecutor',
                'de_estatus as tx_estatus',
                DB::raw("'PG' || public.t46_acciones_centralizadas.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo"),
                DB::raw("coalesce(monto_calc, 0) as monto_calc"),
                DB::raw("coalesce(null, id_estatus = 3) as reabrir"),
                DB::raw("coalesce(null, id_estatus = 1) as eliminar")
            )
            ->where('edo_reg', '=', true)
            ->where('id_ejercicio', '=', Session::get('ejercicio'));

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_ac->where('public.t46_acciones_centralizadas.id_ejecutor', '=', Session::get('ejecutor'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    //$tab_ac->where('tx_ejecutor', 'ILIKE', "%$variable%");
                    $tab_ac->whereRaw("tx_ejecutor||de_nombre||'AC' || public.t46_acciones_centralizadas.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') ILIKE '%".$variable."%'");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ac->count();
                $tab_ac->skip($start)->take($limit);
                $response['data']  = $tab_ac->orderby('public.t46_acciones_centralizadas.id_ejecutor', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ac->count();
                $tab_ac->skip($start)->take($limit);
                $response['data']  = $tab_ac->orderby('public.t46_acciones_centralizadas.id_ejecutor', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function guardar($id = null)
    {
        $id = Input::get("id");
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_ac::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }

                $tabla = tab_ac::find($id);
                $tabla->id_accion = Input::get("id_accion");
                $tabla->id_subsector = Input::get("id_co_sector");
                $tabla->id_estatus = 1;
                $tabla->sit_presupuesto = Input::get("co_situacion_presupuestaria");
                $tabla->descripcion = str_replace('"', '', Input::get("descripcion"));
                $tabla->monto = Input::get("monto");
                //$tabla->monto_calc = 0;
                $tabla->fecha_inicio = Input::get("fecha_inicio");
                $tabla->fecha_fin = Input::get("fecha_fin");
                $tabla->inst_mision = str_replace('"', '', Input::get("inst_mision"));
                $tabla->inst_vision = str_replace('"', '', Input::get("inst_vision"));
                $tabla->inst_objetivos = str_replace('"', '', Input::get("inst_objetivos"));
                $tabla->nu_po_beneficiar = Input::get("nu_po_beneficiar");
                $tabla->nu_em_previsto = Input::get("nu_em_previsto");
                $tabla->tx_re_esperado = str_replace('"', '', Input::get("tx_re_esperado"));
                $tabla->tx_pr_objetivo = str_replace('"', '', Input::get("tx_pr_objetivo"));
                $tabla->edo_reg = 'TRUE';
                $tabla->save();

                DB::commit();
                return Response::json(array(
                  'success' => true,
                  'msg' => 'Programa Editado con Exito!'
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

                // 5.1 or newer
                Validator::extend('composite_unique', function ($attribute, $value, $parameters, $validator) {
                    // remove first parameter and assume it is the table name
                    $table = array_shift($parameters);
                    // start building the conditions
                    $fields = [ $attribute => $value ]; // current field, company_code in your case
                    // iterates over the other parameters and build the conditions for all the required fields
                    while ($field = array_shift($parameters)) {
                        $fields[ $field ] = \Request::get($field);
                    }
                    // query the table with all the conditions
                    $result = DB::table($table)->select(DB::raw(1))->where($fields)->first();

                    return empty($result); // edited here
                }, ':attribute ya esta siendo Utilizada por este Ejecutor.');

                $validator = Validator::make(Input::all(), tab_ac::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_ac();
                if (Session::get('rol') > 2) { //es local
                    $tabla->id_ejecutor = Session::get('ejecutor');
                    
                    $data_ejecutor = tab_ejecutores::select('tx_ejecutor')
                    ->where('id_ejecutor', '=', Session::get('ejecutor'))
                    ->first();
                    
                } else {
                    $tabla->id_ejecutor = Input::get("id_ejecutor");
                    
                    $data_ejecutor = tab_ejecutores::select('tx_ejecutor')
                    ->where('id_ejecutor', '=', Input::get("id_ejecutor"))
                    ->first();                    
                }
                $tabla->id_ejercicio = Session::get('ejercicio');
                $tabla->id_accion = Input::get("id_accion");
                $tabla->id_subsector = Input::get("id_co_sector");
                $tabla->id_estatus = 1;
                $tabla->sit_presupuesto = Input::get("co_situacion_presupuestaria");
                $tabla->descripcion = str_replace('"', '', Input::get("descripcion"));
                $tabla->monto = Input::get("monto");
                $tabla->monto_calc = 0;
                $tabla->fecha_inicio = Input::get("fecha_inicio");
                $tabla->fecha_fin = Input::get("fecha_fin");
                $tabla->inst_mision = str_replace('"', '', Input::get("inst_mision"));
                $tabla->inst_vision = str_replace('"', '', Input::get("inst_vision"));
                $tabla->inst_objetivos = str_replace('"', '', Input::get("inst_objetivos"));
                $tabla->nu_po_beneficiar = Input::get("nu_po_beneficiar");
                $tabla->nu_em_previsto = Input::get("nu_em_previsto");
                $tabla->tx_re_esperado = str_replace('"', '', Input::get("tx_re_esperado"));
                $tabla->tx_pr_objetivo = str_replace('"', '', Input::get("tx_pr_objetivo"));
                $tabla->tx_ejecutor_poa = str_replace('"', '', $data_ejecutor->tx_ejecutor);
                $tabla->edo_reg = 'TRUE';
                $tabla->save();

                DB::commit();

                $consulta_ac = tab_ac::select(DB::raw("'PG' || id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo"))
                ->where('id', '=', $tabla->id)
                ->first();

                return Response::json(array(
                  'success' => true,
                  'msg' => 'El Programa ha sido almacenado con el Código:'.$consulta_ac->codigo,
                  'data' => array('id' => $tabla->id, 'codigo' => $consulta_ac->codigo)
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
    public function cerrar(Request $request)
    {
        DB::beginTransaction();
        try {

            $validar_ae = tab_ac_ae_partida::join('t46_acciones_centralizadas as t02', 't02.id', '=', 't54_ac_ae_partidas.id_accion_centralizada')
            ->select('id_accion_centralizada', DB::raw("t02.monto as mo_ac"), DB::raw("sum(t54_ac_ae_partidas.monto) as mo_partida"))
            ->where('id_accion_centralizada', '=', $request->id_accion_centralizada)
            ->where('t54_ac_ae_partidas.edo_reg', '=', true)
            ->groupBy(DB::raw('1,2'))
            ->first();

            if($validar_ae->mo_ac == $validar_ae->mo_partida) {
                $in_valido = 1;
            } else {
                $in_valido = 0;
            }

            $mensajes = array(
              'valido.in'=>'El monto Cargado No Coincide con el monto de la AC. <br>Monto Accion Centralizada.: <span style="color:green"><b>'.number_format($validar_ae->mo_ac, 2, ',', '.').'</b></span>'.'<br>Monto Cargado Partidas: <span style="color:red"><b>'.number_format($validar_ae->mo_partida, 2, ',', '.').'</b></span>'.'<br>Diferencia: <b>'.number_format(($validar_ae->mo_ac - $validar_ae->mo_partida), 2, ',', '.').'</b>'
            );

            $datos = array(
              'id' => $request->id_accion_centralizada,
              'valido' => $in_valido
            );

            $validador = Validator::make($datos, tab_ac::$cerrarAc, $mensajes);

            $tabla = tab_ac::find($request->id_accion_centralizada);
            $tabla->id_estatus = 3;
            $tabla->save();

            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'La Acción Centralizada se ha cerrado!';
            return Response::json($response, 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 200);
        }
    }
}
