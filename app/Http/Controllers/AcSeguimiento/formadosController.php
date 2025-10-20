<?php

namespace matriz\Http\Controllers\AcSeguimiento;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_ac;
use matriz\Models\AcSegto\tab_ac_ae;
use matriz\Models\AcSegto\tab_ac_ae_fuente;
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\AcSegto\tab_forma_002;
use matriz\Models\Mantenimiento\tab_ac_ae_partida;
use matriz\Models\Mantenimiento\tab_tipo_fondo;
use matriz\Models\Mantenimiento\tab_lapso;
use matriz\Models\Mantenimiento\tab_fuente_financiamiento;
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

class formadosController extends Controller
{
    protected $tab_ac;

    public function __construct(tab_ac $tab_ac, tab_forma_002 $tab_forma_002)
    {
        $this->middleware('auth');
        $this->tab_ac = $tab_ac;
        $this->tab_forma_002 = $tab_forma_002;
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
        
        return View::make('seguimiento.ac.002.lista')->with('data', $data);
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
                where in_bloquear_002 = false and t01.id_tab_ac =ac_seguimiento.tab_ac.id) as pend_enviar"),
                'nu_codigo',
                'de_ac',
                'de_lapso',
                'in_abierta',
                'ac_seguimiento.tab_ac.id_ejecutor',
                'in_002'
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

        return View::make('seguimiento.ac.002.detalle')->with('data', $data);
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

