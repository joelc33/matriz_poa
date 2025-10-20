<?php

namespace matriz\Http\Controllers\AcSeguimiento;

//*******agregar esta linea******//
use matriz\Models\AcSegto\tab_ac;
use matriz\Models\AcSegto\tab_forma_001;
use matriz\Models\Mantenimiento\tab_lapso;
use matriz\Models\AcSegto\tab_ac_ae;
use matriz\Models\AcSegto\tab_meta_fisica;
use matriz\Models\AcSegto\tab_meta_financiera;
use matriz\Models\AcSegto\tab_ac_ae_fuente;
use matriz\Models\AcSegto\tab_ac_localizacion;
use matriz\Models\AcSegto\tab_ac_vinculo;
use matriz\Models\AcSegto\tab_ac_responsable;
use matriz\Models\AcSegto\tab_forma_005;
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

class formaunoController extends Controller
{
    protected $tab_ac;
    protected $tab_forma_001;

    public function __construct(tab_ac $tab_ac, tab_forma_001 $tab_forma_001)
    {
        $this->middleware('auth');
        $this->tab_ac = $tab_ac;
        $this->tab_forma_001 = $tab_forma_001;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista($id)
    {
        $data = tab_lapso::select(
                'id',
                DB::raw("NOW() between fe_inicio and fe_fin as activo")
                )
        ->where('id', '=', $id)
        ->first();
        
        return View::make('seguimiento.ac.001.lista')->with('data', $data);
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
                'in_001',
                'in_abierta',
                'ac_seguimiento.tab_ac.id_ejecutor',
                'ac_seguimiento.tab_ac.id_tab_tipo_registro'
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

        return View::make('seguimiento.ac.001.detalle')->with('data', $data);
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
            'nu_po_beneficiada',
            'nu_em_generado',                  
            'tx_re_esperado',
            'in_activo',
            'id_tab_lapso',
            'in_bloquear_001',
            'de_observacion_001'
        )
        ->where('id', '=', $id)
        ->first();

//        if (tab_forma_001::where('id_tab_ac', '=', $id)
//        ->where('id_tab_estatus', '=', 5)
//        ->where('in_001', '=', false)->exists()) {
//
//            $data = tab_forma_001::select(
//                'id_tab_ac as id',
//                'inst_mision',
//                'inst_vision',
//                'inst_objetivos',
//                'in_001',
//                'created_at',
//                'updated_at',
//                'de_observacion',
//                'id_usuario_solicita',
//                'id_usuario_procesa',
//                'nu_po_beneficiar',
//                'nu_em_previsto',
//                'nu_po_beneficiada',
//                'nu_em_generado',                    
//                'id_tab_estatus',
//                'in_activo as in_bloquear_001'
//            )
//            ->where('id_tab_ac', '=', $id)
//            ->where('id_tab_estatus', '=', 5)
//            ->where('in_001', '=', false)
//            ->first();
//
//        }

        //return View::make('seguimiento.ac.001.datos.lista')->with('data',$data);
        return View::make('seguimiento.ac.001.datos.editar')->with('data', $data);
    }
    
