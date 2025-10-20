<?php

namespace matriz\Http\Controllers\Proyecto;

//*******agregar esta linea******//
use matriz\Models\Proyecto\tab_proyecto;
use matriz\Models\Proyecto\tab_proyecto_vinculo;
use matriz\Models\Proyecto\tab_proyecto_localizacion;
use matriz\Models\Proyecto\tab_proyecto_responsable;
use matriz\Models\Proyecto\tab_proyecto_ae;
use matriz\Models\Proyecto\tab_proyecto_ae_partida;
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
    public function storeLista()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 10);
            $variable = Input::get('variable');

            $tab_proyecto = $this->tab_proyecto
            ->join('mantenimiento.tab_ejecutores as t01', 'public.t26_proyectos.id_ejecutor', '=', 't01.id_ejecutor')
            ->join('mantenimiento.tab_estatus as t02', 'public.t26_proyectos.co_estatus', '=', 't02.id')
            ->select(
                'co_proyectos',
                'id_ejercicio',
                'id_proyecto',
                'nombre',
                'monto',
                'tx_ejecutor',
                'de_estatus as tx_estatus',
                DB::raw("monto_cargado(id_proyecto) as mo_registrado"),
                DB::raw("coalesce(null, co_estatus = 3) as reabrir"),
                DB::raw("coalesce(null, co_estatus = 1) as eliminar")
            )
            ->where('edo_reg', '=', true)
            ->where('id_ejercicio', '=', Session::get('ejercicio'));

            $rol_planificador = array(3, 8);
            if (in_array(Session::get('rol'), $rol_planificador)) {
                $tab_proyecto->where('public.t26_proyectos.id_ejecutor', '=', Session::get('ejecutor'));
            }

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    //$tab_proyecto->where('tx_ejecutor', 'ILIKE', "%$variable%");
                    $tab_proyecto->whereRaw("tx_ejecutor||nombre||id_proyecto ILIKE '%".$variable."%'");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_proyecto->count();
                $tab_proyecto->skip($start)->take($limit);
                $response['data']  = $tab_proyecto->orderby('id_proyecto', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_proyecto->count();
                $tab_proyecto->skip($start)->take($limit);
                $response['data']  = $tab_proyecto->orderby('id_proyecto', 'ASC')->get()->toArray();
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
    public function abrir()
    {
        DB::beginTransaction();
        try {
            $tabla = tab_proyecto::find(Input::get("proyecto"));
            $tabla->co_estatus = 1;
            $tabla->save();
            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Proyecto Reaperturado con Exito!';
            return Response::json($response, 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function cerrar()
    {

        $co_proyectos = Input::get("codigo");
        $id_proyecto = Input::get("proyecto");

        DB::beginTransaction();
        try {

            $t26_proyectos = tab_proyecto::select('co_proyectos', 'id_ejercicio', 'id_ejecutor', 'id_proyecto', 'tipo_registro', 'nombre', 'status_registro', 'codigo_new_etapa', 'fecha_inicio', 'fecha_fin', 'objetivo', 'descripcion', 'sit_presupuesto', 'monto', 'clase_sector', 'clase_subsector', 'plan_operativo', 'co_estatus', 'edo_reg', DB::raw("monto_cargado(id_proyecto) as mo_cargado"))
            ->where('co_proyectos', '=', $co_proyectos)
            ->first();

            if($t26_proyectos->monto == $t26_proyectos->mo_cargado) {
                $in_valido = 1;
            } else {
                $in_valido = 0;
            }

            $mensaje_proy_ae = array(
              'ae_cuadra.in'=>'El monto Cargado de AE, No Coincide con el monto del Proyecto. <br>Monto Proyecto: <span style="color:green"><b>'.number_format($t26_proyectos->monto, 2, ',', '.').'</b></span>'.'<br>Monto Cargado AE: <span style="color:red"><b>'.number_format($t26_proyectos->mo_cargado, 2, ',', '.').'</b></span>'.'<br>Diferencia: <b>'.number_format(($t26_proyectos->monto - $t26_proyectos->mo_cargado), 2, ',', '.').'</b>'
            );

            $t32_proyecto_vinculos = tab_proyecto_vinculo::select('co_proyecto_vinculos', 'id_proyecto', 'id_obj_historico', 'id_obj_nacional', 'id_ob_estrategico', 'id_obj_general', 'co_area_estrategica', 'co_ambito_estado', 'co_objetivo_estado', 'co_macroproblema', 'co_nodo', 'edo_reg')
            ->where('id_proyecto', '=', $t26_proyectos->id_proyecto)
            ->first();

            $t33_proyecto_localizacion = tab_proyecto_localizacion::select('co_proyecto_localizacion', 'id_proyecto', 'co_ambito_localizacion', 'tx_otra_locacion')
            ->where('id_proyecto', '=', $t26_proyectos->id_proyecto)
            ->first();

            $t37_proyecto_responsables = tab_proyecto_responsable::select('co_proyecto_responsables', 'id_proyecto', 'responsable_nombres', 'reponsable_cedula', 'responsable_correo', 'responsable_telefono', 'tecnico_nombres', 'tecnico_cedula', 'tecnico_correo', 'tecnico_telefono', 'tecnico_unidad', 'registrador_nombres', 'registrador_cedula', 'registrador_correo', 'registrador_telefono', 'administrador_nombres', 'administrador_cedula', 'administrador_correo', 'administrador_telefono', 'administrador_unidad', 'edo_reg')
            ->where('id_proyecto', '=', $t26_proyectos->id_proyecto)
            ->first();

            $datosProyecto = array(
              'proyecto_proyecto' => $id_proyecto,
              'ejercicio_proyecto' => $t26_proyectos->id_ejercicio,
              'ejecutor_proyecto' => $t26_proyectos->id_ejecutor,
              'nombre_proyecto' => $t26_proyectos->nombre,
              'status_proyecto' => $t26_proyectos->status_registro,
              'fecha_ini_proyecto' => $t26_proyectos->fecha_inicio,
              'fecha_fin_proyecto' => $t26_proyectos->fecha_fin,
              'objetivo_proyecto' => $t26_proyectos->objetivo,
              'descripcion_proyecto' => $t26_proyectos->descripcion,
              'sit_presupuesto_proyecto' => $t26_proyectos->sit_presupuesto,
              'monto_proyecto' => $t26_proyectos->monto,
              'clase_sector_proyecto' => $t26_proyectos->clase_sector,
              'clase_subsector_proyecto' => $t26_proyectos->clase_subsector,
              'plan_operativo_proyecto' => $t26_proyectos->plan_operativo,
              'co_estatus_proyecto' => $t26_proyectos->co_estatus,
              'ae_cuadra' => $in_valido
            );

            $datosProyectoVinculo = array(
              'proyecto_vinculo' => $t32_proyecto_vinculos->id_proyecto,
              'obj_historico_vinculo' => $t32_proyecto_vinculos->id_obj_historico,
              'obj_nacional_vinculo' => $t32_proyecto_vinculos->id_obj_nacional,
              'ob_estrategico_vinculo' => $t32_proyecto_vinculos->id_ob_estrategico,
              'obj_general_vinculo' => $t32_proyecto_vinculos->id_obj_general,
              'area_estrategica_vinculo' => $t32_proyecto_vinculos->co_area_estrategica,
              'ambito_estado_vinculo' => $t32_proyecto_vinculos->co_ambito_estado,
              'objetivo_estado_vinculo' => $t32_proyecto_vinculos->co_objetivo_estado,
              'macroproblema_vinculo' => $t32_proyecto_vinculos->co_macroproblema,
              'nodo_vinculo' => $t32_proyecto_vinculos->co_nodo
            );

            $datosProyectoLocalizacion = array(
              'proyecto_localizacion' => $t33_proyecto_localizacion->id_proyecto,
              'ambito_localizacion' => $t33_proyecto_localizacion->co_ambito_localizacion
            );

            $datosProyectoResponsable = array(
              'proyecto_responsable' => trim($t37_proyecto_responsables->id_proyecto),
              'responsable_nombres' => trim($t37_proyecto_responsables->responsable_nombres),
              'reponsable_cedula' => trim($t37_proyecto_responsables->reponsable_cedula),
              'responsable_correo' => trim($t37_proyecto_responsables->responsable_correo),
              'responsable_telefono' => trim($t37_proyecto_responsables->responsable_telefono),
              'tecnico_nombres' => trim($t37_proyecto_responsables->tecnico_nombres),
              'tecnico_cedula' => trim($t37_proyecto_responsables->tecnico_cedula),
              'tecnico_correo' => trim($t37_proyecto_responsables->tecnico_correo),
              'tecnico_telefono' => trim($t37_proyecto_responsables->tecnico_telefono),
              'tecnico_unidad' => trim($t37_proyecto_responsables->tecnico_unidad),
              'registrador_nombres' => trim($t37_proyecto_responsables->registrador_nombres),
              'registrador_cedula' => trim($t37_proyecto_responsables->registrador_cedula),
              'registrador_correo' => trim($t37_proyecto_responsables->registrador_correo),
              'registrador_telefono' => trim($t37_proyecto_responsables->registrador_telefono),
              'administrador_nombres' => trim($t37_proyecto_responsables->administrador_nombres),
              'administrador_cedula' => trim($t37_proyecto_responsables->administrador_cedula),
              'administrador_correo' => trim($t37_proyecto_responsables->administrador_correo),
              'administrador_telefono' => trim($t37_proyecto_responsables->administrador_telefono),
              'administrador_unidad' => trim($t37_proyecto_responsables->administrador_unidad)
            );

            $validadorProyecto = Validator::make($datosProyecto, tab_proyecto::$cerrarProyecto, $mensaje_proy_ae);
            $validadorProyectoVinculo = Validator::make($datosProyectoVinculo, tab_proyecto_vinculo::$cerrarProyecto);
            $validadorProyectoLocalizacion = Validator::make($datosProyectoLocalizacion, tab_proyecto_localizacion::$cerrarProyecto);
            $validadorProyectoResponsable = Validator::make($datosProyectoResponsable, tab_proyecto_responsable::$cerrarProyecto);

            $ae_partida = tab_proyecto_ae::select('tx_codigo', 'descripcion', 'total', DB::raw("monto_cargado_ae_proy(co_proyecto_acc_espec) as mo_cargado"))
            ->where('id_proyecto', '=', $id_proyecto)
            ->orderby('tx_codigo', 'ASC')
            ->where('edo_reg', '=', true)
            ->get();

            $validador_registros = array();
            $contador_partida = 0;

            foreach($ae_partida as $valor) {

                if($valor->total == $valor->mo_cargado) {
                    $in_valido = 1;
                } else {
                    $in_valido = 0;
                }

                $de_ae = $valor->tx_codigo;

                $mensaje_ae_partida = array(
                  $de_ae.'.in'=>'*El monto de la Accion Especifica, no Coincide con el total de sus partidas. <br>Monto AE '.$de_ae.': <span style="color:green"><b>'.number_format($valor->total, 2, ',', '.').'</b></span>'.'<br>Monto Partidas: <span style="color:red"><b>'.number_format($valor->mo_cargado, 2, ',', '.').'</b></span>'.'<br>Diferencia: <b>'.number_format(($valor->total - $valor->mo_cargado), 2, ',', '.').'</b>'
                );

                $validador_partida = Validator::make(array($de_ae => $in_valido), array($de_ae => 'integer|in:1'), $mensaje_ae_partida);
                if ($validador_partida->fails()) {
                    $validador_registros[$de_ae] = array($validador_partida->messages()->first($de_ae));
                    $contador_partida++;
                }

            }

            $validacion = array_merge_recursive($validadorProyecto->getMessageBag()->toArray(), $validadorProyectoVinculo->getMessageBag()->toArray(), $validadorProyectoLocalizacion->getMessageBag()->toArray(), $validadorProyectoResponsable->getMessageBag()->toArray(), $validador_registros);

            if ($validadorProyecto->fails() || $validadorProyectoVinculo->fails() || $validadorProyectoLocalizacion->fails() || $validadorProyectoResponsable->fails() || $contador_partida>0) {
                return Response::json(array(
                  'success' => false,
                  'msg' => $validacion
                ), 200);
            }

            $tabla = tab_proyecto::find(Input::get("codigo"));
            $tabla->co_estatus = 3;
            $tabla->save();
            DB::commit();

            return Response::json(array(
              'success' => true,
              'c' => Input::get("codigo"),
              'msg' => 'Proyecto Cerrado con Exito!'
            ), 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            return Response::json(array(
              'success' => false,
              'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
            ), 500);
        }
    }
}
