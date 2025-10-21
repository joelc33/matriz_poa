<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../model/tab_proyecto_ae.php');
require_once (__DIR__.'/../../model/tab_ac_ae.php');
require_once (__DIR__.'/../../model/tab_proyecto_aepartida.php');
require_once (__DIR__.'/../../model/tab_ac_ae_partida.php');
require_once (__DIR__.'/../../model/vista_pr_partida_base.php');
require_once (__DIR__.'/../../model/vista_pr_partida_meta.php');
require_once (__DIR__.'/../../model/vista_ac_partida_base.php');
require_once (__DIR__.'/../../model/vista_ac_partida_meta.php');

$link = $_POST['op'];
switch($link){
	case '1':
		cerrar_tab_proyecto_ae();
		break;
	case '2':
		cerrar_tab_ac_ae();
		break;
	case '3':
		proyecto_ae_partidas();
		break;
	case '4':
		ac_ae_partidas();
		break;
	case '5':
		abrir_tab_proyecto_ae();
		break;
	case '6':
		abrir_tab_ac_ae();
		break;
	default :
		echo 'no run';
}

function cerrar_tab_proyecto_ae() {

	DB::beginTransaction();
	try {
		$validar_ae = tab_proyecto_ae::select('total', DB::raw("mo_proy_ae_meta(co_proyecto_acc_espec) as mo_cargado"))
		->where('co_proyecto_acc_espec', '=', $_POST['ae'])
		->first();

		if($validar_ae->total == $validar_ae->mo_cargado){
			$in_valido = 1;
		}else{
			$in_valido = 0;
		}

		$mensajes = array(
			'valido.in'=>'El monto Cargado No Coincide con el monto de la AE. <br>Monto Accion Esp.: <span style="color:green"><b>'.number_format($validar_ae->total, 2, ',', '.').'</b></span>'.'<br>Monto Cargado: <span style="color:red"><b>'.number_format($validar_ae->mo_cargado, 2, ',', '.').'</b></span>'.'<br>Diferencia: <b>'.number_format(($validar_ae->total - $validar_ae->mo_cargado), 2, ',', '.').'</b>'
		);

		$datos = array(
			'id' => $_POST['ae'],
			'valido' => $in_valido
		);

		$validador = Validator::make($datos, tab_proyecto_ae::$cerrarAe, $mensajes);
		if ($validador->fails()) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validador->getMessageBag()->toArray()
			)); 
			exit();
		}

		$vista_base = vista_pr_partida_base::select('co_proyecto_acc_espec', 'tx_pa', 'mo_partida')
		->where('co_proyecto_acc_espec', '=', $_POST['ae'])
		->get();

		$validador_meta = array();
		$i=0;
		foreach ($vista_base as $key => $base) {

			$meta = vista_pr_partida_meta::select('tx_pa', 'mo_partida')->where('co_proyecto_acc_espec', '=' ,$base->co_proyecto_acc_espec)->where('tx_pa', '=' ,$base->tx_pa)->first();

			$mensajes = array(
				'base.confirmed'=>'El monto Cargado de la partida: <b>'.$base->tx_pa.'</b> No Coincide con el monto de la meta con partida: <b>'.$meta->tx_pa.'</b> <br>Monto Partida: <span style="color:green"><b>'.number_format($base->mo_partida, 2, ',', '.').'</b></span><br>Monto Meta: <span style="color:red"><b>'.number_format($meta->mo_partida, 2, ',', '.').'</b></span><br>Diferencia: <b>'.number_format(($base->mo_partida - $meta->mo_partida), 2, ',', '.').'</b>'
			);

			$datos = array(
				'base' => $base->mo_partida,
				'base_confirmation' => $meta->mo_partida
			);

			$validadorMeta = Validator::make($datos, vista_pr_partida_meta::$cerrarAe, $mensajes);

			if ($validadorMeta->fails()) {
				$validador_meta[$base->tx_pa] = array($validadorMeta->messages()->first());
			$i++;
			}

		}

		if ($i>0) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validador_meta
			)); 
			exit();
		}

		$proyecto_ae = tab_proyecto_ae::find($_POST['ae']);
		$proyecto_ae->in_definitivo = true;
		$proyecto_ae->save();

		DB::commit();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'msg' => 'Acción Especifica cerrada con Exito!'
		)); 

	}catch (\Illuminate\Database\QueryException $e)
	{
		DB::rollback();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => false,
			'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
			//'msg' => array('ERROR ('.$e->getCode().'):'=> 'CODIGO['.$e->getCode().']: Error en Transaccion, verfique e intente de nuevo.')
		)); 
	}

}

