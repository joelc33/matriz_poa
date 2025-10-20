<?php

namespace matriz\Http\Controllers\AcSeguimiento;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_ac;
use matriz\Models\AcSegto\tab_ac_ae;
use matriz\Models\AcSegto\tab_ac_ae_partida;
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\AcSegto\tab_forma_005;
use matriz\Models\Mantenimiento\tab_lapso;
use View;
use Validator;
use Input;
use Response;
use DB;
use Session;
use Auth;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class formacincoController extends Controller
{
    protected $tab_ac;
    protected $tab_forma_005;

    public function __construct(tab_ac $tab_ac, tab_forma_005 $tab_forma_005)
    {
        $this->middleware('auth');
        $this->tab_ac = $tab_ac;
        $this->tab_forma_005 = $tab_forma_005;
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
        
        return View::make('seguimiento.ac.005.lista')->with('data', $data);
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
                'nu_codigo',
                'de_ac',
                'de_lapso',
                'in_005',
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

        return View::make('seguimiento.ac.005.detalle')->with('data', $data);
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function datosNuevo($id)
    {
        $data = tab_ac::select(
            'id as id_tab_ac',
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
            'created_at',
            'updated_at',
            'id_tab_lapso',
            'id_tab_origen',
            'pp_anual',
            'tp_indicador',
            'nb_indicador_gestion',
            'de_valor_obtenido',
            'de_valor_objetivo',
            'nu_cumplimiento',
            'de_indicador_descripcion',
            'de_formula'
        )
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.005.datos.editar')->with('data', $data);
    }

    public function datos($id)
    {



            $data = tab_forma_005::select(
                'tab_forma_005.id',
                'tab_forma_005.id_tab_ac',
                't01.pp_anual',
                'tab_forma_005.tp_indicador',
                'tab_forma_005.nb_indicador_gestion',
                'tab_forma_005.de_valor_obtenido',
                'tab_forma_005.de_valor_objetivo',
                'tab_forma_005.de_valor_obtenido_acu',
                'tab_forma_005.de_valor_objetivo_acu',                    
                'tab_forma_005.nu_cumplimiento',
                'tab_forma_005.de_indicador_descripcion',
                'tab_forma_005.de_formula',
                'tab_forma_005.in_005',
                'tab_forma_005.de_observacion as de_observacion_005',
                'tab_forma_005.id_usuario_solicita',
                'tab_forma_005.id_usuario_procesa',
                'tab_forma_005.id_tab_estatus',
                'tab_forma_005.in_005 as in_bloquear_005'
            )
            ->join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_005.id_tab_ac', '=', 't01.id')
            ->where('tab_forma_005.id', '=', $id)
            ->first();

        

        return View::make('seguimiento.ac.005.datos.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_ac::$validarEditar005);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_ac::find($id);
                $tabla->pp_anual = Input::get("programado_anual");
                $tabla->tp_indicador = Input::get("tipo_indicador");
                $tabla->nb_indicador_gestion = Input::get("nombre_indicador");
                $tabla->de_valor_obtenido = Input::get("valor_obtenido");
                $tabla->de_valor_objetivo = Input::get("valor_objetivo");
                $tabla->de_valor_obtenido_acu = Input::get("valor_obtenido_acu");
                $tabla->de_valor_objetivo_acu = Input::get("valor_objetivo_acu");                
                $tabla->nu_cumplimiento = Input::get("cumplimiento");
                $tabla->de_indicador_descripcion = Input::get("indicador");
                $tabla->de_formula = Input::get("formula");
                $tabla->in_005 = true;
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
                $validator = Validator::make(Input::all(), tab_ac::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_ac();
                $tabla->pp_anual = Input::get("programado_anual");
                $tabla->tp_indicador = Input::get("tipo_indicador");
                $tabla->nb_indicador_gestion = Input::get("nombre_indicador");
                $tabla->de_valor_obtenido = Input::get("valor_obtenido");
                $tabla->de_valor_objetivo = Input::get("valor_objetivo");
                $tabla->de_valor_obtenido_acu = Input::get("valor_obtenido_acu");
                $tabla->de_valor_objetivo_acu = Input::get("valor_objetivo_acu");                
                $tabla->nu_cumplimiento = Input::get("cumplimiento");
                $tabla->de_indicador_descripcion = Input::get("indicador");
                $tabla->de_formula = Input::get("formula");
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
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function enviar($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_ac::$validarEditar005);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }

                $tabla = tab_ac::find(Input::get("id_tab_ac"));
                $tabla->in_005 = true;
                $tabla->in_bloquear_005 = true;
                $tabla->save();                
                
                $tabla_005 = tab_forma_005::find($id);
                $tabla_005->pp_anual = Input::get("programado_anual");
                $tabla_005->tp_indicador = Input::get("tipo_indicador");
                $tabla_005->nb_indicador_gestion = Input::get("nombre_indicador");
                $tabla_005->de_valor_obtenido = Input::get("valor_obtenido");
                $tabla_005->de_valor_objetivo = Input::get("valor_objetivo");
                $tabla_005->de_valor_obtenido_acu = Input::get("valor_obtenido_acu");
                $tabla_005->de_valor_objetivo_acu = Input::get("valor_objetivo_acu");                
                $tabla_005->nu_cumplimiento = Input::get("cumplimiento");
                $tabla_005->de_indicador_descripcion = Input::get("indicador");
                $tabla_005->de_formula = Input::get("formula");
                $tabla_005->de_observacion = Input::get("observacion");
                $tabla_005->id_usuario_solicita = Auth::user()->id;
                $tabla_005->id_tab_estatus = 6;
                $tabla_005->id_usuario_procesa = Auth::user()->id;                
//                $tabla_005->id_tab_estatus = 5;
                $tabla_005->save();

                DB::commit();
                return Response::json(array(
                  'success' => true,
                  'msg' => 'Datos enviados con Exito!'
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
                $validator = Validator::make(Input::all(), tab_ac::$validarEditar005);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_ac::find(Input::get("id_tab_ac"));
                $tabla->in_bloquear_005 = true;
                $tabla->in_005 = true;
                $tabla->save();

                $tabla_005 = new tab_forma_005();
                $tabla_005->id_tab_ac = Input::get("id_tab_ac");
                $tabla_005->pp_anual = Input::get("programado_anual");
                $tabla_005->tp_indicador = Input::get("tipo_indicador");
                $tabla_005->nb_indicador_gestion = Input::get("nombre_indicador");
                $tabla_005->de_valor_obtenido = Input::get("valor_obtenido");
                $tabla_005->de_valor_objetivo = Input::get("valor_objetivo");
                $tabla_005->de_valor_obtenido_acu = Input::get("valor_obtenido_acu");
                $tabla_005->de_valor_objetivo_acu = Input::get("valor_objetivo_acu");                 
                $tabla_005->nu_cumplimiento = Input::get("cumplimiento");
                $tabla_005->de_indicador_descripcion = Input::get("indicador");
                $tabla_005->de_formula = Input::get("formula");
                $tabla_005->de_observacion = Input::get("observacion");
                $tabla_005->in_005 = false;
                $tabla_005->id_usuario_solicita = Auth::user()->id;
                $tabla_005->id_tab_estatus = 6;
                $tabla_005->id_usuario_procesa = Auth::user()->id;                
                $tabla_005->in_activo = true;
//                $tabla_005->id_tab_estatus = 5;
                $tabla_005->save();

                DB::commit();
                return Response::json(array(
                  'success' => true,
                  'msg' => 'Registro enviados con Exito!'
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
    public function listaCambio()
    {
        return View::make('seguimiento.ac.005.cambio.lista');
    }
    
    public function datosLista($id)
    {


        return View::make('seguimiento.ac.005.datos.lista')->with('id_tab_ac', $id);
        
    }    

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function storeListaCambio()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');

            $tab_forma_005 = $this->tab_forma_005
            ->join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_005.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'ac_seguimiento.tab_forma_005.id_tab_estatus')
            ->select(
                'ac_seguimiento.tab_forma_005.id',
                'tx_ejecutor_ac',
                't01.id_tab_ejecutores',
                't02.in_activo',
                'de_estatus',
                DB::raw("to_char(t03.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
                DB::raw("to_char(t03.fe_fin, 'dd/mm/YYYY') as fe_fin"),
                'nu_codigo',
                'de_ac',
                'ac_seguimiento.tab_forma_005.in_005',
                't01.id_ejecutor',
                DB::raw("to_char(ac_seguimiento.tab_forma_005.created_at, 'dd/mm/YYYY hh12:mi AM') as fe_solicitud")
            )
            ->where('t01.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t01.in_activo', '=', true);

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_forma_005->where('t01.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_forma_005->where('nu_codigo', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_forma_005->count();
                $tab_forma_005->skip($start)->take($limit);
                $response['data']  = $tab_forma_005->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_forma_005->count();
                $tab_forma_005->skip($start)->take($limit);
                $response['data']  = $tab_forma_005->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 500);
        }
    }

    public function storeListaDatos()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');

            $tab_forma_005 = $this->tab_forma_005
            ->join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_005.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'ac_seguimiento.tab_forma_005.id_tab_estatus')
            ->select(
                'ac_seguimiento.tab_forma_005.id',
                't01.pp_anual',
                'ac_seguimiento.tab_forma_005.tp_indicador',
                'ac_seguimiento.tab_forma_005.nb_indicador_gestion',
                'ac_seguimiento.tab_forma_005.de_indicador_descripcion',
                'de_estatus',
                'ac_seguimiento.tab_forma_005.id_tab_estatus',
                'ac_seguimiento.tab_forma_005.in_005'
            )
            ->where('ac_seguimiento.tab_forma_005.id_tab_ac', '=', Input::get('id_tab_ac'))         
            ->where('ac_seguimiento.tab_forma_005.in_activo', '=', true);

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_forma_005->where('t01.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_forma_005->where('nu_codigo', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_forma_005->count();
                $tab_forma_005->skip($start)->take($limit);
                $response['data']  = $tab_forma_005->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_forma_005->count();
                $tab_forma_005->skip($start)->take($limit);
                $response['data']  = $tab_forma_005->orderby('ac_seguimiento.tab_forma_005.id', 'ASC')->get()->toArray();
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
    public function detalleCambio()
    {
        $data = tab_forma_005::join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_005.id_tab_ac', '=', 't01.id')
        ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
        ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
        ->join('autenticacion.tab_usuarios as t04a', 'ac_seguimiento.tab_forma_005.id_usuario_solicita', '=', 't04a.id')
        ->leftJoin('autenticacion.tab_usuarios as t04b', 'ac_seguimiento.tab_forma_005.id_usuario_procesa', '=', 't04b.id')
        ->select(
            'ac_seguimiento.tab_forma_005.id',
            'tx_ejecutor_ac',
            't01.id_tab_ejecutores',
            't02.in_activo',
            't04a.da_login as da_login_a',
            't04b.da_login as da_login_b',
            DB::raw("to_char(t03.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t03.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            'nu_codigo',
            'de_observacion',
            'de_ac',
            'ac_seguimiento.tab_forma_005.in_005',
            't01.id_ejecutor',
            DB::raw("to_char(ac_seguimiento.tab_forma_005.created_at, 'dd/mm/YYYY hh12:mi AM') as fe_solicitud")
        )
        ->where('ac_seguimiento.tab_forma_005.id', '=', Input::get('codigo'))
        ->first();

        return View::make('seguimiento.ac.005.cambio.detalle')->with('data', $data);
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function datosCambio($id)
    {
        $data = tab_forma_005::select(
            'id',
            'id_tab_ac',
            'pp_anual',
            'tp_indicador',
            'nb_indicador_gestion',
            'de_valor_obtenido',
            'de_valor_objetivo',
            'nu_cumplimiento',
            'de_indicador_descripcion',
            'de_formula',
            'in_005',
            'de_observacion',
            'id_usuario_solicita',
            'id_usuario_procesa',
            'id_tab_estatus',
            'in_activo'
        )
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.005.cambio.editar')->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function aprobar($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_ac::$validarEditar005);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
//                $tabla = tab_ac::find(Input::get("ac"));
//                $tabla->pp_anual = Input::get("programado_anual");
//                $tabla->tp_indicador = Input::get("tipo_indicador");
//                $tabla->nb_indicador_gestion = Input::get("nombre_indicador");
//                $tabla->de_valor_obtenido = Input::get("valor_objetivo");
//                $tabla->de_valor_objetivo = Input::get("valor_obtenido");
//                $tabla->nu_cumplimiento = Input::get("cumplimiento");
//                $tabla->de_indicador_descripcion = Input::get("indicador");
//                $tabla->de_formula = Input::get("formula");
//                $tabla->de_observacion = Input::get("observacion");
//                $tabla->in_005 = true;
//                $tabla->save();

                $tabla_005 = tab_forma_005::find($id);
                $tabla_005->in_005 = true;
                $tabla_005->id_tab_estatus = 6;
                $tabla_005->id_usuario_procesa = Auth::user()->id;
                $tabla_005->save();
                
                
                $cant = tab_forma_005::where('id_tab_ac', '=', Input::get("ac"))
                ->whereNotIn('id_tab_estatus', [6])
                ->count();
                
                if($cant==0){
                    
                $tabla = tab_ac::find(Input::get("ac"));
                $tabla->in_005 = true;
                $tabla->save();
                
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

        } else {

            try {
                $validator = Validator::make(Input::all(), tab_ac::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_forma_005();
                $tabla->inst_mision = Input::get("mision");
                $tabla->inst_vision = Input::get("vision");
                $tabla->inst_objetivos = Input::get("objetivos");
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
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function negar($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_ac::$validarEditar005);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }

                $tabla_005 = tab_forma_005::find($id);
                $tabla_005->id_tab_estatus = 7;
                $tabla_005->id_usuario_procesa = Auth::user()->id;
                $tabla_005->save();

                DB::commit();
                return Response::json(array(
                  'success' => true,
                  'msg' => 'Solicitud procesada con Exito!'
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
                $validator = Validator::make(Input::all(), tab_ac::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_forma_005();
                $tabla->inst_mision = Input::get("mision");
                $tabla->inst_vision = Input::get("vision");
                $tabla->inst_objetivos = Input::get("objetivos");
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
            $tabla = tab_forma_005::find(Input::get("id"));
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
