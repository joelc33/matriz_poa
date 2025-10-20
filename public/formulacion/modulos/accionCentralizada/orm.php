<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../model/tab_ac_predefinida.php');
require_once (__DIR__.'/../../model/tab_ac.php');
require_once (__DIR__.'/../../model/tab_ac_ae_partida.php');

$router->post('/tipo/accion', function(){
	accionTipo();
});

$router->post('/cerrar/ac', function(){
	$id_ac = $_POST['id_accion_centralizada'];
	cerrarAc($id_ac);
});

require_once (__DIR__.'/../../model/route.php');

function accionTipo() {

	$response['success']  = 'true';
	$response['data']  = tab_ac_predefinida::select( 'id', DB::raw('de_nombre as nombre'), 'de_accion')
	->where('in_activo', '=', true)
	->orderby('id','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
	exit();
}

function cerrarAc($id_ac) {

	DB::beginTransaction();
	try {

		$validar_ae = tab_ac_ae_partida::join('t46_acciones_centralizadas as t02','t02.id','=','t54_ac_ae_partidas.id_accion_centralizada')
		->select( 'id_accion_centralizada', DB::raw("t02.monto as mo_ac"), DB::raw("sum(t54_ac_ae_partidas.monto) as mo_partida"))
		->where('id_accion_centralizada', '=', $id_ac)
		->where('t54_ac_ae_partidas.edo_reg', '=', true)
		->groupBy(DB::raw('1,2'))
		->first();

		if($validar_ae->mo_ac == $validar_ae->mo_partida){
			$in_valido = 1;
		}else{
			$in_valido = 0;
		}

		$mensajes = array(
			'valido.in'=>'El monto Cargado No Coincide con el monto de la AC. <br>Monto Accion Centralizada.: <span style="color:green"><b>'.number_format($validar_ae->mo_ac, 2, ',', '.').'</b></span>'.'<br>Monto Cargado Partidas: <span style="color:red"><b>'.number_format($validar_ae->mo_partida, 2, ',', '.').'</b></span>'.'<br>Diferencia: <b>'.number_format(($validar_ae->mo_ac - $validar_ae->mo_partida), 2, ',', '.').'</b>'
		);

		$datos = array(
			'id' => $id_ac,
			'valido' => $in_valido
		);

		$validador = Validator::make($datos, tab_ac::$cerrarAc, $mensajes);
		if ($validador->fails()) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validador->getMessageBag()->toArray()
			)); 
			exit();
		}

		$ac_cerrar = tab_ac::find($id_ac);
		$ac_cerrar->id_estatus = 3;
		$ac_cerrar->save();

		DB::commit();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'msg' => 'Accion Centralizada Cerrada con Exito!'
		)); 
		exit();

	}catch (\Illuminate\Database\QueryException $e)
	{
		DB::rollback();

		$ms = array();
		if ( preg_match( '/ERROR\:\ *(.*)\s*CONTEXT\:/', $e->getMessage(), $ms ) === 1 ) {
			$mensaje = $ms[1];
		}

		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => false,
			//'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
			'msg' => array('ERROR ('.$e->getCode().'):'=> 'CODIGO['.$e->getCode().']: Error en Transaccion, '.$mensaje)
		)); 
		exit();
	}

}
