<?php

namespace matriz\Http\Controllers\Auxiliar;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_documento;
use matriz\Models\Mantenimiento\tab_cargo;
use matriz\Models\Autenticacion\tab_rol;
use matriz\Models\Mantenimiento\tab_ejecutores;
use matriz\Models\Mantenimiento\tab_ac_ae_predefinida;
use matriz\Models\Mantenimiento\tab_tipo_ejecutor;
use matriz\Models\Mantenimiento\tab_ambito_ejecutor;
use matriz\Models\Mantenimiento\tab_planes;
use matriz\Models\Mantenimiento\tab_planes_zulia;
use matriz\Models\Mantenimiento\tab_ejercicio_fiscal;
use matriz\Models\Mantenimiento\tab_tipo_fondo;
use matriz\Models\Mantenimiento\tab_tipo_recurso;
use matriz\Models\Mantenimiento\tab_ac_predefinida;
use matriz\Models\Mantenimiento\tab_sectores;
use matriz\Models\Mantenimiento\tab_situacion_presupuestaria;
use matriz\Models\Mantenimiento\tab_tipo_personal;
use matriz\Models\Mantenimiento\tab_tipo_empleado;
use matriz\Models\Mantenimiento\tab_municipio_detalle;
use matriz\Models\Mantenimiento\tab_parroquia_detalle;
use matriz\Models\Mantenimiento\tab_municipio;
use matriz\Models\Mantenimiento\tab_periodo;
use matriz\Models\Mantenimiento\tab_tipo_periodo;
use matriz\Models\Mantenimiento\tab_lapso;
use matriz\Models\Mantenimiento\tab_unidad_medida;
use matriz\Models\Mantenimiento\tab_fuente_financiamiento;
use matriz\Models\Mantenimiento\tab_estado;
use Input;
use Response;
use DB;
use Session;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class documentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function documento()
    {
        $response['success']  = 'true';
        $response['data']  = tab_documento::select('id', 'inicial')->where('tipo', '=', "N")->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function cargo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_cargo::select('id', 'de_cargo')->where('in_activo', '=', true)->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function rol()
    {
        $response['success']  = 'true';
        $response['data']  = tab_rol::select('id', 'de_rol')->where('in_estatus', '=', true)->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function ejecutorTodo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ejecutores::select('id', 'id_ejecutor', 'tx_ejecutor')/*->where('in_activo', '=', true)*/->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function acAe()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ac_ae_predefinida::select('id', 'nu_numero as numero', 'de_nombre as nombre')->where('id_padre', '=', Input::get('id_accion'))->where('in_activo', '=', true)->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function acAeActivo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ac_ae_predefinida::select('id', 'nu_numero', 'de_nombre')->where('id_padre', '=', Input::get('id_accion'))->where('in_activo', '=', true)->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function ejecutorAmbito()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ambito_ejecutor::select('id', 'de_ambito_ejecutor')->where('in_activo', '=', true)->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function ejecutorTipo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_tipo_ejecutor::select('id', 'de_tipo_ejecutor')->where('in_activo', '=', true)->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function objetivoHistorico()
    {
        $response['success']  = 'true';
        $response['data']  = tab_planes::select('co_objetivo_historico', 'tx_descripcion')
        ->where('nu_nivel', '=', 1)
        ->where('in_activo', '=', true)
        ->whereRaw(''.Session::get('ejercicio').' = ANY (id_tab_ejercicio_fiscal)')
        ->orderby('co_objetivo_historico', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function objetivoNacional()
    {
        $response['success']  = 'true';
        $response['data']  = tab_planes::select('co_objetivo_nacional', 'tx_descripcion')
        ->where('co_objetivo_historico', '=', Input::get('co_objetivo_historico'))
        ->where('nu_nivel', '=', 2)
        ->where('in_activo', '=', true)
        ->whereRaw(''.Session::get('ejercicio').' = ANY (id_tab_ejercicio_fiscal)')
        ->orderby('co_objetivo_nacional', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function objetivoEstrategico()
    {
        $response['success']  = 'true';
        $response['data']  = tab_planes::select('co_objetivo_estrategico', 'tx_descripcion')
        ->where('co_objetivo_historico', '=', Input::get('co_objetivo_historico'))
        ->where('co_objetivo_nacional', '=', Input::get('co_objetivo_nacional'))
        ->where('nu_nivel', '=', 3)
        ->where('in_activo', '=', true)
        ->whereRaw(''.Session::get('ejercicio').' = ANY (id_tab_ejercicio_fiscal)')
        ->orderby('co_objetivo_estrategico', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function objetivoGeneral()
    {
        $response['success']  = 'true';
        $response['data']  = tab_planes::select('co_objetivo_general', 'tx_descripcion')
        ->where('co_objetivo_historico', '=', Input::get('co_objetivo_historico'))
        ->where('co_objetivo_nacional', '=', Input::get('co_objetivo_nacional'))
        ->where('co_objetivo_estrategico', '=', Input::get('co_objetivo_estrategico'))
        ->where('nu_nivel', '=', 4)
        ->where('in_activo', '=', true)
        ->whereRaw(''.Session::get('ejercicio').' = ANY (id_tab_ejercicio_fiscal)')
        ->orderby('co_objetivo_estrategico', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function planArea()
    {
        $response['success']  = 'true';
        $response['data']  = tab_planes_zulia::select('co_area_estrategica', 'tx_descripcion')
        ->where('nu_nivel', '=', 0)
        ->where('in_activo', '=', true)
        ->orderby('co_area_estrategica', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function planAmbito()
    {
        $response['success']  = 'true';
        $response['data']  = tab_planes_zulia::select('co_ambito_zulia', 'tx_descripcion')
        ->where('co_area_estrategica', '=', Input::get('co_area_estrategica'))
        ->where('nu_nivel', '=', 1)
        ->where('in_activo', '=', true)
        ->orderby('co_ambito_zulia', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function planObjetivo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_planes_zulia::select('co_objetivo_zulia', 'tx_descripcion')
        ->where('co_ambito_zulia', '=', Input::get('co_ambito_zulia'))
        ->where('nu_nivel', '=', 2)
        ->where('in_activo', '=', true)
        ->orderby('co_objetivo_zulia', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function planMacroproblema()
    {
        $response['success']  = 'true';
        $response['data']  = tab_planes_zulia::select('co_macroproblema', 'tx_descripcion')
        ->where('co_ambito_zulia', '=', Input::get('co_ambito_zulia'))
        ->where('nu_nivel', '=', 3)
        ->where('in_activo', '=', true)
        ->orderby('co_macroproblema', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function planNudo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_planes_zulia::select('co_nodo', 'tx_descripcion')
        ->where('co_ambito_zulia', '=', Input::get('co_ambito_zulia'))
        ->where('nu_nivel', '=', 4)
        ->where('in_activo', '=', true)
        ->orderby('co_nodo', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function ejercicioFiscal()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ejercicio_fiscal::select('id')->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function fondoTipo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_tipo_fondo::select('id', 'de_tipo_fondo')->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function recursoTipo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_tipo_recurso::select('id', 'de_tipo_recurso')->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function accionTipo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ac_predefinida::select('id', 'de_nombre', 'de_accion')->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function ejecutorActivo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ejecutores::select('id', 'id_ejecutor', 'tx_ejecutor')
        //->where('in_activo', '=', true)
        ->whereRaw("mantenimiento.sp_in_ejecutor( id, ".Session::get('ejercicio').") is true")
        ->orderby('id_ejecutor', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function poaSector()
    {
        $response['success']  = 'true';
        $response['data']  = tab_sectores::select('id', 'co_sector', 'nu_descripcion')
        ->where('nu_nivel', '=', 1)
        ->where('in_activo', '=', true)
        ->orderby('co_sector', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function poaSubsector()
    {
        $response['success']  = 'true';
        $response['data']  = tab_sectores::select('id', 'co_sub_sector', 'nu_descripcion')
        ->where('co_sector', '=', Input::get('co_sector'))
        ->where('nu_nivel', '=', 2)
        ->where('in_activo', '=', true)
        ->orderby('co_sub_sector', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function poaSituacion()
    {
        $response['success']  = 'true';
        $response['data']  = tab_situacion_presupuestaria::select('id', 'de_situacion_presupuestaria')
        ->where('in_activo', '=', true)
        ->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function personalTipo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_tipo_personal::select('id', 'nu_codigo', 'de_tipo_personal')
        ->where('in_activo', '=', true)
        ->orderby('id', 'ASC')->get()->toBase();
        $response['data']->push(array('id' => 0, 'nu_codigo' => 'N/A', 'de_tipo_personal' => 'Sin Padre' ));
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function personalHijo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_tipo_personal::select('id', 'nu_codigo', 'de_tipo_personal')
        ->where('in_activo', '=', true)
        ->where('id_padre', '>', 0)
        ->orderby('id', 'ASC')->get()->toArray();
        /*$response['data']->push(array('id' => 0, 'nu_codigo' => 'N/A', 'de_tipo_personal' => 'Sin Padre' ));*/
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function empleadoTipo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_tipo_empleado::select('id', 'de_tipo_empleado')
        ->where('in_activo', '=', true)
        ->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function municipioTodo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_municipio_detalle::select('id', 'de_municipio')
        //->where('in_activo', '=', true)
        ->where('id_tab_estado', '=', 23)
        ->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function municipioTodoPost()
    {
        $response['success']  = 'true';
        $response['data']  = tab_municipio_detalle::select('id', 'de_municipio')
        //->where('in_activo', '=', true)
        ->where('id_tab_estado', '=', Input::get('estado'))
        ->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function parroquiaTodo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_parroquia_detalle::select('id', 'de_parroquia')
        //->where('in_activo', '=', true)
        ->where('id_tab_municipio_detalle', '=', Input::get('id_tab_municipio'))
        ->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function municipio()
    {
        $response['success']  = 'true';
        $response['data']  = tab_municipio::select('id', 'de_municipio')
        //->where('in_activo', '=', true)
        ->where('id_tab_estado', '=', 23)
        ->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function periodo()
    {
        $response['success']  = 'true';
        $response['data']  = tab_periodo::select('id', 'de_periodo')->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    public function tipoPeriodo()
    {
        
        $excluir = tab_lapso::select('id_tab_tipo_periodo')
        ->where('id_tab_periodo', '=', Input::get('periodo'))
        ->where('id_tab_ejercicio_fiscal', '=', Input::get('anio'))
        ->get()->toArray();    
        
        $response['success']  = 'true';
        $response['data']  = tab_tipo_periodo::select('id', 'de_tipo_periodo')
        ->where('id_tab_periodo', '=', Input::get('periodo'))
        ->whereNotIn('id', $excluir)
        ->where('in_activo', '=', true)->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }    
    
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function lapso()
    {
        $response['success']  = 'true';
        $response['data']  = tab_lapso::select(
            'id',
            'id_tab_ejercicio_fiscal',
            'id_tab_periodo',
            'nu_lapso',
            'de_lapso',
            DB::raw("to_char(fe_inicio, 'dd/mm/YYYY') as fe_inicio"),
            DB::raw("to_char(fe_fin, 'dd/mm/YYYY') as fe_fin")
        )
        ->where('in_activo', '=', true)
        ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
        ->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function unidadmedida()
    {
        $response['success']  = 'true';
        $response['data']  = tab_unidad_medida::select('id', 'de_unidad_medida')->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function fuentefinanciamiento()
    {
        $response['success']  = 'true';
        $response['data']  = tab_fuente_financiamiento::select('id', 'de_fuente_financiamiento')->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function estado()
    {
        $response['success']  = 'true';
        $response['data']  = tab_estado::select('id', 'de_estado')
        ->where('id_tab_pais', '=', 1)
        ->orderby('id', 'ASC')
        ->get()->toArray();
        return Response::json($response, 200);
    }

}
