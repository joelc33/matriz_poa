<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../../model/tab_proyecto.php');

$router->post('/lista', function(){
	storeLista();
});
$router->post('/lista/proyecto', function(){
	storeListaProyecto();
});
$router->post('/reordenar', function(){
	$ejecutor = $_POST['ejecutor'];
	reordenarProyecto($ejecutor);
});

require_once (__DIR__.'/../../../model/route.php');

function storeLista($start, $limit, $variable, $BuscarBy) {

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	$variable = $_POST['variable'];
	$BuscarBy = $_POST['BuscarBy'];

	try {

		$tab_proyecto = tab_proyecto::select('t26_proyectos.id_ejecutor', 'tx_ejecutor', 'id_ejercicio',  DB::raw('count(id_proyecto) as nu_proyecto'))
		->join('mantenimiento.tab_ejecutores as t01','t01.id_ejecutor','=','t26_proyectos.id_ejecutor')
		->groupBy('t26_proyectos.id_ejecutor', 'tx_ejecutor', 'id_ejercicio')
		->where('t26_proyectos.edo_reg', '=', TRUE)
		->where('t26_proyectos.id_ejercicio', '=', $_SESSION['ejercicio_fiscal']);

		if ($BuscarBy=="true") {

			if($variable!=""){
				$tab_proyecto->where(DB::raw('tx_ejecutor::text'), 'ILIKE', "%$variable%");
			}

			$response['success']  = 'true';
			$response['total'] = $tab_proyecto->count();
			$tab_proyecto->skip($start)->take($limit);
			$response['data']  = $tab_proyecto->orderby('t26_proyectos.id_ejecutor','ASC')->get()->toArray();
		} else {
			$response['success']  = 'true';
			$response['total'] = $tab_proyecto->count();
			$tab_proyecto->skip($start)->take($limit);
			$response['data']  = $tab_proyecto->orderby('t26_proyectos.id_ejecutor','ASC')->get()->toArray();
		}

		header('Content-Type: application/json');
			echo json_encode($response); 
		exit();
	} catch (\Illuminate\Database\QueryException $e) {

		header('Content-Type: application/json');
			echo json_encode(array('success' => false, 'message' => utf8_encode( $e->getMessage()))); 
		exit();

	}
}

function storeListaProyecto() {

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 5: $_POST["limit"];
	$variable = $_POST['variable'];
	$BuscarBy = $_POST['BuscarBy'];
	$ejecutor = $_POST['ejecutor'];

	try {

		$tab_proyecto = tab_proyecto::join('mantenimiento.tab_estatus as t01','t01.id','=','t26_proyectos.co_estatus')
		->select('id_proyecto', 'nombre', 'monto',  DB::raw('monto_cargado(id_proyecto) as monto'), 'de_estatus', 'co_estatus')
		->where('t26_proyectos.edo_reg', '=', TRUE)
		->where('t26_proyectos.id_ejecutor', '=', $ejecutor)
		->where('t26_proyectos.id_ejercicio', '=', $_SESSION['ejercicio_fiscal']);

		if ($BuscarBy=="true") {

			if($variable!=""){
				$tab_proyecto->where(DB::raw('nombre::text'), 'ILIKE', "%$variable%");
			}

			$response['success']  = 'true';
			$response['total'] = $tab_proyecto->count();
			$tab_proyecto->skip($start)->take($limit);
			$response['data']  = $tab_proyecto->orderby('t26_proyectos.id_proyecto','ASC')->get()->toArray();
		} else {
			$response['success']  = 'true';
			$response['total'] = $tab_proyecto->count();
			$tab_proyecto->skip($start)->take($limit);
			$response['data']  = $tab_proyecto->orderby('t26_proyectos.id_proyecto','ASC')->get()->toArray();
		}

		header('Content-Type: application/json');
			echo json_encode($response); 
		exit();
	} catch (\Illuminate\Database\QueryException $e) {

		header('Content-Type: application/json');
			echo json_encode(array('success' => false, 'message' => utf8_encode( $e->getMessage()))); 
		exit();

	}
}

function reordenarProyecto($ejecutor) {

	DB::beginTransaction();
	try {

		$ef = array(2015,2016);
		if (in_array($_SESSION['ejercicio_fiscal'], $ef)){
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => array('ERROR:'=>'Periodo Fiscal No valido para Ejecutar esta Función.')
			)); 
			exit();
		}

		if (tab_proyecto::where('co_estatus', '=', 1)->where('id_ejecutor', '=', $ejecutor)->where('id_ejercicio', '=', $_SESSION['ejercicio_fiscal'])->where('edo_reg', '=', TRUE)->exists()) { 
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => array('ERROR:'=>'Existen proyectos sin cerrar deben estar cerrados <b>TODOS</b> para Reordenar los Codigos.')
			)); 
			exit();
		}
		$datos = array(
			'ejecutor' => $ejecutor
		);

		$validador = Validator::make($datos, tab_proyecto::$ordenarProyecto);
		if ($validador->fails()) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validador->getMessageBag()->toArray()
			)); 
			exit();
		}

		$drop_pr = DB::select( DB::raw("DROP TABLE IF EXISTS orden_cursor_tab_proyecto;"));

		$ordenar_pr = DB::select( DB::raw("SELECT co_proyectos, id_proyecto as original, 'PR'||id_ejecutor||id_ejercicio||lpad((row_number() OVER (ORDER BY co_proyectos))::text, 4, '0') as corregido into temp orden_cursor_tab_proyecto FROM t26_proyectos where id_ejecutor= :ejecutor and edo_reg is true and id_ejercicio = :ejercicio order by co_proyectos asc;"), array( 'ejecutor' => $ejecutor, 'ejercicio' => $_SESSION['ejercicio_fiscal']));

		$delete_pr = DB::select( DB::raw("DELETE FROM t26_proyectos WHERE id_ejecutor= :ejecutor and id_ejercicio = :ejercicio and edo_reg is false;"), array( 'ejecutor' => $ejecutor, 'ejercicio' => $_SESSION['ejercicio_fiscal']));

		$update_pr = DB::select( DB::raw("UPDATE t26_proyectos t1 SET id_proyecto = t2.corregido FROM orden_cursor_tab_proyecto t2 WHERE t1.co_proyectos = t2.co_proyectos;"));

		DB::commit();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'msg' => 'Codigos de Proyecto Ordenados con Exito!'
		)); 
		exit();

	}catch (\Illuminate\Database\QueryException $e)
	{
		DB::rollback();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => false,
			'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
			//'msg' => array('ERROR ('.$e->getCode().'):'=> 'CODIGO['.$e->getCode().']: Error en Transaccion, verfique e intente de nuevo.')
		)); 
		exit();
	}

}
