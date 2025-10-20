<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../../model/autenticacion/tab_privilegio_menu.php');

$router->post('/lista/opcion', function(){
	storeLista();
});
$router->post('/si', function(){
	si();
});
$router->post('/no', function(){
	no();
});

require_once (__DIR__.'/../../../model/route.php');

function storeLista($start, $limit, $variable, $BuscarBy) {

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 40: $_POST["limit"];
	$variable = $_POST['variable'];
	$BuscarBy = $_POST['BuscarBy'];

	try {

		$tab_privilegio_menu = tab_privilegio_menu::join('autenticacion.tab_privilegio as t01','t01.id','=','autenticacion.tab_privilegio_menu.id_tab_privilegio')
		->join('autenticacion.tab_menu as t02','t02.id','=','t01.id_tab_menu')
		->join('autenticacion.tab_rol_menu as t03','t03.id','=','autenticacion.tab_privilegio_menu.id_tab_rol_menu')
		->select('autenticacion.tab_privilegio_menu.id', 'de_menu', 'de_privilegio', DB::raw("autenticacion.tab_privilegio_menu.in_estatus as in_habilitado"))
		->where('id_tab_rol', '=', $_POST['rol']);

		if ($BuscarBy=="true") {

			if($variable!=""){
				$tab_privilegio_menu->where(DB::raw('de_privilegio::text'), 'ILIKE', "%$variable%");
			}

			$response['success']  = 'true';
			$response['total'] = $tab_privilegio_menu->count();
			$tab_privilegio_menu->skip($start)->take($limit);
			$response['data']  = $tab_privilegio_menu->orderby('t01.id','ASC')->get()->toArray();
		} else {
			$response['success']  = 'true';
			$response['total'] = $tab_privilegio_menu->count();
			$tab_privilegio_menu->skip($start)->take($limit);
			$response['data']  = $tab_privilegio_menu->orderby('t01.id','ASC')->get()->toArray();
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

function si()
{
	DB::beginTransaction();
	try {
		$privilegio = tab_privilegio_menu::find($_POST['id']);
		$privilegio->in_estatus = 'TRUE';
		$privilegio->save();
		DB::commit();

		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'msg' => 'Registro habilitado con Exito!'
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

function no()
{
	DB::beginTransaction();
	try {
		$privilegio = tab_privilegio_menu::find($_POST['id']);
		$privilegio->in_estatus = 'FALSE';
		$privilegio->save();
		DB::commit();

		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'msg' => 'Registro Deshabilitado con Exito!'
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
