<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../../model/tab_apertura_ef.php');

$router->post('/lista/cronograma', function(){
	storeLista();
});
$router->post('/guardar', function(){
	guardar();
});
$router->post('/guardar/{id}', function($id){
	guardar($id);
});
$router->post('/eliminar', function(){
	eliminar();
});

require_once (__DIR__.'/../../../model/route.php');

function storeLista($start, $limit, $variable, $BuscarBy) {

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	$variable = $_POST['variable'];
	$BuscarBy = $_POST['BuscarBy'];

	try {

		$tab_apertura_ef = tab_apertura_ef::select('id', 'de_apertura', DB::raw("to_char(fe_desde, 'dd/mm/YYYY') as fe_desde"), DB::raw("to_char(fe_hasta, 'dd/mm/YYYY') as fe_hasta"))
		->where('in_activo', '=', TRUE)
		->where('id_tab_ejercicio_fiscal', '=', $_POST['ef']);

		if ($BuscarBy=="true") {

			if($variable!=""){
				$tab_apertura_ef->where(DB::raw('de_apertura::text'), 'ILIKE', "%$variable%");
			}

			$response['success']  = 'true';
			$response['total'] = $tab_apertura_ef->count();
			$tab_apertura_ef->skip($start)->take($limit);
			$response['data']  = $tab_apertura_ef->orderby('id','ASC')->get()->toArray();
		} else {
			$response['success']  = 'true';
			$response['total'] = $tab_apertura_ef->count();
			$tab_apertura_ef->skip($start)->take($limit);
			$response['data']  = $tab_apertura_ef->orderby('id','ASC')->get()->toArray();
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

function guardar($id = NULL) {

	DB::beginTransaction();

	if($id!=''||$id!=null){

		$datos = array(
			'fecha_apertura' => $_POST['fecha_apertura'],
			'fecha_cierre' => $_POST['fecha_cierre'],
			'descripcion' => $_POST['descripcion'],
		);

		try {

			$validador = Validator::make($datos, tab_apertura_ef::$validarEditar);
			if ($validador->fails()) {
				header('Content-Type: application/json');
				echo json_encode(array(
					'success' => false,
					'msg' => $validador->getMessageBag()->toArray()
				));
				exit();
			}

			list($dia, $mes, $anio) = explode("/",$_POST['fecha_apertura']);
			$fecha_apertura = $anio."-".$mes."-".$dia;

			list($dia, $mes, $anio) = explode("/",$_POST['fecha_cierre']);
			$fecha_cierre = $anio."-".$mes."-".$dia;

			$apertura = tab_apertura_ef::find($id);
			$apertura->fe_desde = $fecha_apertura;
			$apertura->fe_hasta = $fecha_cierre;
			$apertura->de_apertura = $_POST['descripcion'];
			$apertura->save();

			DB::commit();
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => true,
				'msg' => 'Editado con Exito!'
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
	}else{

		$datos = array(
			'periodo_fiscal' => $_POST['id_tab_ejercicio_fiscal'],
			'fecha_apertura' => $_POST['fecha_apertura'],
			'fecha_cierre' => $_POST['fecha_cierre'],
			'descripcion' => $_POST['descripcion'],
		);

		try {

			$validador = Validator::make($datos, tab_apertura_ef::$validarCrear);
			if ($validador->fails()) {
				header('Content-Type: application/json');
				echo json_encode(array(
					'success' => false,
					'msg' => $validador->getMessageBag()->toArray()
				));
				exit();
			}

			list($dia, $mes, $anio) = explode("/",$_POST['fecha_apertura']);
			$fecha_apertura = $anio."-".$mes."-".$dia;

			list($dia, $mes, $anio) = explode("/",$_POST['fecha_cierre']);
			$fecha_cierre = $anio."-".$mes."-".$dia;

			$apertura = new tab_apertura_ef;
			$apertura->id_tab_ejercicio_fiscal = $_POST['id_tab_ejercicio_fiscal'];
			$apertura->fe_desde = $fecha_apertura;
			$apertura->fe_hasta = $fecha_cierre;
			$apertura->de_apertura = $_POST['descripcion'];
			$apertura->in_activo = TRUE;
			$apertura->save();

			DB::commit();
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => true,
				'msg' => 'Creado con Exito!'
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

}

function eliminar()
{
	DB::beginTransaction();
	try {
		$apertura = tab_apertura_ef::find($_POST['id']);
		$apertura->in_activo = 'FALSE';
		$apertura->save();
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
