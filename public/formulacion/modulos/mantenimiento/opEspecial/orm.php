<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../../model/mantenimiento/tab_estatus_proyecto.php');
require_once (__DIR__.'/../../../model/mantenimiento/tab_situacion_presupuestaria.php');
require_once (__DIR__.'/../../../model/mantenimiento/tab_sectores.php');
require_once (__DIR__.'/../../../model/mantenimiento/tab_plan_operativo.php');
require_once (__DIR__.'/../../../model/mantenimiento/tab_ejecutores.php');
require_once (__DIR__.'/../../../model/tab_proyecto.php');
require_once (__DIR__.'/../../../model/tab_ac_predefinida.php');
require_once (__DIR__.'/../../../model/tab_ac.php');

$router->get('/estatusproyecto', function(){
	estatusProyecto();
});
$router->get('/situacionpresupuestaria', function(){
	situacionPresupuestaria();
});
$router->get('/sector', function(){
	sector();
});
$router->post('/subsector', function(){
	subSector();
});
$router->get('/planoperativo', function(){
	planOperativo();
});
$router->get('/ejecutor', function(){
	ejecutor();
});
$router->post('/guardar/proyecto', function(){
	guardarPR();
});
$router->get('/tipoaccion', function(){
	tipoAccion();
});
$router->post('/guardar/ac', function($id){
	guardarAC();
});

require_once (__DIR__.'/../../../model/route.php');

