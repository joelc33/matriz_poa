<?php

namespace matriz\Http\Controllers\PrSeguimiento;

//*******agregar esta linea******//
use matriz\Models\ProySegto\tab_proyecto;
use matriz\Models\ProySegto\tab_proyecto_ae;
use matriz\Models\Mantenimiento\tab_lapso;
use matriz\Models\Proyecto\tab_proyecto as proyecto;
use matriz\Models\Proyecto\tab_proyecto_ae as proyecto_ae;
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

class proyectoController extends Controller
{
    protected $tab_proyecto;

    public function __construct(tab_proyecto $tab_proyecto)
    {
        $this->middleware('auth');
        $this->tab_proyecto = $tab_proyecto;
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('seguimiento.proyecto.lista');
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
                'de_nombre',
                'proyecto_seguimiento.tab_proyecto.id_ejecutor'
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
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function nuevo()
    {
        $data = json_encode(array("id" => ""));
        return View::make('seguimiento.proyecto.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function disponible()
    {

        $excluir = tab_proyecto::select('nu_codigo')
        ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
        ->where('id_tab_lapso', '=', Input::get('periodo'))
        ->get()->toArray();

        $tab_proyecto = proyecto::join('mantenimiento.tab_ejecutores as t01', 'public.t26_proyectos.id_ejecutor', '=', 't01.id_ejecutor')
        ->select(
            'public.t26_proyectos.co_proyectos',
            'public.t26_proyectos.id_ejecutor',
            'id_ejercicio',
            'codigo_new_etapa',
            'nombre as de_nombre',
            'tx_ejecutor',
            DB::raw("id_proyecto as codigo")
        )
        ->where('edo_reg', '=', true)
        ->where('co_estatus', '=', 3)
        ->whereNotIn(DB::raw("id_proyecto"), $excluir)
        ->where('id_ejercicio', '=', Session::get('ejercicio'));

        $rol_planificador = array(3, 8);
        if (in_array(Session::get('rol'), $rol_planificador)) {
            $tab_proyecto->where('public.t26_proyectos.id_ejecutor', '=', Session::get('ejecutor'));
        }

        $response['success']  = 'true';
        $response['data']  = $tab_proyecto->orderby('public.t26_proyectos.id_ejecutor', 'ASC')
        ->orderby('public.t26_proyectos.id_proyecto', 'ASC')
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
                $validator= Validator::make(Input::all(), tab_proyecto::$validarEditar);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = tab_proyecto::find($id);
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

            $tab_proyecto = proyecto::select(
                'co_proyectos',
                'id_ejercicio',
                'id_ejecutor',
                'id_proyecto',
                'tipo_registro',
                'nombre',
                'status_registro',
                'codigo_new_etapa',
                'fecha_inicio',
                'fecha_fin',
                'objetivo',
                'descripcion',
                'sit_presupuesto',
                'monto',
                'clase_sector',
                'clase_subsector',
                'plan_operativo',
                'co_estatus',
                'edo_reg',
                'fecha_creacion',
                'fecha_actualizacion',
                'id_tab_ejecutor'
            )
            ->where('edo_reg', '=', true)
            ->where('co_estatus', '=', 3)
            ->where('public.t26_proyectos.co_proyectos', '=', Input::get('proyecto'))
            ->where('id_ejercicio', '=', $data->id_tab_ejercicio_fiscal)
            ->first();

            $tab_proyecto_ae = proyecto_ae::select(
                'co_proyecto_acc_espec',
                'tx_codigo',
                'id_proyecto',
                'descripcion',
                'co_unidades_medida',
                'meta',
                'ponderacion',
                'bien_servicio',
                'total',
                'fec_inicio',
                'fec_termino',
                'co_ejecutores',
                'fecha_creacion',
                'fecha_actualizacion',
                'edo_reg',
                'tx_objetivo_institucional',
                'id_padre',
                'in_definitivo',
                'id_ejecutor'
            )
            ->join('mantenimiento.tab_ejecutores as t01', 'public.t39_proyecto_acc_espec.co_ejecutores', '=', 't01.id')
            ->where('id_proyecto', '=', $tab_proyecto->id_proyecto)
            ->where('edo_reg', '=', true)
            ->orderby('tx_codigo', 'ASC')
            ->get();

            try {
                $validator = Validator::make(Input::all(), tab_proyecto::$validarCrear);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }
                $tabla = new tab_proyecto();
                $tabla->nu_codigo = $tab_proyecto->id_proyecto;
                $tabla->id_ejecutor = $tab_proyecto->id_ejecutor;
                $tabla->id_tab_ejecutores = $tab_proyecto->id_tab_ejecutor;
                $tabla->id_tab_ejercicio_fiscal = $tab_proyecto->id_ejercicio;
                $tabla->clase_sector = $tab_proyecto->clase_sector;
                $tabla->clase_subsector = $tab_proyecto->clase_subsector;
                $tabla->plan_operativo = $tab_proyecto->plan_operativo;
                $tabla->id_tab_estatus_proyecto = $tab_proyecto->status_registro;
                $tabla->id_tab_estatus = $tab_proyecto->co_estatus;
                $tabla->id_tab_situacion_presupuestaria = $tab_proyecto->sit_presupuesto;
                $tabla->id_tab_tipo_registro = 1;
                $tabla->co_new_etapa = $tab_proyecto->codigo_new_etapa;
                $tabla->de_nombre = $tab_proyecto->nombre;
                $tabla->mo_proyecto = $tab_proyecto->monto;
                $tabla->fe_inicio = $data->fe_inicio;
                $tabla->fe_fin = $data->fe_fin;
                $tabla->de_objetivo = $tab_proyecto->objetivo;
                $tabla->de_proyecto = $tab_proyecto->descripcion;
                $tabla->id_tab_lapso = $data->id;
                $tabla->id_tab_origen = 1;
                $tabla->in_activo = 'TRUE';
                $tabla->in_001 = false;
                $tabla->in_005 = false;
                $tabla->in_bloquear_001 = false;
                $tabla->in_bloquear_005 = false;
                $tabla->save();

                foreach ($tab_proyecto_ae as $arreglo_proyecto_ae) {

                    $tab_proyecto_ae= new tab_proyecto_ae();
                    $tab_proyecto_ae->id_tab_proyecto = $tabla->id;
                    $tab_proyecto_ae->tx_codigo = $arreglo_proyecto_ae->tx_codigo;
                    $tab_proyecto_ae->descripcion = $arreglo_proyecto_ae->descripcion;
                    $tab_proyecto_ae->id_tab_unidad_medida = $arreglo_proyecto_ae->co_unidades_medida;
                    $tab_proyecto_ae->meta = $arreglo_proyecto_ae->meta;
                    $tab_proyecto_ae->ponderacion = $arreglo_proyecto_ae->ponderacion;
                    $tab_proyecto_ae->bien_servicio = $arreglo_proyecto_ae->bien_servicio;
                    $tab_proyecto_ae->mo_total = $arreglo_proyecto_ae->total;
                    $tab_proyecto_ae->fe_ini = $arreglo_proyecto_ae->fec_inicio;
                    $tab_proyecto_ae->fe_fin = $arreglo_proyecto_ae->fec_termino;
                    $tab_proyecto_ae->id_tab_ejecutores = $arreglo_proyecto_ae->co_ejecutores;
                    $tab_proyecto_ae->tx_objetivo_institucional = $arreglo_proyecto_ae->tx_objetivo_institucional;
                    $tab_proyecto_ae->id_ejecutor = $arreglo_proyecto_ae->id_ejecutor;
                    $tab_proyecto_ae->id_tab_origen = 1;
                    $tab_proyecto_ae->in_activo = 'TRUE';
                    $tab_proyecto_ae->save();

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

        return View::make('seguimiento.proyecto.detalle')->with('data', $data);
    }

}