function cerrar_tab_ac_ae() {

	DB::beginTransaction();
	try {
		$validar_ae = tab_ac_ae::select('monto', DB::raw("mo_ac_ae_meta(id_accion_centralizada, id_accion) as mo_cargado"))
		->where('id_accion_centralizada', '=', $_POST['ac'])
		->where('id_accion', '=', $_POST['ae'])
		->first();

		if($validar_ae->monto == $validar_ae->mo_cargado){
			$in_valido = 1;
		}else{
			$in_valido = 0;
		}

		$mensajes = array(
			'valido.in'=>'El monto Cargado No Coincide con el monto de la AE. <br>Monto Accion Esp.: <span style="color:green"><b>'.number_format($validar_ae->monto, 2, ',', '.').'</b></span>'.'<br>Monto Cargado: <span style="color:red"><b>'.number_format($validar_ae->mo_cargado, 2, ',', '.').'</b></span>'.'<br>Diferencia: <b>'.number_format(($validar_ae->monto - $validar_ae->mo_cargado), 2, ',', '.').'</b>'
		);

		$datos = array(
			'id' => $_POST['ac'],
			'valido' => $in_valido
		);

		$validador = Validator::make($datos, tab_ac_ae::$cerrarAe, $mensajes);
		if ($validador->fails()) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validador->getMessageBag()->toArray()
			)); 
			exit();
		}

		$vista_base = vista_ac_partida_base::select('ac', 'ae', 'tx_pa', 'mo_partida')
		->where('ac', '=', $_POST['ac'])
		->where('ae', '=', $_POST['ae'])
		->get();

		$validador_meta = array();
		$i=0;
		foreach ($vista_base as $key => $base) {

			$meta = vista_ac_partida_meta::select('tx_pa', 'mo_partida')->where('ac', '=' ,$base->ac)->where('ae', '=' ,$base->ae)->where('tx_pa', '=' ,$base->tx_pa)->first();

			$mensajes = array(
				'base.confirmed'=>'El monto Cargado de la partida: <b>'.$base->tx_pa.'</b> No Coincide con el monto de la meta con partida: <b>'.$meta->tx_pa.'</b> <br>Monto Partida: <span style="color:green"><b>'.number_format($base->mo_partida, 2, ',', '.').'</b></span><br>Monto Meta: <span style="color:red"><b>'.number_format($meta->mo_partida, 2, ',', '.').'</b></span><br>Diferencia: <b>'.number_format(($base->mo_partida - $meta->mo_partida), 2, ',', '.').'</b>'
			);

			$datos = array(
				'base' => $base->mo_partida,
				'base_confirmation' => $meta->mo_partida
			);

			$validadorMeta = Validator::make($datos, vista_ac_partida_meta::$cerrarAe, $mensajes);

			if ($validadorMeta->fails()) {
				$validador_meta[$base->tx_pa] = array($validadorMeta->messages()->first());
			$i++;
			}

		}

		if ($i>0) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validador_meta
			)); 
			exit();
		}

		//$ac_ae = tab_ac_ae::find($_POST['ae']);
		$ac_ae = tab_ac_ae::updateOrCreate(array('id_accion_centralizada' => $_POST['ac'], 'id_accion' => $_POST['ae']));
		$ac_ae->in_definitivo = true;
		$ac_ae->save();

		DB::commit();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'msg' => 'Acción Especifica cerrada con Exito!'
		)); 

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
	}

}

