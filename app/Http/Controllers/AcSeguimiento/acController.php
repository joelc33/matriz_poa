<?php

namespace matriz\Http\Controllers\AcSeguimiento;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_ac;
use matriz\Models\AcSegto\tab_ac_ae;
use matriz\Models\AcSegto\tab_ac_ae_partida;
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\Ac\tab_ac as ac;
use matriz\Models\Ac\tab_ac_ae as ac_ae;
use matriz\Models\Ac\tab_ac_ae_partida as ac_ae_partida;
use matriz\Models\Ac\tab_meta_fisica as ac_ae_mf;
use matriz\Models\Ac\tab_meta_financiera as ac_ae_mff;
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
    public function lista()
    {
        return View::make('seguimiento.ac.lista');
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
                'nu_codigo',
                'de_ac',
                'ac_seguimiento.tab_ac.id_ejecutor'
            )
            ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
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
                $response['data']  = $tab_ac->orderby('ac_seguimiento.tab_ac.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_ac->count();
                $tab_ac->skip($start)->take($limit);
                $response['data']  = $tab_ac->orderby('ac_seguimiento.tab_ac.id', 'ASC')->get()->toArray();
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
        return View::make('seguimiento.ac.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function disponible()
    {

        $excluir = tab_ac::select('nu_codigo')
        ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
        ->where('id_tab_lapso', '=', Input::get('periodo'))
        ->get()->toArray();

        $tab_ac = ac::join('mantenimiento.tab_ejecutores as t01', 'public.t46_acciones_centralizadas.id_ejecutor', '=', 't01.id_ejecutor')
        ->join('mantenimiento.tab_ac_predefinida as t03', 'public.t46_acciones_centralizadas.id_accion', '=', 't03.id')
        ->select(
            'public.t46_acciones_centralizadas.id',
            'public.t46_acciones_centralizadas.id_ejecutor',
            'id_ejercicio',
            'id_accion',
            'id_subsector',
            'id_estatus',
            'sit_presupuesto',
            'codigo_new_etapa',
            'descripcion',
            'monto',
            'monto_calc',
            'fecha_inicio',
            'fecha_fin',
            'de_nombre',
            'tx_ejecutor',
            DB::raw("'AC' || public.t46_acciones_centralizadas.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo")
        )
        ->where('edo_reg', '=', true)
        ->where('id_estatus', '=', 3)
        ->whereNotIn(DB::raw("'AC' || public.t46_acciones_centralizadas.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0')"), $excluir)
        ->where('id_ejercicio', '=', Session::get('ejercicio'));

        $rol_planificador = array(3, 8);
        if (in_array(Session::get('rol'), $rol_planificador)) {
            $tab_ac->where('public.t46_acciones_centralizadas.id_ejecutor', '=', Session::get('ejecutor'));
        }

        $response['success']  = 'true';
        $response['data']  = $tab_ac->orderby('public.t46_acciones_centralizadas.id_ejecutor', 'ASC')
        ->orderby('public.t46_acciones_centralizadas.id_accion', 'ASC')
        ->get()->toArray();

        return Response::json($response, 200);
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
                $validator= Validator::make(Input::all(), tab_ac::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_ac::find($id);
                $tabla->co_aplicacion = Input::get("codigo");
                $tabla->de_aplicacion = Input::get("aplicacion");
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

            $data = tab_lapso::select(
                'id',
                'id_tab_ejercicio_fiscal',
                'id_tab_periodo',
                'nu_lapso',
                'fe_inicio',
                'fe_fin',
                'in_activo'
            )
            ->where('id', '=', Input::get('ejercicio'))
            ->first();

    
            $tab_ac = ac::join('mantenimiento.tab_ejecutores as t01', 'public.t46_acciones_centralizadas.id_ejecutor', '=', 't01.id_ejecutor')
            ->join('mantenimiento.tab_ac_predefinida as t03', 'public.t46_acciones_centralizadas.id_accion', '=', 't03.id')
            ->select(
                'public.t46_acciones_centralizadas.id',
                'public.t46_acciones_centralizadas.id_ejecutor',
                'id_ejercicio',
                'id_accion',
                'id_subsector',
                'id_estatus',
                'sit_presupuesto',
                'codigo_new_etapa',
                'de_nombre',
                'monto',
                'monto_calc',
                'fecha_inicio',
                'fecha_fin',
                'de_nombre',
                'tx_ejecutor',
                't01.id as id_tab_ejecutores',
                'inst_mision',
                'inst_vision',
                'inst_objetivos',
                'nu_po_beneficiar',
                'nu_em_previsto',
                'tx_re_esperado',
                'tx_pr_objetivo',
                DB::raw("'AC' || public.t46_acciones_centralizadas.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo")
            )
            ->where('edo_reg', '=', true)
            ->where('id_estatus', '=', 3)
            ->where('public.t46_acciones_centralizadas.id', '=', Input::get('ac'))
            ->where('id_ejercicio', '=', $data->id_tab_ejercicio_fiscal)
            ->first();

            $tab_ac_ae = ac_ae::select(
                'id_accion_centralizada',
                'id_accion',
                'public.t47_ac_accion_especifica.id_ejecutor',
                'bien_servicio',
                'id_unidad_medida',
                'meta',
                'ponderacion',
                'id_tipo_fondo',
                'monto',
                'monto_calc',
                'fecha_inicio',
                'fecha_fin',
                'edo_reg',
                'fecha_creacion',
                'fecha_actualizacion',
                'objetivo_institucional',
                'id_tab_ejecutor',
                'in_definitivo',
                't01.id as id_tab_ejecutores'
            )
            ->join('mantenimiento.tab_ejecutores as t01', 'public.t47_ac_accion_especifica.id_ejecutor', '=', 't01.id_ejecutor')
            ->where('id_accion_centralizada', '=', $tab_ac->id)
            ->orderby('id_accion', 'ASC')
            ->get();

            try {
                $validator = Validator::make(Input::all(), tab_ac::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                
                
                
                $tabla = new tab_ac();
                $tabla->nu_codigo = $tab_ac->codigo;
                $tabla->id_ejecutor = $tab_ac->id_ejecutor;
                $tabla->id_tab_ejecutores = $tab_ac->id_tab_ejecutores;
                $tabla->id_tab_ejercicio_fiscal = $tab_ac->id_ejercicio;
                $tabla->id_tab_ac_predefinida = $tab_ac->id_accion;
                $tabla->id_tab_sectores = $tab_ac->id_subsector;
                $tabla->id_tab_estatus = $tab_ac->id_estatus;
                $tabla->id_tab_situacion_presupuestaria = $tab_ac->sit_presupuesto;
                $tabla->id_tab_tipo_registro = 1;
                $tabla->co_new_etapa = $tab_ac->codigo_new_etapa;
                $tabla->de_ac = $tab_ac->de_nombre;
                $tabla->mo_ac = $tab_ac->monto;
                $tabla->mo_calculado = $tab_ac->monto_calc;
                $tabla->fe_inicio = $data->fe_inicio;
                $tabla->fe_fin = $data->fe_fin;
                $tabla->inst_mision = $tab_ac->inst_mision;
                $tabla->inst_vision = $tab_ac->inst_vision;
                $tabla->inst_objetivos = $tab_ac->inst_objetivos;
                $tabla->nu_po_beneficiar = $tab_ac->nu_po_beneficiar;
                $tabla->nu_em_previsto = $tab_ac->nu_em_previsto;
                $tabla->tx_re_esperado = $tab_ac->tx_re_esperado;
                $tabla->pp_anual = $tab_ac->tx_pr_objetivo;
                $tabla->id_tab_lapso = $data->id;
                $tabla->id_tab_origen = 1;
                $tabla->in_activo = 'TRUE';
                $tabla->in_001 = false;
                $tabla->in_005 = false;
                $tabla->in_bloquear_001 = false;
                $tabla->in_bloquear_005 = false;
                $tabla->save();

                foreach ($tab_ac_ae as $arreglo_ac_ae) {

                    $tabla_ac_ae= new tab_ac_ae();
                    $tabla_ac_ae->id_tab_ac = $tabla->id;
                    $tabla_ac_ae->id_tab_ac_ae_predefinida = $arreglo_ac_ae->id_accion;
                    $tabla_ac_ae->id_ejecutor = $arreglo_ac_ae->id_ejecutor;
                    $tabla_ac_ae->id_tab_ejecutores = $tab_ac->id_tab_ejecutores;
                    $tabla_ac_ae->bien_servicio = $arreglo_ac_ae->bien_servicio;
                    $tabla_ac_ae->id_tab_unidad_medida = $arreglo_ac_ae->id_unidad_medida;
                    $tabla_ac_ae->meta = $arreglo_ac_ae->meta;
                    $tabla_ac_ae->ponderacion = $arreglo_ac_ae->ponderacion;
                    $tabla_ac_ae->id_tab_tipo_fondo = $arreglo_ac_ae->id_tipo_fondo;
                    $tabla_ac_ae->mo_ae = $arreglo_ac_ae->monto;
                    $tabla_ac_ae->mo_ae_calculado = $arreglo_ac_ae->monto_calc;
                    $tabla_ac_ae->fecha_inicio = $arreglo_ac_ae->fecha_inicio;
                    $tabla_ac_ae->fecha_fin = $arreglo_ac_ae->fecha_fin;
                    $tabla_ac_ae->objetivo_institucional = $arreglo_ac_ae->objetivo_institucional;
                    $tabla_ac_ae->id_tab_origen = 1;
                    $tabla_ac_ae->in_activo = 'TRUE';
                    $tabla_ac_ae->save();

                    $ac_ae_partida = ac_ae_partida::select(
                        'id_accion_centralizada',
                        'id_accion',
                        'co_partida',
                        'monto',
                        'edo_reg',
                        'fecha_creacion',
                        'fecha_actualizacion',
                        'id_tab_ejercicio_fiscal',
                        'nu_aplicacion',
                        'de_denominacion'
                    )
                    ->where('id_accion_centralizada', '=', $arreglo_ac_ae->id_accion_centralizada)
                    ->where('id_accion', '=', $arreglo_ac_ae->id_accion)
                    ->where('edo_reg', '=', true)
                    ->orderby('co_partida', 'ASC')
                    ->get();

                    foreach ($ac_ae_partida as $arreglo_ac_ae_partida) {

                        
                        
                        $tabla_ac_ae_partida= new tab_ac_ae_partida();
                        $tabla_ac_ae_partida->id_tab_ac_ae = $tabla_ac_ae->id;
                        $tabla_ac_ae_partida->co_partida = $arreglo_ac_ae_partida->co_partida;
                        $tabla_ac_ae_partida->monto = $arreglo_ac_ae_partida->monto;
                        $tabla_ac_ae_partida->de_denominacion = $arreglo_ac_ae_partida->de_denominacion;
                        $tabla_ac_ae_partida->id_tab_ejercicio_fiscal = $arreglo_ac_ae_partida->id_tab_ejercicio_fiscal;
                        $tabla_ac_ae_partida->in_activo = 'TRUE';
                        $tabla_ac_ae_partida->id_tab_origen = 1;
                        $tabla_ac_ae_partida->save();

                    }

                    $ac_ae_mf = ac_ae_mf::select(
                        'co_metas',
                        'id_accion_centralizada',
                        'co_ac_acc_espec',
                        'codigo',
                        'nb_meta',
                        'co_unidades_medida',
                        'tx_prog_anual',
                        'fecha_inicio',
                        'fecha_fin',
                        'nb_responsable',
                        'fecha_creacion',
                        'fecha_actualizacion',
                        'edo_reg'
                    )
                    ->where('id_accion_centralizada', '=', $arreglo_ac_ae->id_accion_centralizada)
                    ->where('co_ac_acc_espec', '=', $arreglo_ac_ae->id_accion)
                    ->where('edo_reg', '=', true)
                    ->orderby('codigo', 'ASC')
                    ->get();
                    
//                    var_dump($ac_ae_mf);

                    foreach ($ac_ae_mf as $arreglo_ac_ae_mf) {

                        $tabla_ac_ae_mf= new tab_meta_fisica();
                        $tabla_ac_ae_mf->id_tab_ac_ae = $tabla_ac_ae->id;
                        $tabla_ac_ae_mf->codigo = $arreglo_ac_ae_mf->codigo;
                        $tabla_ac_ae_mf->nb_meta = $arreglo_ac_ae_mf->nb_meta;
                        $tabla_ac_ae_mf->id_tab_unidad_medida = $arreglo_ac_ae_mf->co_unidades_medida;
                        $tabla_ac_ae_mf->tx_prog_anual = $arreglo_ac_ae_mf->tx_prog_anual;
                        $tabla_ac_ae_mf->fecha_inicio = $arreglo_ac_ae_mf->fecha_inicio;
                        $tabla_ac_ae_mf->fecha_fin = $arreglo_ac_ae_mf->fecha_fin;
                        $tabla_ac_ae_mf->nb_responsable = $arreglo_ac_ae_mf->nb_responsable;
                        $tabla_ac_ae_mf->id_tab_origen = 1;
                        $tabla_ac_ae_mf->in_activo = 'TRUE';
                        $tabla_ac_ae_mf->in_cargado = 'FALSE';
                        $tabla_ac_ae_mf->save();

                        $ac_ae_mff = ac_ae_mff::select(
                            'co_metas_detalle',
                            'co_metas',
                            'co_municipio',
                            'co_parroquia',
                            'mo_presupuesto',
                            'co_partida',
                            'co_fuente',
                            'fecha_creacion',
                            'fecha_actualizacion',
                            'edo_reg'
                        )
                        ->where('co_metas', '=', $arreglo_ac_ae_mf->co_metas)
                        ->where('edo_reg', '=', true)
                        ->orderby('co_partida', 'ASC')
                        ->get();

                        foreach ($ac_ae_mff as $arreglo_ac_ae_mff) {

                            $tabla_ac_ae_mff= new tab_meta_financiera();
                            $tabla_ac_ae_mff->id_tab_meta_fisica = $tabla_ac_ae_mf->id;
                            $tabla_ac_ae_mff->id_tab_municipio_detalle = $arreglo_ac_ae_mff->co_municipio;
                            $tabla_ac_ae_mff->id_tab_parroquia_detalle = $arreglo_ac_ae_mff->co_parroquia;
                            $tabla_ac_ae_mff->mo_presupuesto = $arreglo_ac_ae_mff->mo_presupuesto;
                            $tabla_ac_ae_mff->co_partida = $arreglo_ac_ae_mff->co_partida;
                            $tabla_ac_ae_mff->id_tab_fuente_financiamiento = $arreglo_ac_ae_mff->co_fuente;
                            $tabla_ac_ae_mff->id_tab_origen = 1;
                            $tabla_ac_ae_mff->in_activo = 'TRUE';
                            $tabla_ac_ae_mff->in_cargado = 'FALSE';
                            $tabla_ac_ae_mff->save();

                        }

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

        return View::make('seguimiento.ac.detalle')->with('data', $data);
    }

}