        return View::make('seguimiento.ac.002.datos.lista')->with('data', $data);
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
        return View::make('seguimiento.ac.002.actividad.lista')->with('data', $data);
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
            'pp_anual as tx_pr_objetivo',
            'tx_pr_programado as producto_programado',
            'tx_re_esperado',
            'tx_pr_obtenido',
            'tx_pr_obtenido_a',
            'de_observacion_002',
            'in_activo',
            'id_tab_lapso',
            'in_bloquear_001',
            'de_observacion_001'
        )
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.002.editar')->with('data', $data);
    } 
    
    public function editarAe($id)
    {
        $data = tab_ac_ae::select(
            'id',
            'id_tab_ac',
            'observaciones'
        )
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.002.datos.editar')->with('data', $data);
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
                'id_tab_origen',
                'id_tab_estatus',
                'in_cargado',
                DB::raw("to_char(fecha_inicio, 'dd-mm-YYYY') as fecha_inicio"),
                DB::raw("to_char(fecha_fin, 'dd-mm-YYYY') as fecha_fin")
            )
             ->join('mantenimiento.tab_unidad_medida as t01', 'ac_seguimiento.tab_meta_fisica.id_tab_unidad_medida', '=', 't01.id')
             ->where('id_tab_ac_ae', '=', Input::get('ac_ae'));

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_meta_fisica->where('nb_meta', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_meta_fisica->count();
                $tab_meta_fisica->skip($start)->take($limit);
                $response['data']  = $tab_meta_fisica->orderby('ac_seguimiento.tab_meta_fisica.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_meta_fisica->count();
                $tab_meta_fisica->skip($start)->take($limit);
                $response['data']  = $tab_meta_fisica->orderby('ac_seguimiento.tab_meta_fisica.id', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 200);
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

        return View::make('seguimiento.ac.002.actividad.nuevo')
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
            'ac_seguimiento.tab_meta_fisica.id',
            'id_tab_ac_ae',
            'codigo as cod',
            'nb_meta',
            'ac_seguimiento.tab_meta_fisica.id_tab_unidad_medida',
            'tx_prog_anual',
            'ac_seguimiento.tab_meta_fisica.nb_responsable',
            'ac_seguimiento.tab_meta_fisica.in_activo',
            'de_unidad_medida',
            DB::raw("to_char(ac_seguimiento.tab_meta_fisica.fecha_inicio, 'dd-mm-YYYY') as fecha_inicio"),
            DB::raw("to_char(ac_seguimiento.tab_meta_fisica.fecha_fin, 'dd-mm-YYYY') as fecha_fin"),
            'ac_seguimiento.tab_meta_fisica.nu_meta_modificada',
            'ac_seguimiento.tab_meta_fisica.nu_meta_actualizada',
            'ac_seguimiento.tab_meta_fisica.nu_obtenido',
            'ac_seguimiento.tab_meta_fisica.nu_corte',
            'ac_seguimiento.tab_meta_fisica.resultado',
            'nu_po_beneficiar',
            'nu_em_previsto',
            'ac_seguimiento.tab_meta_fisica.observacion',                
            'ac_seguimiento.tab_meta_fisica.id_tab_municipio_detalle',
            'ac_seguimiento.tab_meta_fisica.id_tab_parroquia_detalle',
            'in_bloquear_002',
            'ac_seguimiento.tab_meta_fisica.id_tab_origen',
            't04.id as codigo',
            DB::raw('coalesce(tx_prog_anual::numeric) + coalesce(tab_meta_fisica.nu_meta_modificada_periodo,0) as tx_prog_nuevo'),    
            't05.id_tab_tipo_periodo'
        )
        ->join('mantenimiento.tab_unidad_medida as t01', 'ac_seguimiento.tab_meta_fisica.id_tab_unidad_medida', '=', 't01.id')
        ->join('ac_seguimiento.tab_ac_ae as t02', 'ac_seguimiento.tab_meta_fisica.id_tab_ac_ae', '=', 't02.id')
        ->join('ac_seguimiento.tab_ac as t03', 't02.id_tab_ac', '=', 't03.id')
        ->join('mantenimiento.tab_lapso as t05', 't03.id_tab_lapso', '=', 't05.id')
        ->leftjoin('ac_seguimiento.tab_forma_002 as t04', 't04.id_tab_meta_fisica', '=', 'ac_seguimiento.tab_meta_fisica.id')
        ->where('ac_seguimiento.tab_meta_fisica.id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.002.actividad.editar')->with('data', $data);
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

            try {
                $validator = Validator::make(Input::all(), tab_meta_fisica::$validarCrearMeta);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                
                $detalle = json_decode(Input::get("json_detalle"),true);
                
                $data1 = tab_ac_ae::select(
                    'mo_ae'
                )
                ->where('id', '=', Input::get("ac_ae"))
                ->first();   
                
                $data2 = tab_meta_fisica::select(
                 DB::raw("coalesce(sum(mo_presupuesto),0) + coalesce(sum(mo_modificado_anual),0) + coalesce(sum(mo_modificado),0) as mo_presupuesto")
                )
                ->join('ac_seguimiento.tab_meta_financiera as t02', 'ac_seguimiento.tab_meta_fisica.id', '=', 't02.id_tab_meta_fisica')
                ->where('id_tab_ac_ae', '=', Input::get("ac_ae"))
                ->first();
                               
                $mo_actividad = 0;
                $mo_ae = $data1->mo_ae;
                $mo_presupuesto = $data2->mo_presupuesto;
                foreach ($detalle as $lista){
                $mo_actividad = $mo_actividad + $lista['mo_presupuesto'];
                } 
                $mo_actividad_nuevo = ($mo_presupuesto+$mo_actividad);
                
                
                if(round($mo_actividad_nuevo,2)>round($mo_ae,2)){
                
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La suma de las actividades excede el monto de la accion especifica, verifique!'
                ));
                
                }
               
                $tabla = new tab_meta_fisica();
                $tabla->id_tab_ac_ae = Input::get("ac_ae");
                $tabla->nb_meta = str_replace('"', '', Input::get("actividad"));
                $tabla->id_tab_unidad_medida = Input::get("unidad_medida");
                $tabla->tx_prog_anual = 0;
                $tabla->nu_meta_modificada = Input::get("programado_anual");
                $tabla->fecha_inicio = Input::get("fecha_inicio");
                $tabla->fecha_fin = Input::get("fecha_culminacion");
                $tabla->nb_responsable = Input::get("responsable");
                $tabla->id_tab_origen = 2;
                $tabla->in_cargado = false;
                $tabla->in_activo = true;
                $tabla->save();
                
               
                
                foreach ($detalle as $lista){
                    
                $tab_meta_financiera = new tab_meta_financiera();
                $tab_meta_financiera->id_tab_meta_fisica = $tabla->id;
                $tab_meta_financiera->id_tab_municipio_detalle = $lista['co_municipio'];
                $tab_meta_financiera->id_tab_parroquia_detalle = $lista['co_parroquia'];
                $tab_meta_financiera->mo_presupuesto = 0;
                $tab_meta_financiera->mo_modificado_anual = $lista['mo_presupuesto'];
                $tab_meta_financiera->co_partida = $lista['co_partida'];
                $tab_meta_financiera->id_tab_fuente_financiamiento = $lista['co_fuente_financiamiento'];
                $tab_meta_financiera->id_tab_origen = 2;
                $tab_meta_financiera->in_cargado = false;
                $tab_meta_financiera->in_activo = true;
                $tab_meta_financiera->save();    
                
                $municipio = $lista['co_municipio'];
                $parroquia = $lista['co_parroquia'];                
                
                }   
                
                $tabla->id_tab_parroquia_detalle = $parroquia;
                $tabla->id_tab_municipio_detalle = $municipio;
                $tabla->save();                
                
                $data4 = tab_meta_fisica::select(
                 DB::raw("coalesce(sum(mo_presupuesto),0) + coalesce(sum(mo_modificado_anual),0) + coalesce(sum(mo_modificado),0) as mo_fondo"),'id_tab_fuente_financiamiento'
                )
                ->join('ac_seguimiento.tab_meta_financiera as t02', 'ac_seguimiento.tab_meta_fisica.id', '=', 't02.id_tab_meta_fisica')
                ->where('id_tab_ac_ae', '=', Input::get("ac_ae"))
                ->groupBy('id_tab_fuente_financiamiento')
                ->get();
                

                
                foreach($data4 as $item) {
                    
                $data3 = tab_ac_ae_fuente::select(
                 DB::raw("coalesce(sum(mo_fondo),0) as mo_fondo"),'de_fuente_financiamiento'
                )
                ->join('mantenimiento.tab_fuente_financiamiento as t01', 'tab_ac_ae_fuente.id_tab_tipo_fondo', '=', 't01.id_tab_tipo_fondo')
                ->where('id_tab_ac_ae', '=', Input::get("ac_ae"))
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
    
    public function guardarEditarAc($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                
                $tabla = tab_ac::find($id);
                $tabla->nu_po_beneficiada = Input::get("nu_po_beneficiada");
                $tabla->nu_em_generado = Input::get("nu_em_generado");
                $tabla->tx_pr_programado = str_replace('"', '', Input::get("producto_programado"));
                $tabla->tx_pr_obtenido = str_replace('"', '', Input::get("tx_pr_obtenido"));
                $tabla->tx_pr_obtenido_a = str_replace('"', '', Input::get("tx_pr_obtenido_a"));
                $tabla->pp_anual = str_replace('"', '', Input::get("tx_pr_objetivo"));
                $tabla->de_observacion_002 = str_replace('"', '', Input::get("de_observacion_002"));
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
    
    public function guardarEditarAe($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {

                $tabla = tab_ac_ae::find($id);
                $tabla->observaciones = Input::get("observaciones");
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
                $validator= Validator::make(Input::all(), tab_meta_fisica::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                
                
                $data1 = tab_meta_fisica::select(
                't02.nu_codigo','t02.id_tab_ejercicio_fiscal','ac_seguimiento.tab_meta_fisica.codigo','t01.id_tab_ac_ae_predefinida'
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_meta_fisica.id_tab_ac_ae', '=', 't01.id')
                ->join('ac_seguimiento.tab_ac as t02', 't01.id_tab_ac', '=', 't02.id')
                ->where('ac_seguimiento.tab_meta_fisica.id', '=', Input::get("id_tab_meta_fisica"))
                ->first();
                
                
                $data2 = tab_ac::select(
                 DB::raw("coalesce(sum(nu_obtenido),0) as nu_obtenido")
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_ac.id', '=', 't01.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t02', 't01.id', '=', 't02.id_tab_ac_ae')
                ->join('mantenimiento.tab_lapso as t03', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't03.id')
                ->where('ac_seguimiento.tab_ac.nu_codigo', '=', $data1->nu_codigo)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)        
                ->where('t02.codigo', '=', $data1->codigo)
                ->where('t01.id_tab_ac_ae_predefinida', '=', $data1->id_tab_ac_ae_predefinida)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $data1->id_tab_ejercicio_fiscal)
                ->whereNotIn('t02.id', [Input::get("id_tab_meta_fisica")])
                ->first();           
                
                if(($data2->nu_obtenido+Input::get("obtenido"))>Input::get("meta_actualizada")){
                
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La suma de las metas obtenidas excede el monto del actualizado, verifique!'
                ));
                
                }                
              
                
                $tabla = tab_meta_fisica::find(Input::get("id_tab_meta_fisica"));
                $tabla->in_bloquear_002 = false;
                $tabla->nu_meta_modificada = Input::get("meta_modificada");
                $tabla->nu_meta_actualizada = Input::get("meta_actualizada");
                $tabla->nu_obtenido = Input::get("obtenido");
                $tabla->nb_responsable = Input::get("responsable");
                $tabla->id_tab_municipio_detalle = Input::get("municipio");
                $tabla->id_tab_parroquia_detalle = Input::get("parroquia");
                $tabla->resultado = Input::get("resultado");
                $tabla->observacion = Input::get("observacion");               
                $tabla->in_cargado = false;
                $tabla->id_tab_estatus = 2;
                $tabla->save();

                $tabla_002 = tab_forma_002::find($id);
                $tabla_002->id_tab_meta_fisica = Input::get("id_tab_meta_fisica");
                $tabla_002->nu_meta_modificada = Input::get("meta_modificada");
                $tabla_002->nu_meta_actualizada = Input::get("meta_actualizada");
                $tabla_002->resultado = Input::get("resultado");
                $tabla_002->nu_obtenido = Input::get("obtenido");
                $tabla_002->observacion = Input::get("observacion");
                $tabla_002->nb_responsable = Input::get("responsable");
                $tabla_002->id_tab_municipio_detalle = Input::get("municipio");
                $tabla_002->id_tab_parroquia_detalle = Input::get("parroquia");
                $tabla_002->in_002 = false;
                $tabla_002->id_usuario_solicita = Auth::user()->id;
                $tabla_002->in_activo = true;
                $tabla_002->id_tab_estatus = 5;
                $tabla_002->save();

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
                $validator = Validator::make(Input::all(), tab_meta_fisica::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                
                $data1 = tab_meta_fisica::select(
                't02.nu_codigo','t02.id_tab_ejercicio_fiscal','ac_seguimiento.tab_meta_fisica.codigo','t01.id_tab_ac_ae_predefinida'
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_meta_fisica.id_tab_ac_ae', '=', 't01.id')
                ->join('ac_seguimiento.tab_ac as t02', 't01.id_tab_ac', '=', 't02.id')
                ->where('ac_seguimiento.tab_meta_fisica.id', '=', Input::get("id_tab_meta_fisica"))
                ->first();
                
                
                $data2 = tab_ac::select(
                 DB::raw("coalesce(sum(nu_obtenido),0) as nu_obtenido")
                )
                ->join('ac_seguimiento.tab_ac_ae as t01', 'ac_seguimiento.tab_ac.id', '=', 't01.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t02', 't01.id', '=', 't02.id_tab_ac_ae')
                ->join('mantenimiento.tab_lapso as t03', 'ac_seguimiento.tab_ac.id_tab_lapso', '=', 't03.id')
                ->where('ac_seguimiento.tab_ac.nu_codigo', '=', $data1->nu_codigo)
                ->where('t02.codigo', '=', $data1->codigo)
                ->where('t01.id_tab_ac_ae_predefinida', '=', $data1->id_tab_ac_ae_predefinida)
                ->where('ac_seguimiento.tab_ac.in_activo', '=', true)
                ->where('ac_seguimiento.tab_ac.id_tab_ejercicio_fiscal', '=', $data1->id_tab_ejercicio_fiscal)
                ->whereNotIn('t02.id', [Input::get("id_tab_meta_fisica")])
                ->first();           
                
                if(($data2->nu_obtenido+Input::get("obtenido"))>Input::get("meta_actualizada")){
                
                return Response::json(array(
                  'success' => false,
                  'msg' => 'La suma de las metas obtenidas excede el monto del actualizado, verifique!'
                ));
                
                }
                
                $tabla = tab_meta_fisica::find(Input::get("id_tab_meta_fisica"));
                $tabla->in_bloquear_002 = false;
                $tabla->nu_meta_modificada = Input::get("meta_modificada");
                $tabla->nu_meta_actualizada = Input::get("meta_actualizada");
                $tabla->nu_obtenido = Input::get("obtenido");
                $tabla->nb_responsable = Input::get("responsable");
                $tabla->id_tab_municipio_detalle = Input::get("municipio");
                $tabla->id_tab_parroquia_detalle = Input::get("parroquia");
                $tabla->resultado = Input::get("resultado");
                $tabla->observacion = Input::get("observacion");               
                $tabla->in_cargado = false;
                $tabla->id_tab_estatus = 2;
                $tabla->save();

                $tabla_002 = new tab_forma_002();
                $tabla_002->id_tab_meta_fisica = Input::get("id_tab_meta_fisica");
                $tabla_002->nu_meta_modificada = Input::get("meta_modificada");
                $tabla_002->nu_meta_actualizada = Input::get("meta_actualizada");
                $tabla_002->resultado = Input::get("resultado");
                $tabla_002->nu_obtenido = Input::get("obtenido");
                $tabla_002->observacion = Input::get("observacion");
                $tabla_002->nb_responsable = Input::get("responsable");
                $tabla_002->id_tab_municipio_detalle = Input::get("municipio");
                $tabla_002->id_tab_parroquia_detalle = Input::get("parroquia");
                $tabla_002->in_002 = false;
                $tabla_002->id_usuario_solicita = Auth::user()->id;
                $tabla_002->in_activo = true;
                $tabla_002->id_tab_estatus = 5;
                $tabla_002->save();

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
    
    public function cargar()
    {
        DB::beginTransaction();
        try {
            
            
                $cant = tab_meta_fisica::where('id_tab_ac_ae', '=', Input::get("id"))
                ->where('id_tab_estatus','=', 1)
                ->count();
                
                if($cant>0){
             $response['success']  = 'true';
            $response['msg']  = 'Tiene Actividades Pendientes por cargar, verifique!';
            return Response::json($response, 200);                   
                }else{
                    
                 $cant1 = tab_meta_fisica::where('id_tab_ac_ae', '=', Input::get("id"))
                ->where('id_tab_estatus','=', 2)
                ->where('in_cargado','=', false)         
                ->count();                   
                 
             if($cant1>0){    
              
            $data = tab_forma_002::select(
                't01.id as id_tab_meta_fisica',
                'ac_seguimiento.tab_forma_002.id'
            )
            ->join('ac_seguimiento.tab_meta_fisica as t01', 'ac_seguimiento.tab_forma_002.id_tab_meta_fisica', '=', 't01.id')
            ->where('id_tab_ac_ae', '=', Input::get("id"))
            ->get();                        
                

                
                foreach ($data as $lista){   

            $tabla = tab_meta_fisica::find($lista->id_tab_meta_fisica);
            $tabla->in_cargado = true;
            $tabla->in_bloquear_002 = true;
            $tabla->save();  
            
            
//            $tabla_002 = tab_forma_002::find($lista->id);
//            $tabla_002->in_002 = true;
//            $tabla_002->save();             
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
    
    
        public function listaCambio()
    {
        return View::make('seguimiento.ac.002.cambio.lista');
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
        
        return View::make('seguimiento.ac.002.cambio.listaAe')->with('data', $data);            
    }    

    public function storeListaCambio()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');
            $id_tab_tipo_periodo = Input::get('id_tab_tipo_periodo');

            $tab_forma_002 = $this->tab_forma_002
            ->join('ac_seguimiento.tab_meta_fisica as t06', 'ac_seguimiento.tab_forma_002.id_tab_meta_fisica', '=', 't06.id')        
            ->join('ac_seguimiento.tab_ac_ae as t05', 't06.id_tab_ac_ae', '=', 't05.id')        
            ->join('ac_seguimiento.tab_ac as t01', 't05.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'ac_seguimiento.tab_forma_002.id_tab_estatus')
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
                't05.in_002' 
            )
            ->where('t01.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t01.in_activo', '=', true)
            ->where('in_cargado', '=', true)
                    ->groupBy('t01.id')
            ->groupBy('tx_ejecutor_ac')
                    ->groupBy('t02.in_activo')
                   ->groupBy('t05.in_002')
                    ->groupBy('id_tab_ac_ae')
                    ->groupBy('nu_codigo')
                    ->groupBy('de_ac')
                    ->groupBy('de_lapso')
                    ->groupBy('t01.id_ejecutor')
                    ->groupBy('de_nombre')
                    ;

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_forma_002->where('t01.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_forma_002->where('tx_ejecutor_ac', 'ILIKE', "%$variable%");
                }

                if($id_tab_tipo_periodo!="") {
                    $tab_forma_002->where('id_tab_tipo_periodo', '=', $id_tab_tipo_periodo);
                }   
                
                $response['success']  = 'true';
                $response['total'] = $tab_forma_002->get()->count();
                $tab_forma_002->skip($start)->take($limit);
                $response['data']  = $tab_forma_002->orderby('t01.id_ejecutor', 'ASC')->orderby('nu_codigo', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_forma_002->get()->count();
                $tab_forma_002->skip($start)->take($limit);

                $response['data']  = $tab_forma_002->orderby('t01.id_ejecutor', 'ASC')->orderby('nu_codigo', 'ASC')->get()->toArray();
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

            $tab_forma_002 = $this->tab_forma_002
            ->join('ac_seguimiento.tab_meta_fisica as t06', 'ac_seguimiento.tab_forma_002.id_tab_meta_fisica', '=', 't06.id')        
            ->join('ac_seguimiento.tab_ac_ae as t05', 't06.id_tab_ac_ae', '=', 't05.id')        
            ->join('ac_seguimiento.tab_ac as t01', 't05.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'ac_seguimiento.tab_forma_002.id_tab_estatus')  
            ->join('mantenimiento.tab_unidad_medida as t07', 't06.id_tab_unidad_medida', '=', 't07.id')
            ->select(
                'ac_seguimiento.tab_forma_002.id',
                'codigo',
                'nb_meta',
                'tx_prog_anual',
                't06.in_activo',
                'de_estatus',
                'de_unidad_medida',
                'ac_seguimiento.tab_forma_002.in_002',
                DB::raw("to_char(t06.fecha_inicio, 'dd-mm-YYYY') as fecha_inicio"),
                DB::raw("to_char(t06.fecha_fin, 'dd-mm-YYYY') as fecha_fin") 
            )
            ->where('t01.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('id_tab_ac_ae', '=', Input::get('ac_ae'))
            ->where('t01.in_activo', '=', true)
            ->where('in_cargado', '=', true);

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_forma_002->where('t01.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_forma_002->where('nb_meta', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_forma_002->count();
                $tab_forma_002->skip($start)->take($limit);
                $response['data']  = $tab_forma_002->orderby('t06.codigo', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_forma_002->count();
                $tab_forma_002->skip($start)->take($limit);
                $response['data']  = $tab_forma_002->orderby('t06.codigo', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 500);
        }
    }    

    public function detalleCambio()
    {
        

        $data = tab_forma_002::join('ac_seguimiento.tab_meta_fisica as t06', 'ac_seguimiento.tab_forma_002.id_tab_meta_fisica', '=', 't06.id')        
        ->join('ac_seguimiento.tab_ac_ae as t05', 't06.id_tab_ac_ae', '=', 't05.id')
        ->join('mantenimiento.tab_ac_ae_predefinida as t07', 't05.id_tab_ac_ae_predefinida', '=', 't07.id')
        ->join('ac_seguimiento.tab_ac as t01', 't05.id_tab_ac', '=', 't01.id')
        ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
        ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
        ->join('autenticacion.tab_usuarios as t04a', 'ac_seguimiento.tab_forma_002.id_usuario_solicita', '=', 't04a.id')
        ->leftJoin('autenticacion.tab_usuarios as t04b', 'ac_seguimiento.tab_forma_002.id_usuario_procesa', '=', 't04b.id')
        ->select(
            'ac_seguimiento.tab_forma_002.id',
            'tx_ejecutor_ac',
            't01.id_tab_ejecutores',
            'nb_meta',
            'de_nombre',
            't02.in_activo',
            't04a.da_login as da_login_a',
            't04b.da_login as da_login_b',
            DB::raw("to_char(t03.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t03.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            'nu_codigo',
            'de_ac',
            'ac_seguimiento.tab_forma_002.in_002',
            't01.id_ejecutor',
            DB::raw("to_char(ac_seguimiento.tab_forma_002.created_at, 'dd/mm/YYYY hh12:mi AM') as fe_solicitud")
        )
        ->where('ac_seguimiento.tab_forma_002.id', '=', Input::get('codigo'))
        ->first();

        return View::make('seguimiento.ac.002.cambio.detalle')->with('data', $data);
    } 
    
    public function datosCambio($id)
    {
        $data = tab_meta_fisica::select(
            't02.id',
            't02.id_tab_meta_fisica',    
            'id_tab_ac_ae',
            'codigo',
            'nb_meta',
            'id_tab_unidad_medida',
            'tx_prog_anual',
            't02.nb_responsable',
            'ac_seguimiento.tab_meta_fisica.in_activo',
            'de_unidad_medida',
            DB::raw("to_char(fecha_inicio, 'dd-mm-YYYY') as fecha_inicio"),
            DB::raw("to_char(fecha_fin, 'dd-mm-YYYY') as fecha_fin"),
            't02.nu_meta_modificada',
            't02.nu_meta_actualizada',
            't02.nu_obtenido',
            't02.nu_corte',
            't02.resultado',
            't02.observacion',                
            't02.in_002',    
            't02.id_tab_municipio_detalle',
            't02.id_tab_parroquia_detalle',
            'in_bloquear_002'    
        )
        ->join('mantenimiento.tab_unidad_medida as t01', 'ac_seguimiento.tab_meta_fisica.id_tab_unidad_medida', '=', 't01.id')
        ->join('ac_seguimiento.tab_forma_002 as t02', 'ac_seguimiento.tab_meta_fisica.id', '=', 't02.id_tab_meta_fisica')
        ->where('t02.id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.002.cambio.editar')->with('data', $data);
    }    

    public function negar($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_meta_fisica::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_meta_fisica::find(Input::get("id_tab_meta_fisica"));
                $tabla->in_cargado = false;               
                $tabla->in_bloquear_002 = false;
                $tabla->id_tab_estatus = 1;
                $tabla->save();

                $tabla_002 = tab_forma_002::find($id);
                $tabla_002->in_002 = false;
                $tabla_002->id_tab_estatus = 7;
                $tabla_002->id_usuario_procesa = Auth::user()->id;
                $tabla_002->save();

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
                $validator= Validator::make(Input::all(), tab_meta_fisica::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }

                $tabla_002 = tab_forma_002::find($id);
                $tabla_002->in_002 = true;
                $tabla_002->id_tab_estatus = 6;
                $tabla_002->id_usuario_procesa = Auth::user()->id;
                $tabla_002->save();
                
                $tabla_meta = tab_meta_fisica::find(Input::get("id_tab_meta_fisica"));
                $tabla_meta->id_tab_estatus = 6;
                $tabla_meta->save();                
                
                $data = tab_forma_002::join('ac_seguimiento.tab_meta_fisica as t06', 'ac_seguimiento.tab_forma_002.id_tab_meta_fisica', '=', 't06.id')        
                ->join('ac_seguimiento.tab_ac_ae as t05', 't06.id_tab_ac_ae', '=', 't05.id')
                ->join('ac_seguimiento.tab_ac as t01', 't05.id_tab_ac', '=', 't01.id')
                ->select(
                    't01.id',
                    't06.id_tab_ac_ae'    
                )
                ->where('ac_seguimiento.tab_forma_002.id', '=', $id)
                ->first();         
                
                
                $cant = tab_meta_fisica::where('id_tab_ac_ae', '=', $data->id_tab_ac_ae)
                ->whereNotIn('id_tab_estatus', [6])
                ->count();
                
                if($cant==0){                    
                
                $tabla_ae = tab_ac_ae::find($data->id_tab_ac_ae);
                $tabla_ae->in_002 = true;
                $tabla_ae->save();                
                
                }
                
                $cant1 = tab_ac::join('ac_seguimiento.tab_ac_ae as t03', 'ac_seguimiento.tab_ac.id', '=', 't03.id_tab_ac')
                ->join('ac_seguimiento.tab_meta_fisica as t01', 't03.id', '=', 't01.id_tab_ac_ae')        
                ->where('tab_ac.id', '=', $data->id)
                ->whereNotIn('t01.id_tab_estatus', [6])
                ->count(); 
                
                if($cant1==0){
                
                $tabla = tab_ac::find($data->id);
                $tabla->in_002 = true;
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

        } 
    }

    public function nuevoFinanciera($id)
    {

        $data = tab_ac_ae::select('id as id_tab_meta_fisica','id_tab_ac_ae_predefinida')
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.002.financiera.editar')
        ->with('data', $data);
    }  
    
    public function partida()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ac_ae_partida::select(DB::raw('substring(nu_partida::text from 1 for 3) as co_partida'))
        ->where('id_tab_ac_ae_predefinida', '=', Input::get('id_tab_ac_ae_predefinida'))
        ->where('in_activo', '=', true)
        ->groupBy(DB::raw('1'))
        ->orderby('co_partida', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }    
    

    public function fondoTipo()
    {
        
         $incluir = tab_ac_ae_fuente::select('id_tab_tipo_fondo')
        ->where('id_tab_ac_ae', '=', Input::get('id_tab_ac_ae'))
        ->get()->toArray();
                
        $response['success']  = 'true';
        $response['data']  = tab_tipo_fondo::select('t01.id', 'de_tipo_fondo')
                ->join('mantenimiento.tab_fuente_financiamiento as t01', 'tab_tipo_fondo.id', '=', 't01.id_tab_tipo_fondo')
                ->whereIn('tab_tipo_fondo.id',$incluir)
                ->orderby('tab_tipo_fondo.id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }
    
    public function eliminar()
    {
        DB::beginTransaction();
        try {
            
            $tabla = tab_meta_fisica::find(Input::get("id"));
            $tabla->delete();
            
            $tab_meta_financiera = tab_meta_financiera::where('id_tab_meta_fisica', '=', Input::get("id"))
            ->delete();        
            
            $tab_forma_002 = tab_forma_002::where('id_tab_meta_fisica', '=', Input::get("id"))
            ->delete();             

            DB::commit();
            
            
            $data = tab_meta_fisica::select(
                'id',
                'codigo as original',
                DB::raw("lpad((row_number() OVER (ORDER BY codigo))::text, 3, '0') as corregido")
            )
            ->where('id_tab_ac_ae', '=', $tabla->id_tab_ac_ae)
            ->get();                        

                foreach ($data as $lista){   

            $tabla_meta = tab_meta_fisica::find($lista->id);
            $tabla_meta->codigo = $lista->corregido;
            $tabla_meta->save();  
                      
                }             

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