function proyecto_ae_partidas()
{
	$response['success']  = 'true';

	/*$response['data']  = tab_proyecto_aepartida::select('co_partida', 'tx_denominacion')->where('co_proyecto_acc_espec', '=', $_POST['ae'])->where('edo_reg', '=', true)->orderby('co_partida','ASC')->get()->toArray();*/

	$response['data']  = tab_proyecto_aepartida::join('mantenimiento.tab_partidas as t44','t44.co_partida','=','t42_proyecto_acc_espec_partida.tx_pa')
	->select(DB::raw('tx_pa as co_partida'), 't44.tx_nombre')
	->where('co_proyecto_acc_espec', '=', $_POST['ae'])
	->where('edo_reg', '=', true)
	->groupBy('tx_pa', 'tx_nombre')
	->orderby('co_partida','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
}

function ac_ae_partidas()
{
	$response['success']  = 'true';

	/*$response['data']  = tab_ac_ae_partida::select('co_partida')->where('id_accion_centralizada', '=', $_POST['id_accion_centralizada'])->where('id_accion', '=', $_POST['co_ac_acc_espec'])->where('edo_reg', '=', true)->orderby('co_partida','ASC')->get()->toArray();*/

	$response['data']  = tab_ac_ae_partida::select(DB::raw('left(co_partida, 3) as co_partida'))
//	->where('id_accion_centralizada', '=', $_POST['id_accion_centralizada'])
//	->where('id_accion', '=', $_POST['co_ac_acc_espec'])
	->where('edo_reg', '=', true)
	->groupBy(DB::raw('1'))
	->orderby('co_partida','ASC')->get()->toArray();

	header('Content-Type: application/json');
	echo json_encode($response);
}

function abrir_tab_proyecto_ae() {

	DB::beginTransaction();
	try {
		$validar_ae = tab_proyecto_ae::select('total', DB::raw("mo_proy_ae_meta(co_proyecto_acc_espec) as mo_cargado"))
		->where('co_proyecto_acc_espec', '=', $_POST['ae'])
		->first();

		if($validar_ae->total == $validar_ae->mo_cargado){
			$in_valido = 1;
		}else{
			$in_valido = 0;
		}

		$mensajes = array(
			'valido.in'=>'El monto Cargado No Coincide con el monto de la AE. <br>Monto Accion Esp.: <span style="color:green"><b>'.number_format($validar_ae->total, 2, ',', '.').'</b></span>'.'<br>Monto Cargado: <span style="color:red"><b>'.number_format($validar_ae->mo_cargado, 2, ',', '.').'</b></span>'.'<br>Diferencia: <b>'.number_format(($validar_ae->total - $validar_ae->mo_cargado), 2, ',', '.').'</b>'
		);

		$datos = array(
			'id' => $_POST['ae'],
			'valido' => $in_valido
		);

		$validador = Validator::make($datos, tab_proyecto_ae::$cerrarAe, $mensajes);
		if ($validador->fails()) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validador->getMessageBag()->toArray()
			)); 
			exit();
		}

		$proyecto_ae = tab_proyecto_ae::find($_POST['ae']);
		$proyecto_ae->in_definitivo = null;
		$proyecto_ae->save();

		DB::commit();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'msg' => 'Acción Especifica Reaperturada!'
		)); 

	}catch (\Illuminate\Database\QueryException $e)
	{
		DB::rollback();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => false,
			'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
			//'msg' => array('ERROR ('.$e->getCode().'):'=> 'CODIGO['.$e->getCode().']: Error en Transaccion, verfique e intente de nuevo.')
		)); 
	}

}

function abrir_tab_ac_ae() {

	DB::beginTransaction();
	try {
		$validar_ae = tab_ac_ae::select('monto', DB::raw("mo_ac_ae_meta(id_accion_centralizada, id_accion) as mo_cargado"))
		->where('id_accion_centralizada', '=', $_POST['ac'])
		->where('id_accion', '=', $_POST['ae'])
		->first();

		if($validar_ae->monto == $validar_ae->mo_cargado){
			$in_valido = 1;
		}else{
			$in_valido = 0;
		}

		$mensajes = array(
			'valido.in'=>'El monto Cargado No Coincide con el monto de la AE. <br>Monto Accion Esp.: <span style="color:green"><b>'.number_format($validar_ae->monto, 2, ',', '.').'</b></span>'.'<br>Monto Cargado: <span style="color:red"><b>'.number_format($validar_ae->mo_cargado, 2, ',', '.').'</b></span>'.'<br>Diferencia: <b>'.number_format(($validar_ae->monto - $validar_ae->mo_cargado), 2, ',', '.').'</b>'
		);

		$datos = array(
			'id' => $_POST['ac'],
			'valido' => $in_valido
		);

		$validador = Validator::make($datos, tab_ac_ae::$cerrarAe, $mensajes);
		if ($validador->fails()) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validador->getMessageBag()->toArray()
			)); 
			exit();
		}

		//$ac_ae = tab_ac_ae::find($_POST['ae']);
		$ac_ae = tab_ac_ae::updateOrCreate(array('id_accion_centralizada' => $_POST['ac'], 'id_accion' => $_POST['ae']));
		$ac_ae->in_definitivo = null;
		$ac_ae->save();

		DB::commit();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'msg' => 'Acción Especifica Reaperturada!'
		)); 

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
	}

}