function estatusProyecto() {

	$response['success']  = 'true';
	$response['data']  = tab_estatus_proyecto::select( 'id', 'de_estatus_proyecto')
	->where('in_activo', '=', true)
	->orderby('id','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function situacionPresupuestaria() {

	$response['success']  = 'true';
	$response['data']  = tab_situacion_presupuestaria::select( 'id', 'de_situacion_presupuestaria')
	->where('in_activo', '=', true)
	->orderby('id','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function sector() {

	$response['success']  = 'true';
	$response['data']  = tab_sectores::select( 'id', 'co_sector', 'nu_descripcion')
	->where('in_activo', '=', true)
	->where('nu_nivel', '=', 1)
	->orderby('co_sector','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function subSector() {

	$response['success']  = 'true';
	$response['data']  = tab_sectores::select( 'id', 'co_sector', 'co_sub_sector', 'nu_descripcion')
	->where('in_activo', '=', true)
	->where('nu_nivel', '=', 2)
	->where('co_sector', '=', $_POST['co_sector'])
	->orderby('co_sector','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function planOperativo() {

	$response['success']  = 'true';
	$response['data']  = tab_plan_operativo::select( 'id', 'de_plan_operativo')
	->where('in_activo', '=', true)
	->orderby('id','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function ejecutor() {

	$response['success']  = 'true';
	$response['data']  = tab_ejecutores::select( 'id', 'id_ejecutor', DB::raw("id_ejecutor||'-'||tx_ejecutor as tx_ejecutor"))
	->whereRaw("mantenimiento.sp_in_ejecutor( id, :periodo ) is true", array( 'periodo' => $_SESSION['ejercicio_fiscal']))
	->orderby('id_ejecutor','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function tipoAccion() {

	$response['success']  = 'true';
	$response['data']  = tab_ac_predefinida::select( 'id', DB::raw('de_nombre as nombre'), 'de_accion')
	->where('in_activo', '=', true)
	->orderby('id','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function guardarPR() {

	DB::beginTransaction();

		$datos = array(
			'ejercicio_proyecto' => $_POST['ejercicio_proyecto'],
			'ejecutor_proyecto' => $_POST['ejecutor_proyecto'],
			'nombre_proyecto' => $_POST['nombre_proyecto'],
			'status_proyecto' => $_POST['status_proyecto'],
			'fecha_ini_proyecto' => $_POST['fecha_ini_proyecto'],
			'fecha_fin_proyecto' => $_POST['fecha_fin_proyecto'],
			'objetivo_proyecto' => $_POST['objetivo_proyecto'],
			'descripcion_proyecto' => $_POST['descripcion_proyecto'],
			'sit_presupuesto_proyecto' => $_POST['sit_presupuesto_proyecto'],
			'monto_proyecto' => $_POST['monto_proyecto'],
			'clase_sector_proyecto' => $_POST['clase_sector_proyecto'],
			'clase_subsector_proyecto' => $_POST['clase_subsector_proyecto'],
			'plan_operativo_proyecto' => $_POST['plan_operativo_proyecto'],
			'id_tab_ejecutor' => $_POST['id_tab_ejecutor']
		);

		try {

			$validador = Validator::make($datos, tab_proyecto::$validarCrear);
			if ($validador->fails()) {
				header('Content-Type: application/json');
				echo json_encode(array(
					'success' => false,
					'msg' => $validador->getMessageBag()->toArray()
				)); 
				exit();
			}

			$proyecto = new tab_proyecto;
			$proyecto->id_ejercicio = $_POST['ejercicio_proyecto'];
			$proyecto->id_ejecutor = $_POST['ejecutor_proyecto'];
			$proyecto->tipo_registro = 1;
			$proyecto->nombre = $_POST['nombre_proyecto'];
			$proyecto->fecha_inicio = $_POST['fecha_ini_proyecto'];
			$proyecto->fecha_fin = $_POST['fecha_fin_proyecto'];
			$proyecto->status_registro = $_POST['status_proyecto'];
			$proyecto->objetivo = $_POST['objetivo_proyecto'];
			$proyecto->sit_presupuesto = $_POST['sit_presupuesto_proyecto'];
			$proyecto->monto = $_POST['monto_proyecto'];
			$proyecto->descripcion = $_POST['descripcion_proyecto'];
			$proyecto->clase_sector = $_POST['clase_sector_proyecto'];
			$proyecto->clase_subsector = $_POST['clase_subsector_proyecto'];
			$proyecto->plan_operativo = $_POST['plan_operativo_proyecto'];
			$proyecto->id_tab_ejecutor = $_POST['id_tab_ejecutor'];
			$proyecto->co_estatus = 1;
			$proyecto->edo_reg = true;
			$proyecto->save();

			DB::commit();

			$serial = tab_proyecto::findOrFail($proyecto->co_proyectos);

			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => true,
				'msg' => '<span style="color:green;font-size:13px,">Proceso realizado exitosamente.<br>
				CÓDIGO DEL PROYECTO <br><textarea readonly>'.$serial->id_proyecto.'</textarea></span>'
			)); 
			exit();

		}catch (\Illuminate\Database\QueryException $e)
		{
			DB::rollback();
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				//'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
				'msg' => array('ERROR ('.$e->getCode().'):'=> 'CODIGO['.$e->getCode().']: Error en Transaccion, verfique e intente de nuevo.')
			)); 
			exit();
		}

}

function guardarAC() {

	DB::beginTransaction();

		$datos = array(
			'ejercicio_ac' => $_POST['ejercicio_ac'],
			'ejecutor_ac' => $_POST['ejecutor_ac'],
			'accion_ac' => $_POST['accion_ac'],
			'descripcion_ac' => $_POST['descripcion_ac'],
			'mision_ac' => $_POST['mision_ac'],
			'vision_ac' => $_POST['vision_ac'],
			'objetivo_ac' => $_POST['objetivo_ac'],
			'sector_ac' => $_POST['sector_ac'],
			'subsector_ac' => $_POST['subsector_ac'],
			'fecha_ini_ac' => $_POST['fecha_ini_ac'],
			'fecha_fin_ac' => $_POST['fecha_fin_ac'],
			'sit_presupuesto_ac' => $_POST['sit_presupuesto_ac'],
			'monto_ac' => $_POST['monto_ac'],
			'poblacion_ac' => $_POST['poblacion_ac'],
			'empleo_ac' => $_POST['empleo_ac'],
			'producto_ac' => $_POST['producto_ac'],
			'resultado_ac' => $_POST['resultado_ac'],
			'id_tab_ejecutor' => $_POST['id_tab_ejecutor']
		);

		try {

			$validador = Validator::make($datos, tab_ac::$validarCrear);
			if ($validador->fails()) {
				header('Content-Type: application/json');
				echo json_encode(array(
					'success' => false,
					'msg' => $validador->getMessageBag()->toArray()
				)); 
				exit();
			}

			$ac = new tab_ac;
			$ac->id_ejercicio = $_POST['ejercicio_ac'];
			$ac->id_ejecutor = $_POST['ejecutor_ac'];
			$ac->id_estatus = 1;
			$ac->id_accion = $_POST['accion_ac'];
			$ac->id_subsector = $_POST['subsector_ac'];
			$ac->descripcion = $_POST['descripcion_ac'];
			$ac->inst_mision = $_POST['mision_ac'];
			$ac->inst_vision = $_POST['vision_ac'];
			$ac->inst_objetivos = $_POST['objetivo_ac'];
			$ac->fecha_inicio = $_POST['fecha_ini_ac'];
			$ac->fecha_fin = $_POST['fecha_fin_ac'];
			$ac->sit_presupuesto = $_POST['sit_presupuesto_ac'];
			$ac->nu_po_beneficiar = $_POST['poblacion_ac'];
			$ac->nu_em_previsto = $_POST['empleo_ac'];
			$ac->tx_re_esperado = $_POST['resultado_ac'];
			$ac->tx_pr_objetivo = $_POST['producto_ac'];
			$ac->monto = $_POST['monto_ac'];
			$ac->id_tab_ejecutor = $_POST['id_tab_ejecutor'];
			$ac->edo_reg = true;
			$ac->save();

			DB::commit();

			$serial = tab_ac::findOrFail($ac->id);

			//$serial = tab_ac::select(DB::raw("'AC' || id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo"))->where('id', '=', $ac->id);

			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => true,
				'msg' => '<span style="color:green;font-size:13px,">Proceso realizado exitosamente.<br>
				CÓDIGO DE LA ACCIÓN CENTRALIZADA <br><textarea readonly>'.'AC'.$serial->id_ejecutor.$serial->id_ejercicio.str_pad( $serial->id_accion, 5, '0', STR_PAD_LEFT ).'</textarea></span>'
			)); 
			exit();

		}catch (\Illuminate\Database\QueryException $e)
		{
			DB::rollback();
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				//'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
				'msg' => array('ERROR ('.$e->getCode().'):'=> 'CODIGO['.$e->getCode().']: Error en Transaccion, verfique e intente de nuevo.')
			)); 
			exit();
		}

}
