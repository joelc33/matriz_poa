<?php

namespace matriz\Http\Controllers\PrSeguimiento;

//*******agregar esta linea******//
use matriz\Models\ProySegto\tab_proyecto;
use matriz\Models\ProySegto\tab_forma_001;
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
    protected $tab_proyecto;
    protected $tab_forma_001;

    public function __construct(tab_proyecto $tab_proyecto, tab_forma_001 $tab_forma_001)
    {
        $this->middleware('auth');
        $this->tab_proyecto = $tab_proyecto;
        $this->tab_forma_001 = $tab_forma_001;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('seguimiento.proyecto.001.lista');
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

            $tab_proyecto = $this->tab_proyecto
            ->join('mantenimiento.tab_ejecutores as t01', 'proyecto_seguimiento.tab_proyecto.id_tab_ejecutores', '=', 't01.id')
            ->join('mantenimiento.tab_lapso as t02', 'proyecto_seguimiento.tab_proyecto.id_tab_lapso', '=', 't02.id')
            ->select(
                'proyecto_seguimiento.tab_proyecto.id',
                'tx_ejecutor',
                'proyecto_seguimiento.tab_proyecto.id_tab_ejecutores',
                'proyecto_seguimiento.tab_proyecto.in_activo',
                DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
                DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
                'nu_codigo',
                'de_nombre as de_proyecto',
                'proyecto_seguimiento.tab_proyecto.id_ejecutor',
                'in_001'
            )
            ->where('proyecto_seguimiento.tab_proyecto.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('proyecto_seguimiento.tab_proyecto.in_activo', '=', true);

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_proyecto->where('proyecto_seguimiento.tab_proyecto.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_proyecto->where('tx_ejecutor', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_proyecto->count();
                $tab_proyecto->skip($start)->take($limit);
                $response['data']  = $tab_proyecto->orderby('proyecto_seguimiento.tab_proyecto.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_proyecto->count();
                $tab_proyecto->skip($start)->take($limit);
                $response['data']  = $tab_proyecto->orderby('proyecto_seguimiento.tab_proyecto.id', 'ASC')->get()->toArray();
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
        $data = tab_proyecto::join('mantenimiento.tab_ejecutores as t01', 'proyecto_seguimiento.tab_proyecto.id_tab_ejecutores', '=', 't01.id')
        ->join('mantenimiento.tab_lapso as t02', 'proyecto_seguimiento.tab_proyecto.id_tab_lapso', '=', 't02.id')
        ->select(
            'proyecto_seguimiento.tab_proyecto.id',
            'tx_ejecutor',
            'proyecto_seguimiento.tab_proyecto.id_tab_ejecutores',
            'proyecto_seguimiento.tab_proyecto.in_activo',
            DB::raw("to_char(t02.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t02.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            'nu_codigo',
            'de_nombre as de_proyecto'
        )
        ->where('proyecto_seguimiento.tab_proyecto.id', '=', Input::get('codigo'))
        ->first();

        return View::make('seguimiento.proyecto.001.detalle')->with('data', $data);
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function datos($id)
    {
        $data = tab_proyecto::select(
            'id',
            'nu_codigo',
            'id_tab_ejercicio_fiscal',
            'id_ejecutor',
            'id_tab_ejecutores',
            'id_tab_tipo_registro',
            'de_nombre',
            'id_tab_estatus_proyecto',
            'co_new_etapa',
            'fe_inicio',
            'fe_fin',
            'de_objetivo',
            'de_proyecto',
            'id_tab_situacion_presupuestaria',
            'mo_proyecto',
            'clase_sector',
            'clase_subsector',
            'plan_operativo',
            'id_tab_estatus',
            'in_activo',
            'created_at',
            'updated_at',
            'id_tab_lapso',
            'id_tab_origen',
            'in_001',
            'in_005',
            'in_bloquear_001',
            'in_bloquear_005',
            'de_observacion_001'
        )
        ->where('id', '=', $id)
        ->first();

        if (tab_forma_001::where('id_tab_proyecto', '=', $id)
        ->where('id_tab_estatus', '=', 5)
        ->where('in_001', '=', false)->exists()) {

            $data = tab_forma_001::select(
                'id',
                'id_tab_proyecto',
                'de_objetivo',
                'de_proyecto',
                'in_001',
                'created_at',
                'updated_at',
                'de_observacion as de_observacion_001',
                'id_usuario_solicita',
                'id_usuario_procesa',
                'id_tab_estatus',
                'in_activo as in_bloquear_001'
            )
            ->where('id_tab_proyecto', '=', $id)
            ->where('id_tab_estatus', '=', 5)
            ->where('in_001', '=', false)
            ->first();

        }

        return View::make('seguimiento.proyecto.001.datos.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_proyecto::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_proyecto::find($id);
                /*$tabla->inst_mision = Input::get("mision");
                $tabla->inst_vision = Input::get("vision");
                $tabla->inst_objetivos = Input::get("objetivos");*/
                $tabla->in_001 = true;
                $tabla->in_bloquear_001 = true;
                $tabla->de_observacion_001 = Input::get("observacion");
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
                $validator = Validator::make(Input::all(), tab_proyecto::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_proyecto();
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
    public function enviar($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validator= Validator::make(Input::all(), tab_proyecto::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_proyecto::find($id);
                $tabla->in_bloquear_001 = true;
                $tabla->de_observacion_001 = Input::get("observacion");
                $tabla->save();

                $tabla_001 = new tab_forma_001();
                $tabla_001->id_tab_proyecto = $id;
                $tabla_001->de_objetivo = Input::get("objetivo");
                $tabla_001->de_proyecto = Input::get("descripcion");
                $tabla_001->de_observacion = Input::get("observacion");
                $tabla_001->in_001 = false;
                $tabla_001->id_usuario_solicita = Auth::user()->id;
                $tabla_001->in_activo = true;
                $tabla_001->id_tab_estatus = 5;
                $tabla_001->save();

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
                $validator = Validator::make(Input::all(), tab_proyecto::$validarCrear);
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
        return View::make('seguimiento.proyecto.001.cambio.lista');
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

            $tab_forma_001 = $this->tab_forma_001
            ->join('proyecto_seguimiento.tab_proyecto as t01', 'proyecto_seguimiento.tab_forma_001.id_tab_proyecto', '=', 't01.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
            ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
            ->join('mantenimiento.tab_estatus as t04', 't04.id', '=', 'proyecto_seguimiento.tab_forma_001.id_tab_estatus')
            ->select(
                'proyecto_seguimiento.tab_forma_001.id',
                'tx_ejecutor',
                't01.id_tab_ejecutores',
                't02.in_activo',
                'de_estatus',
                DB::raw("to_char(t03.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
                DB::raw("to_char(t03.fe_fin, 'dd/mm/YYYY') as fe_fin"),
                'nu_codigo',
                'proyecto_seguimiento.tab_forma_001.de_proyecto',
                'proyecto_seguimiento.tab_forma_001.in_001',
                't01.id_ejecutor',
                DB::raw("to_char(proyecto_seguimiento.tab_forma_001.created_at, 'dd/mm/YYYY hh12:mi AM') as fe_solicitud")
            )
            ->where('t01.id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
            ->where('t01.in_activo', '=', true);

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_forma_001->where('t01.id_tab_ejecutores', '=', Session::get('id_tab_ejecutores'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_forma_001->where('nu_codigo', 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_forma_001->count();
                $tab_forma_001->skip($start)->take($limit);
                $response['data']  = $tab_forma_001->orderby('proyecto_seguimiento.tab_forma_001.id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_forma_001->count();
                $tab_forma_001->skip($start)->take($limit);
                $response['data']  = $tab_forma_001->orderby('proyecto_seguimiento.tab_forma_001.id', 'ASC')->get()->toArray();
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
        $data = tab_forma_001::join('proyecto_seguimiento.tab_proyecto as t01', 'proyecto_seguimiento.tab_forma_001.id_tab_proyecto', '=', 't01.id')
        ->join('mantenimiento.tab_ejecutores as t02', 't01.id_tab_ejecutores', '=', 't02.id')
        ->join('mantenimiento.tab_lapso as t03', 't01.id_tab_lapso', '=', 't03.id')
        ->join('autenticacion.tab_usuarios as t04a', 'proyecto_seguimiento.tab_forma_001.id_usuario_solicita', '=', 't04a.id')
        ->leftJoin('autenticacion.tab_usuarios as t04b', 'proyecto_seguimiento.tab_forma_001.id_usuario_procesa', '=', 't04b.id')
        ->select(
            'proyecto_seguimiento.tab_forma_001.id',
            'tx_ejecutor',
            't01.id_tab_ejecutores',
            't02.in_activo',
            't04a.da_login as da_login_a',
            't04b.da_login as da_login_b',
            DB::raw("to_char(t03.fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(t03.fe_fin, 'dd/mm/YYYY') as fe_fin"),
            'nu_codigo',
            'proyecto_seguimiento.tab_forma_001.de_observacion',
            'proyecto_seguimiento.tab_forma_001.de_proyecto',
            'proyecto_seguimiento.tab_forma_001.in_001',
            't01.id_ejecutor',
            'de_nombre',
            DB::raw("to_char(proyecto_seguimiento.tab_forma_001.created_at, 'dd/mm/YYYY hh12:mi AM') as fe_solicitud")
        )
        ->where('proyecto_seguimiento.tab_forma_001.id', '=', Input::get('codigo'))
        ->first();

        return View::make('seguimiento.proyecto.001.cambio.detalle')->with('data', $data);
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
            'id_tab_proyecto',
            'de_objetivo',
            'de_proyecto',
            'in_activo',
            'in_001',
            'created_at',
            'updated_at',
            'de_observacion',
            'id_usuario_solicita',
            'id_usuario_procesa',
            'id_tab_estatus'
        )
        ->where('id', '=', $id)
        ->first();

        return View::make('seguimiento.proyecto.001.cambio.editar')->with('data', $data);
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
                $validator= Validator::make(Input::all(), tab_proyecto::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_proyecto::find(Input::get("proyecto"));
                $tabla->de_objetivo = Input::get("objetivo");
                $tabla->de_proyecto = Input::get("descripcion");
                $tabla->in_001 = true;
                $tabla->save();

                $tabla_001 = tab_forma_001::find($id);
                $tabla_001->in_001 = true;
                $tabla_001->id_tab_estatus = 6;
                $tabla_001->id_usuario_procesa = Auth::user()->id;
                $tabla_001->save();

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
                $validator = Validator::make(Input::all(), tab_proyecto::$validarCrear);
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
                $validator= Validator::make(Input::all(), tab_proyecto::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_proyecto::find(Input::get("proyecto"));
                $tabla->in_001 = false;
                $tabla->in_bloquear_001 = false;
                $tabla->save();

                $tabla_001 = tab_forma_001::find($id);
                $tabla_001->in_001 = true;
                $tabla_001->id_tab_estatus = 7;
                $tabla_001->id_usuario_procesa = Auth::user()->id;
                $tabla_001->save();

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
                $validator = Validator::make(Input::all(), tab_proyecto::$validarCrear);
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

}
