<?php

namespace matriz\Http\Controllers\Mantenimiento;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_ejercicio_fiscal;
use matriz\Models\Ac\tab_ac;
use matriz\Models\Ac\tab_ac_ae_partida;
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

class ejerciciofiscalController extends Controller
{
    protected $tab_ejercicio_fiscal;

    public function __construct(tab_ejercicio_fiscal $tab_ejercicio_fiscal, tab_ac $tab_ac)
    {
        $this->middleware('auth');
        $this->tab_ejercicio_fiscal = $tab_ejercicio_fiscal;
        $this->tab_ac = $tab_ac;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('mantenimiento.ejercicio.lista');
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

            $tab_ejercicio_fiscal = $this->tab_ejercicio_fiscal
            ->select('id', 'in_activo');

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_ejercicio_fiscal->where('id', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_ejercicio_fiscal->count();
                $tab_ejercicio_fiscal->skip($start)->take($limit);
                $response['data']  = $tab_ejercicio_fiscal->orderby('id', 'DESC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ejercicio_fiscal->count();
                $tab_ejercicio_fiscal->skip($start)->take($limit);
                $response['data']  = $tab_ejercicio_fiscal->orderby('id', 'DESC')->get()->toArray();
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
        $ejercicio = Session::get('ejercicio')+1;
        $data = json_encode(array("nu_anio" => $ejercicio));
        return View::make('mantenimiento.ejercicio.editar')
        ->with('data', $data)
        ->with('ejercicio', $ejercicio);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_ejercicio_fiscal::select('id', 'in_activo')
        ->where('id', '=', $id)
        ->first();
        return View::make('mantenimiento.ejercicio.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_ejercicio_fiscal::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_ejercicio_fiscal::find($id);
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
                $validator = Validator::make(Input::all(), tab_ejercicio_fiscal::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_ejercicio_fiscal();
                $tabla->id = Input::get("periodo");
                $tabla->in_activo = 'FALSE';
                $tabla->save();

                DB::commit();
                return Response::json(array(
                  'success' => true,
                  'msg' => 'Ejercicio creado con Exito!'
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
    public function habilitar()
    {
        DB::beginTransaction();
        try {
            $tabla = tab_ejercicio_fiscal::find(Input::get("periodo"));
            $tabla->in_activo = 'TRUE';
            $tabla->save();
            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Periodo Habilitado con Exito!';
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
    public function cerrar()
    {
        DB::beginTransaction();
        try {

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
                DB::raw("'AC' || public.t46_acciones_centralizadas.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo"),
                DB::raw("coalesce(monto_calc, 0) as monto_calc"),
                DB::raw("coalesce(null, id_estatus = 3) as reabrir"),
                DB::raw("coalesce(null, id_estatus = 1) as eliminar")
            )
            ->where('edo_reg', '=', true)
            ->where('id_ejercicio', '=', Session::get('ejercicio'))
            ->orderby('id', 'DESC')->get()->toArray();

            foreach ($tab_ac as $key => $value) {
                $this->cerrarAc($value['id']);
            }

            $tabla = tab_ejercicio_fiscal::find(Input::get("periodo"));
            $tabla->in_activo = 'FALSE';
            $tabla->save();
            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Periodo cerrado con Exito!';
            return Response::json($response, 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 200);
        }
    }

    public function cerrarAc($id_ac)
    {

        $validar_ae = tab_ac_ae_partida::join('t46_acciones_centralizadas as t02', 't02.id', '=', 't54_ac_ae_partidas.id_accion_centralizada')
        ->select('id_accion_centralizada', DB::raw("t02.monto as mo_ac"), DB::raw("sum(t54_ac_ae_partidas.monto) as mo_partida"))
        ->where('id_accion_centralizada', '=', $id_ac)
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
          'id' => $id_ac,
          'valido' => $in_valido
        );

        $validador = Validator::make($datos, tab_ac::$cerrarAc, $mensajes);
        if ($validador->fails()) {
            return Response::json(array(
              'success' => false,
              'msg' => $validador->getMessageBag()->toArray()
            ));
        }

        $ac_cerrar = tab_ac::find($id_ac);
        $ac_cerrar->id_estatus = 3;
        $ac_cerrar->save();

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function cronograma($id)
    {
        $data = tab_ejercicio_fiscal::select('id as periodo', 'in_activo')
        ->where('id', '=', $id)
        ->first();

        return View::make('mantenimiento.ejercicio.cronograma.lista')->with('data', $data);
    }
}