    public function datosSector($id)
    {
        $data = tab_ac::select(
            'id',
            'de_sector',
            'tx_ejecutor_ac'
        )
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.001.datos.editarSector')->with('data', $data);
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
                $tabla->inst_mision = Input::get("mision");
                $tabla->inst_vision = Input::get("vision");
                $tabla->inst_objetivos = Input::get("objetivos");
                $tabla->save();
                
                $data = tab_ac::select(
                    'id'
                )
                ->where('id_ejecutor', '=', $tabla->id_ejecutor)
                ->where('id_tab_ejercicio_fiscal', '=', $tabla->id_tab_ejercicio_fiscal)
                ->where('id_tab_lapso', '=', $tabla->id_tab_lapso)
                ->get();                

                foreach ($data as $lista){
  
                $tabla_ac = tab_ac::find($lista->id);
                $tabla_ac->inst_mision = Input::get("mision");
                $tabla_ac->inst_vision = Input::get("vision");
                $tabla_ac->inst_objetivos = Input::get("objetivos");
                $tabla_ac->save(); 
                
                $data2 = tab_forma_001::select(
                    'id'
                )
                ->where('id_tab_ac', '=', $lista->id)
                ->first(); 

                if($data2){
                $tabla_001 = tab_forma_001::find($data2->id);
                $tabla_001->inst_mision = Input::get("mision");
                $tabla_001->inst_vision = Input::get("vision");
                $tabla_001->inst_objetivos = Input::get("objetivos");
                $tabla_001->save(); 
                }
                    
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
                $validator = Validator::make(Input::all(), tab_ac::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_ac();
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
    
    public function guardarSector($id = null)
    {
        DB::beginTransaction();

            try {
                

                $tabla = tab_ac::find($id);
                $tabla->de_sector = Input::get("de_sector");
                $tabla->tx_ejecutor_ac = Input::get("tx_ejecutor_ac");
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
                $validator= Validator::make(Input::all(), tab_ac::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                
                
                $tabla = tab_ac::find($id);
                $tabla->in_bloquear_001 = true;
                $tabla->save();

                $data = tab_ac::select(
                    'id'
                )
                ->where('id_ejecutor', '=', $tabla->id_ejecutor)
                ->where('id_tab_ejercicio_fiscal', '=', $tabla->id_tab_ejercicio_fiscal)
                ->where('id_tab_lapso', '=', $tabla->id_tab_lapso)
                ->get();                        
                
                
                foreach ($data as $lista){
                    

                $tabla_ac = tab_ac::find($lista->id);
                $tabla_ac->in_bloquear_001 = true;
                $tabla_ac->inst_mision = Input::get("mision");
                $tabla_ac->inst_vision = Input::get("vision");
                $tabla_ac->inst_objetivos = Input::get("objetivos");                
                $tabla_ac->save();  
                
                $tabla_001 = new tab_forma_001();
                $tabla_001->id_tab_ac = $lista->id;
                $tabla_001->inst_mision = Input::get("mision");
                $tabla_001->inst_vision = Input::get("vision");
                $tabla_001->inst_objetivos = Input::get("objetivos");
                $tabla_001->in_001 = false;
                $tabla_001->id_usuario_solicita = Auth::user()->id;
                $tabla_001->in_activo = true;
                $tabla_001->id_tab_estatus = 5;
                $tabla_001->save();                
                    
                }                 
                


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
                $validator = Validator::make(Input::all(), tab_ac::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_forma_001();
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
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function listaCambio()
    {
        return View::make('seguimiento.ac.001.cambio.lista');
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
            $id_tab_tipo_periodo = Input::get('id_tab_tipo_periodo');

            $tab_forma_001 = $this->tab_forma_001
            ->join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_001.id_tab_ac', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'ac_seguimiento.tab_forma_001.id_tab_estatus')
            ->select(
                'ac_seguimiento.tab_forma_001.id',
                'ac_seguimiento.tab_forma_001.id_tab_ac',
                'tx_ejecutor_ac',
                't01.id_tab_ejecutores',
                't02.in_activo',
                'de_estatus',
                DB::raw("to_char(t03.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
                DB::raw("to_char(t03.fe_fin, 'dd/mm/YYYY') as fe_fin"),
                'nu_codigo',
                'de_ac',
                'de_lapso',
                'ac_seguimiento.tab_forma_001.in_001',
                't01.id_ejecutor',
                DB::raw("to_char(ac_seguimiento.tab_forma_001.created_at, 'dd/mm/YYYY hh12:mi AM') as fe_solicitud")
            )
            ->where('t01.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->whereIn('ac_seguimiento.tab_forma_001.id_tab_estatus',[5,6])
            ->where('t01.in_activo', '=', true);

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_forma_001->where('t01.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_forma_001->where('tx_ejecutor_ac', 'ILIKE', "%$variable%");
                }
                if($id_tab_tipo_periodo!="") {
                    $tab_forma_001->where('id_tab_tipo_periodo', '=', $id_tab_tipo_periodo);
                }                
                

                $response['success']  = 'true';
                $response['total'] = $tab_forma_001->count();
                $tab_forma_001->skip($start)->take($limit);
                $response['data']  = $tab_forma_001->orderby('t01.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_forma_001.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_forma_001->count();
                $tab_forma_001->skip($start)->take($limit);
                $response['data']  = $tab_forma_001->orderby('t01.id_ejecutor', 'ASC')->orderby('ac_seguimiento.tab_forma_001.id', 'ASC')->get()->toArray();
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
        $data = tab_forma_001::join('ac_seguimiento.tab_ac as t01', 'ac_seguimiento.tab_forma_001.id_tab_ac', '=', 't01.id')
        ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
        ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
        ->join('autenticacion.tab_usuarios as t04a', 'ac_seguimiento.tab_forma_001.id_usuario_solicita', '=', 't04a.id')
        ->leftJoin('autenticacion.tab_usuarios as t04b', 'ac_seguimiento.tab_forma_001.id_usuario_procesa', '=', 't04b.id')
        ->select(
            'ac_seguimiento.tab_forma_001.id',
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
            'ac_seguimiento.tab_forma_001.in_001',
            't01.id_ejecutor',
            DB::raw("to_char(ac_seguimiento.tab_forma_001.created_at, 'dd/mm/YYYY hh12:mi AM') as fe_solicitud")
        )
        ->where('ac_seguimiento.tab_forma_001.id', '=', Input::get('codigo'))
        ->first();

        return View::make('seguimiento.ac.001.cambio.detalle')->with('data', $data);
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function datosCambio($id)
    {
        $data = tab_forma_001::select(
            'id',
            'id_tab_ac',
            'inst_mision',
            'inst_vision',
            'inst_objetivos',
            'in_activo',
            'in_001',
            'created_at',
            'updated_at',
            'de_observacion',
            'nu_po_beneficiar',
            'nu_em_previsto',
            'nu_po_beneficiada',
            'nu_em_generado',                
            'id_usuario_solicita',
            'id_usuario_procesa'
        )
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.ac.001.cambio.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_ac::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_ac::find(Input::get("ac"));
                $tabla->in_001 = true;
                $tabla->save();

                $data = tab_ac::select(
                    'id'
                )
                ->where('id_ejecutor', '=', $tabla->id_ejecutor)
                ->where('id_tab_ejercicio_fiscal', '=', $tabla->id_tab_ejercicio_fiscal)
                ->where('id_tab_lapso', '=', $tabla->id_tab_lapso)
                ->get();                        
                

                foreach ($data as $lista){
                    

                $tabla_ac = tab_ac::find($lista->id);
                $tabla_ac->in_001 = true;
                $tabla_ac->save();  
 
                $data2 = tab_forma_001::select(
                    'id'
                )
                ->where('id_tab_ac', '=', $lista->id)       
                ->get(); 

                foreach ($data2 as $lista2){
              
                $tabla_001 = tab_forma_001::find($lista2->id);
                $tabla_001->in_001 = true;
                $tabla_001->id_tab_estatus = 6;
                $tabla_001->id_usuario_procesa = Auth::user()->id;
                $tabla_001->save();              
                }
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
                $tabla = new tab_forma_001();
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
                $validator= Validator::make(Input::all(), tab_ac::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_ac::find(Input::get("ac"));
                $tabla->in_001 = false;
                $tabla->in_bloquear_001 = false;
                $tabla->save();
                
                $data = tab_ac::select(
                    'id'
                )
                ->where('id_ejecutor', '=', $tabla->id_ejecutor)
                ->where('id_tab_ejercicio_fiscal', '=', $tabla->id_tab_ejercicio_fiscal)
                ->where('id_tab_lapso', '=', $tabla->id_tab_lapso)
                ->get();                        
                
                
                foreach ($data as $lista){
                    

                $tabla_ac = tab_ac::find($lista->id);
                $tabla_ac->in_001 = false;
                $tabla_ac->in_bloquear_001 = false;
                $tabla_ac->save();  

                $data2 = tab_forma_001::select(
                    'id'
                )
                ->where('id_tab_ac', '=', $lista->id)
                ->first();                                 
                
                $tabla_001 = tab_forma_001::find($data2->id);
                $tabla_001->in_001 = true;
                $tabla_001->id_tab_estatus = 7;
                $tabla_001->id_usuario_procesa = Auth::user()->id;
                $tabla_001->save();             
                    
                }                



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
                $tabla = new tab_forma_001();
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
            $tabla = tab_ac::find(Input::get("id"));
            $tabla->in_activo = false;
            $tabla->save();

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
    
    public function extender()
    {
        DB::beginTransaction();
        try {
            
                $tab_ac = tab_ac::select(
                    'id_tab_lapso','nu_codigo'
                )
                ->where('id', '=', Input::get("id"))
                ->first(); 
                
        $data_lapso = tab_lapso::where('id', '=', $tab_ac->id_tab_lapso)
        ->first();                
            
        $data_lapso_siguiente = tab_lapso::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'id_tab_periodo',
            'nu_lapso',
            'fe_inicio',
            'fe_fin',
            'in_activo',
            'de_lapso',
            'id_tab_tipo_periodo'
        )
        ->where('id_tab_ejercicio_fiscal', '=', $data_lapso->id_tab_ejercicio_fiscal)
        ->where('id_tab_periodo', '=', $data_lapso->id_tab_periodo)
        ->where('id', '>', $data_lapso->id)
        ->orderby('mantenimiento.tab_lapso.id', 'ASC')
        ->first();
        

        
        if($data_lapso_siguiente){     
            
            
        $data1_ac = tab_ac::where('nu_codigo', '=', $tab_ac->nu_codigo)
        ->where('id_tab_lapso', '=', $data_lapso_siguiente->id)
        ->where('in_activo', '=', true)
        ->first();  
        
        if($data1_ac){
            
 
            $response['success']  = 'true';
            $response['msg']  = 'El Siguiente periodo para la Ac seleccionada ya ah sido creado, Verifique!';
            return Response::json($response, 200);            
            
        }else{
            
            $tabla = tab_ac::find(Input::get("id"));
            $tabla->in_abierta = true;
            $tabla->in_002 = false;
            $tabla->in_003 = false;
            $tabla->save();
            
            $data = tab_ac_ae::select(
                'id as id_tab_ac_ae'
            )
            ->where('id_tab_ac', '=', Input::get("id"))
            ->get(); 
            
            foreach($data as $item) {

            $data2 = tab_meta_fisica::select(
                'id as id_tab_meta_fisica'
            )
            ->where('id_tab_ac_ae', '=', $item->id_tab_ac_ae)
            ->get();                 
                
            foreach($data2 as $item2) {
                
            $tabla2 = tab_meta_fisica::find($item2->id_tab_meta_fisica);
            $tabla2->in_cargado = false;
            $tabla2->in_bloquear_002 = false;
            $tabla2->id_tab_estatus = 1;
            $tabla2->save();
            
            $data3 = tab_meta_financiera::select(
                'id as id_tab_meta_financiera'
            )
            ->where('id_tab_meta_fisica', '=', $item2->id_tab_meta_fisica)
            ->get();

            foreach($data3 as $item3) {  

            $tabla3 = tab_meta_financiera::find($item3->id_tab_meta_financiera);
            $tabla3->in_cargado = false;
            $tabla3->in_enviado = false;
            $tabla3->id_tab_estatus = 1;
            $tabla3->save();                
                
            }          
                
            }    
            }
            
        }
            
        }else{
            
if($data_lapso->id_tab_tipo_periodo==22){
    
                $tabla = tab_ac::find(Input::get("id"));
            $tabla->in_abierta = true;
            $tabla->in_002 = false;
            $tabla->in_003 = false;
            $tabla->save();
            
            $data = tab_ac_ae::select(
                'id as id_tab_ac_ae'
            )
            ->where('id_tab_ac', '=', Input::get("id"))
            ->get(); 
            
            foreach($data as $item) {

            $data2 = tab_meta_fisica::select(
                'id as id_tab_meta_fisica'
            )
            ->where('id_tab_ac_ae', '=', $item->id_tab_ac_ae)
            ->get();                 
                
            foreach($data2 as $item2) {
                
            $tabla2 = tab_meta_fisica::find($item2->id_tab_meta_fisica);
            $tabla2->in_cargado = false;
            $tabla2->in_bloquear_002 = false;
            $tabla2->id_tab_estatus = 1;
            $tabla2->save();
            
            $data3 = tab_meta_financiera::select(
                'id as id_tab_meta_financiera'
            )
            ->where('id_tab_meta_fisica', '=', $item2->id_tab_meta_fisica)
            ->get();

            foreach($data3 as $item3) {  

            $tabla3 = tab_meta_financiera::find($item3->id_tab_meta_financiera);
            $tabla3->in_cargado = false;
            $tabla3->in_enviado = false;
            $tabla3->id_tab_estatus = 1;
            $tabla3->save();                
                
            }          
                
            }    
            }
    
}else{            
            
            $tabla = tab_ac::find(Input::get("id"));
            $tabla->in_abierta = true;
            $tabla->save();
}
            
        }

            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Proceso Realizado con Exito!';
            return Response::json($response, 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 200);
        }
    } 
    
    
    public function crearPeriodo()
    {
        DB::beginTransaction();
        try {
            
        $data = tab_lapso::where('id', '=', Input::get("id_tab_lapso"))
        ->first();  
        
        $data1 = tab_lapso::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'id_tab_periodo',
            'nu_lapso',
            'fe_inicio',
            'fe_fin',
            'in_activo',
            'de_lapso',
            'id_tab_tipo_periodo'
        )
        ->where('id_tab_ejercicio_fiscal', '=', $data->id_tab_ejercicio_fiscal)
        ->where('id_tab_periodo', '=', $data->id_tab_periodo)
        ->where('id', '>', $data->id)
        ->orderby('mantenimiento.tab_lapso.id', 'ASC')
        ->first();
        
        if($data1){
 
        $data_ac = tab_ac::where('id', '=', Input::get("id"))
        ->first();  
        
        $data1_ac = tab_ac::where('nu_codigo', '=', $data_ac->nu_codigo)
        ->where('id_tab_lapso', '=', $data1->id)
        ->where('in_activo', '=', true)
        ->first();  
        
        if($data1_ac){
 
            $response['success']  = 'true';
            $response['msg']  = 'El Siguiente periodo para la Ac seleccionada ya ah sido creado, Verifique!';
            return Response::json($response, 200);            
            
        }else{
            
        $tab_ac = tab_ac::where('id', '=', Input::get("id"))
        ->orderby('id_ejecutor', 'ASC')
        ->orderby('id_tab_ac_predefinida', 'ASC')
        ->get();            
            
        foreach ($tab_ac as $arreglo_ac) {
            
                $tabla = new tab_ac();
                $tabla->nu_codigo = $arreglo_ac->nu_codigo;
                $tabla->id_ejecutor = $arreglo_ac->id_ejecutor;
                $tabla->id_tab_ejecutores = $arreglo_ac->id_tab_ejecutores;
                $tabla->id_tab_ejercicio_fiscal = $arreglo_ac->id_tab_ejercicio_fiscal;
                $tabla->id_tab_ac_predefinida = $arreglo_ac->id_tab_ac_predefinida;
                $tabla->id_tab_sectores = $arreglo_ac->id_tab_sectores;
                $tabla->id_tab_estatus = $arreglo_ac->id_tab_estatus;
                $tabla->id_tab_situacion_presupuestaria = $arreglo_ac->id_tab_situacion_presupuestaria;
                $tabla->id_tab_tipo_registro = 1;
                $tabla->co_new_etapa = $arreglo_ac->co_new_etapa;
                $tabla->de_ac = $arreglo_ac->de_ac;
                $tabla->mo_ac = $arreglo_ac->mo_ac;
                $tabla->mo_calculado = $arreglo_ac->mo_calculado;
                $tabla->fe_inicio = $arreglo_ac->fe_inicio;
                $tabla->fe_fin = $arreglo_ac->fe_fin;
                $tabla->inst_mision = $arreglo_ac->inst_mision;
                $tabla->inst_vision = $arreglo_ac->inst_vision;
                $tabla->inst_objetivos = $arreglo_ac->inst_objetivos;
                $tabla->nu_po_beneficiar = $arreglo_ac->nu_po_beneficiar;
                $tabla->nu_em_previsto = $arreglo_ac->nu_em_previsto;
                $tabla->tx_re_esperado = $arreglo_ac->tx_re_esperado;
                $tabla->pp_anual = $arreglo_ac->pp_anual;
                $tabla->id_tab_lapso = $data1->id;
                $tabla->id_tab_origen = 1;
                $tabla->in_activo = 'TRUE';
                $tabla->in_001 = false;
                $tabla->in_005 = false;
                $tabla->in_bloquear_001 = false;
                $tabla->in_bloquear_005 = false;
                
                $tabla->de_observacion_001 = $arreglo_ac->de_observacion_001;
                $tabla->de_observacion_005 = $arreglo_ac->de_observacion_005;
                $tabla->nu_po_beneficiada = $arreglo_ac->nu_po_beneficiada;
                $tabla->nu_em_generado = $arreglo_ac->nu_em_generado;
                $tabla->tx_pr_objetivo = $arreglo_ac->tx_pr_objetivo;
                $tabla->tx_pr_programado = $arreglo_ac->tx_pr_programado;
                $tabla->de_observacion_003 = $arreglo_ac->de_observacion_003;
                $tabla->de_observacion_002 = $arreglo_ac->de_observacion_002;
                $tabla->tx_pr_obtenido_a = $arreglo_ac->tx_pr_obtenido_a;
                $tabla->de_sector = $arreglo_ac->de_sector; 
                $tabla->tx_ejecutor_ac = $arreglo_ac->tx_ejecutor_ac;
                
//                $tabla->id_accion_centralizada = $arreglo_ac->id;
                $tabla->save();  
                
            $tab_ac_ae = tab_ac_ae::where('id_tab_ac', '=', $arreglo_ac->id)
            ->orderby('id_tab_ac_ae_predefinida', 'ASC')
            ->get();
            
            foreach ($tab_ac_ae as $arreglo_ac_ae) {
                
                    $tabla_ac_ae= new tab_ac_ae();
                    $tabla_ac_ae->id_tab_ac = $tabla->id;
                    $tabla_ac_ae->id_tab_ac_ae_predefinida = $arreglo_ac_ae->id_tab_ac_ae_predefinida;
                    $tabla_ac_ae->id_ejecutor = $arreglo_ac_ae->id_ejecutor;
                    $tabla_ac_ae->id_tab_ejecutores = $arreglo_ac->id_tab_ejecutores;
                    $tabla_ac_ae->bien_servicio = $arreglo_ac_ae->bien_servicio;
                    $tabla_ac_ae->id_tab_unidad_medida = $arreglo_ac_ae->id_tab_unidad_medida;
                    $tabla_ac_ae->meta = $arreglo_ac_ae->meta;
                    $tabla_ac_ae->ponderacion = $arreglo_ac_ae->ponderacion;
                    $tabla_ac_ae->id_tab_tipo_fondo = $arreglo_ac_ae->id_tab_tipo_fondo;
                    $tabla_ac_ae->mo_ae = $arreglo_ac_ae->mo_ae;
                    $tabla_ac_ae->mo_ae_calculado = $arreglo_ac_ae->mo_ae_calculado;
                    $tabla_ac_ae->fecha_inicio = $arreglo_ac_ae->fecha_inicio;
                    $tabla_ac_ae->fecha_fin = $arreglo_ac_ae->fecha_fin;
                    $tabla_ac_ae->objetivo_institucional = $arreglo_ac_ae->objetivo_institucional;
                    $tabla_ac_ae->id_tab_origen = 1;
                    $tabla_ac_ae->in_activo = 'TRUE';
//                    $tabla_ac_ae->id_accion_centralizada = $arreglo_ac_ae->id_accion_centralizada;
                    $tabla_ac_ae->save(); 
                    
            $tab_ac_ae_fuente = tab_ac_ae_fuente::where('id_tab_ac_ae', '=', $arreglo_ac_ae->id)
            ->orderby('id', 'ASC')
            ->get();  
            
                    foreach ($tab_ac_ae_fuente as $arreglo_ac_ae_fuente) {
                
                    $tabla_ac_ae_fuente = new tab_ac_ae_fuente();
                    $tabla_ac_ae_fuente->id_tab_ac_ae = $tabla_ac_ae->id;
                    $tabla_ac_ae_fuente->id_tab_tipo_fondo = $arreglo_ac_ae_fuente->id_tab_tipo_fondo;
                    $tabla_ac_ae_fuente->mo_fondo = $arreglo_ac_ae_fuente->mo_fondo;
                    $tabla_ac_ae_fuente->in_activo = 'TRUE';
                    $tabla_ac_ae_fuente->save(); 
                    
                        }
                        
            $tab_meta_fisica = tab_meta_fisica::where('id_tab_ac_ae', '=', $arreglo_ac_ae->id)
            ->orderby('id', 'ASC')
            ->get();  
            
                    foreach ($tab_meta_fisica as $arreglo_meta_fisica) {
                
                    $tabla_meta_fisica = new tab_meta_fisica();
                    $tabla_meta_fisica->id_tab_ac_ae = $tabla_ac_ae->id;
                    $tabla_meta_fisica->codigo = $arreglo_meta_fisica->codigo;
                    $tabla_meta_fisica->nb_meta = $arreglo_meta_fisica->nb_meta;
                    $tabla_meta_fisica->id_tab_unidad_medida = $arreglo_meta_fisica->id_tab_unidad_medida;
                    $tabla_meta_fisica->tx_prog_anual = $arreglo_meta_fisica->tx_prog_anual;
//                    $tabla_meta_fisica->tx_prog_anual = 0;
                    $tabla_meta_fisica->fecha_inicio = $arreglo_meta_fisica->fecha_inicio;
                    $tabla_meta_fisica->fecha_fin = $arreglo_meta_fisica->fecha_fin;
                    $tabla_meta_fisica->nb_responsable = $arreglo_meta_fisica->nb_responsable;
                    $tabla_meta_fisica->id_tab_origen = 1;
                    $tabla_meta_fisica->nu_meta_modificada_periodo = ($arreglo_meta_fisica->nu_meta_modificada + $arreglo_meta_fisica->nu_meta_modificada_periodo);
//                    $tabla_meta_fisica->nu_meta_modificada_periodo = 0;
//                    $tabla_meta_fisica->nu_meta_modificada = $arreglo_meta_fisica->nu_meta_actualizada;
                    $tabla_meta_fisica->nu_meta_actualizada = $arreglo_meta_fisica->nu_meta_actualizada;
                    $tabla_meta_fisica->id_tab_municipio_detalle = $arreglo_meta_fisica->id_tab_municipio_detalle;
                    $tabla_meta_fisica->id_tab_parroquia_detalle = $arreglo_meta_fisica->id_tab_parroquia_detalle;
//                    $tabla_meta_fisica->de_desvio = $arreglo_meta_fisica->de_desvio;
                    $tabla_meta_fisica->resultado = $arreglo_meta_fisica->resultado;
                    $tabla_meta_fisica->observacion = $arreglo_meta_fisica->observacion;
                    $tabla_meta_fisica->in_cargado = 'FALSE';
                    $tabla_meta_fisica->in_activo = 'TRUE';
                    $tabla_meta_fisica->save(); 
                    
                    
            $tab_meta_financiera = tab_meta_financiera::where('id_tab_meta_fisica', '=', $arreglo_meta_fisica->id)
            ->orderby('id', 'ASC')
            ->get();  
            
                    foreach ($tab_meta_financiera as $arreglo_meta_financiera) {
                
                    $tab_meta_financiera = new tab_meta_financiera();
                    $tab_meta_financiera->id_tab_meta_fisica = $tabla_meta_fisica->id;
                    $tab_meta_financiera->id_tab_municipio_detalle = $arreglo_meta_financiera->id_tab_municipio_detalle;
                    $tab_meta_financiera->id_tab_parroquia_detalle = $arreglo_meta_financiera->id_tab_parroquia_detalle;
                    $tab_meta_financiera->mo_presupuesto = $arreglo_meta_financiera->mo_presupuesto;
//                    $tab_meta_financiera->mo_presupuesto = 0;
                    $tab_meta_financiera->co_partida = $arreglo_meta_financiera->co_partida;
                    $tab_meta_financiera->id_tab_fuente_financiamiento = $arreglo_meta_financiera->id_tab_fuente_financiamiento;
//                    $tab_meta_financiera->mo_modificado_anual = $arreglo_meta_financiera->mo_actualizado_anual;
                    $tab_meta_financiera->mo_actualizado_anual = $arreglo_meta_financiera->mo_actualizado_anual;
                    $tab_meta_financiera->mo_modificado = ($arreglo_meta_financiera->mo_modificado_anual + $arreglo_meta_financiera->mo_modificado);
//                    $tab_meta_financiera->mo_modificado = 0;
                    $tab_meta_financiera->id_tab_origen = 1;
                    $tab_meta_financiera->in_cargado = 'FALSE';
                    $tab_meta_financiera->in_activo = 'TRUE';
                    $tab_meta_financiera->save(); 
                    
                        }                     
                    
                    
                    
                    
                        }                        
                    
                                
            }
            
            $tab_ac_localizacion = tab_ac_localizacion::where('id_tab_ac', '=', $arreglo_ac->id)
            ->orderby('id', 'ASC')
            ->get();  
            
                    foreach ($tab_ac_localizacion as $arreglo_tab_ac_localizacion) {
                
                    $tab_ac_localizacion = new tab_ac_localizacion();
                    $tab_ac_localizacion->id_tab_ac = $tabla->id;
                    $tab_ac_localizacion->id_tab_municipio = $arreglo_tab_ac_localizacion->id_tab_municipio;
                    $tab_ac_localizacion->id_tab_parroquia = $arreglo_tab_ac_localizacion->id_tab_parroquia;
                    $tab_ac_localizacion->in_activo = 'TRUE';
                    $tab_ac_localizacion->save(); 
                    
                        }            
            
            $tab_ac_vinculo = tab_ac_vinculo::where('id_tab_ac', '=', $arreglo_ac->id)
            ->first(); 
                        
            if($tab_ac_vinculo){
            
                    $tab_vinculo = new tab_ac_vinculo();
                    $tab_vinculo->id_tab_ac = $tabla->id;
                    $tab_vinculo->co_area_estrategica = $tab_ac_vinculo->co_area_estrategica;
                    $tab_vinculo->co_ambito_estado = $tab_ac_vinculo->co_ambito_estado;
                    $tab_vinculo->co_objetivo_estado = $tab_ac_vinculo->co_objetivo_estado;
                    $tab_vinculo->co_macroproblema = $tab_ac_vinculo->co_macroproblema;
                    $tab_vinculo->co_nodos = $tab_ac_vinculo->co_nodos;
                    $tab_vinculo->co_objetivo_historico = $tab_ac_vinculo->co_objetivo_historico;
                    $tab_vinculo->co_objetivo_nacional = $tab_ac_vinculo->co_objetivo_nacional;
                    $tab_vinculo->co_objetivo_estrategico = $tab_ac_vinculo->co_objetivo_estrategico;
                    $tab_vinculo->co_objetivo_general = $tab_ac_vinculo->co_objetivo_general;                  
                    $tab_vinculo->in_activo = 'TRUE';
                    $tab_vinculo->save();   
                    
            }
                    $tab_ac_responsable = tab_ac_responsable::where('id_tab_ac', '=', $arreglo_ac->id)
                    ->first(); 
               
                    if($tab_ac_responsable){
                    
                    $tab_responsable = new tab_ac_responsable();
                    $tab_responsable->id_tab_ac = $tabla->id;
                    $tab_responsable->realizador_nombres = $tab_ac_responsable->realizador_nombres;
                    $tab_responsable->realizador_cedula = $tab_ac_responsable->realizador_cedula;
                    $tab_responsable->realizador_cargo = $tab_ac_responsable->realizador_cargo;
                    $tab_responsable->realizador_correo = $tab_ac_responsable->realizador_correo;
                    $tab_responsable->realizador_telefono = $tab_ac_responsable->realizador_telefono;
                    $tab_responsable->realizador_unidad = $tab_ac_responsable->realizador_unidad;
                    $tab_responsable->registrador_nombres = $tab_ac_responsable->registrador_nombres;
                    $tab_responsable->registrador_cedula = $tab_ac_responsable->registrador_cedula;
                    $tab_responsable->registrador_cargo = $tab_ac_responsable->registrador_cargo;
                    $tab_responsable->registrador_correo = $tab_ac_responsable->registrador_correo;
                    $tab_responsable->registrador_telefono = $tab_ac_responsable->registrador_telefono;
                    $tab_responsable->registrador_unidad = $tab_ac_responsable->registrador_unidad;
                    $tab_responsable->autorizador_nombres = $tab_ac_responsable->autorizador_nombres;
                    $tab_responsable->autorizador_cedula = $tab_ac_responsable->autorizador_cedula;
                    $tab_responsable->autorizador_cargo = $tab_ac_responsable->autorizador_cargo;
                    $tab_responsable->autorizador_correo = $tab_ac_responsable->autorizador_correo;
                    $tab_responsable->autorizador_telefono = $tab_ac_responsable->autorizador_telefono;
                    $tab_responsable->autorizador_unidad = $tab_ac_responsable->autorizador_unidad;
                    $tab_responsable->in_activo = 'TRUE';
                    $tab_responsable->save(); 
                    
                    }
                    
                    $tab_ac_forma_005 = tab_forma_005::where('id_tab_ac', '=', $arreglo_ac->id)
                    ->get(); 
               
                    foreach ($tab_ac_forma_005 as $arreglo_tab_forma_005) {
                
                    $tab_forma_005 = new tab_forma_005();
                    $tab_forma_005->id_tab_ac = $tabla->id;
                    $tab_forma_005->pp_anual = $arreglo_tab_forma_005->pp_anual;
                    $tab_forma_005->tp_indicador = $arreglo_tab_forma_005->tp_indicador;
                    $tab_forma_005->nb_indicador_gestion = $arreglo_tab_forma_005->nb_indicador_gestion;
                    $tab_forma_005->de_valor_obtenido = $arreglo_tab_forma_005->de_valor_obtenido;
                    $tab_forma_005->de_valor_objetivo = $arreglo_tab_forma_005->de_valor_objetivo;
                    $tab_forma_005->nu_cumplimiento = $arreglo_tab_forma_005->nu_cumplimiento;
                    $tab_forma_005->de_indicador_descripcion = $arreglo_tab_forma_005->de_indicador_descripcion;
                    $tab_forma_005->de_formula = $arreglo_tab_forma_005->de_formula;
                    $tab_forma_005->in_005 = $arreglo_tab_forma_005->in_005;
                    $tab_forma_005->de_observacion = $arreglo_tab_forma_005->de_observacion;
                    $tab_forma_005->id_usuario_solicita = $arreglo_tab_forma_005->id_usuario_solicita;
                    $tab_forma_005->id_usuario_procesa = $arreglo_tab_forma_005->id_usuario_procesa;
                    $tab_forma_005->id_tab_estatus = $arreglo_tab_forma_005->id_tab_estatus;
                    $tab_forma_005->de_valor_objetivo_acu = $arreglo_tab_forma_005->de_valor_objetivo_acu;
                    $tab_forma_005->de_valor_obtenido_acu = $arreglo_tab_forma_005->de_valor_obtenido_acu;                    
                    $tab_forma_005->in_activo = 'TRUE';
                    $tab_forma_005->save(); 
                    
                        }                    

        }         
        
            
        }
                    
            
        }else{
            
            $response['success']  = 'true';
            $response['msg']  = 'No existe un periodo siguente, Verifique!';
            return Response::json($response, 200);             
            
        }
        

            
            $tabla = tab_ac::find(Input::get("id"));
            $tabla->in_abierta = false;
            $tabla->save();

            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Proceso Realizado con Exito!';
            return Response::json($response, 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 200);
        }
    }    

}
