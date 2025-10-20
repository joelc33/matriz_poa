<?php
session_start();

require_once (__DIR__.'/../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../model/tab_usuarios.php');
require_once (__DIR__.'/../../model/tab_login_acceso.php');
require_once (__DIR__.'/../../model/autenticacion/tab_privilegio_menu.php');

$router->post('/validar', function(){

	$usuarioPost = $_POST['usuario'];
	$passwordPost = md5($_POST['password']);
	$codigoseg = $_POST["codigoseg"];
	$captcha = $_SESSION['codigo_seguridad'];
	validar_usuario($usuarioPost, $passwordPost, $codigoseg, $captcha);

});

require_once (__DIR__.'/../../model/route.php');

function validar_usuario($usuarioPost, $passwordPost, $codigoseg, $captcha) {

	if (tab_usuarios::where('da_login', '=', $usuarioPost)->where('da_password', '=', $passwordPost)->where('autenticacion.tab_usuarios.in_estatus', '=', TRUE)->exists()) {

		DB::beginTransaction();
		try {

			$datos = array(
				'usuario' => $usuarioPost,
				'contrasena' => $passwordPost,
				'codigoseg' => $codigoseg,
				'codigoseg_confirmation' => $captcha
			);

			$mensajes = array(
				'usuario.exists'=>'Las credenciales que has introducido no coinciden con nuestros registros. Intente de Nuevo.',
				'contrasena.exists'=>'Las credenciales que has introducido no coinciden con nuestros registros. Intente de Nuevo.',
				'codigoseg.confirmed'=>'El captcha ingresado es incorrecto.'
			);

			$validador = Validator::make($datos, tab_usuarios::$validar, $mensajes);
			if ($validador->fails()) {
				header('Content-Type: application/json');
				echo json_encode(array(
					'success' => false,
					'msg' => $validador->getMessageBag()->toArray()
				));
				exit();
			}

			DB::commit();

			$data = tab_usuarios::join('mantenimiento.tab_funcionario as t29','t29.id_tab_usuarios','=','autenticacion.tab_usuarios.id')
			->join('mantenimiento.tab_ejecutores as t24','t24.id','=','t29.id_tab_ejecutores')
			->join('autenticacion.tab_usuario_rol as t05','t05.id_tab_usuarios','=','autenticacion.tab_usuarios.id')
			->select('autenticacion.tab_usuarios.id', 'id_ejecutor', 'id_tab_rol', DB::raw('t24.id as co_ejecutores'))
			->where('da_login', '=', $usuarioPost)
			->where('da_password', '=', $passwordPost)
			->where('autenticacion.tab_usuarios.in_estatus', '=', TRUE)
			->first();

			$acceso = new tab_login_acceso;
			$acceso->id_tab_usuarios = $data->id;
			$acceso->id_tap_tipo_accion = 1;
			$acceso->de_login_accion = 'Acceso al Sistema';
			$acceso->ip_cliente = $_SERVER["REMOTE_ADDR"];
			$acceso->save();

			$credencial = tab_privilegio_menu::join('autenticacion.tab_privilegio as t01','t01.id','=','autenticacion.tab_privilegio_menu.id_tab_privilegio')
			->join('autenticacion.tab_menu as t02','t02.id','=','t01.id_tab_menu')
			->join('autenticacion.tab_rol_menu as t03','t03.id','=','autenticacion.tab_privilegio_menu.id_tab_rol_menu')
			->select('de_privilegio', DB::raw("autenticacion.tab_privilegio_menu.in_estatus as in_habilitado"))
			->where('id_tab_rol', '=', $data->id_tab_rol)->get()->toArray();

			$_SESSION['estatus'] = 'OK';
			$_SESSION['co_usuario'] = $data->id;
			$_SESSION['co_rol'] = $data->id_tab_rol;
			$_SESSION['id_ejecutor'] = $data->id_ejecutor;
			$_SESSION['co_ejecutores'] = $data->co_ejecutores;

			//var_dump($credencial); exit();

			$_SESSION['spe_session']=array();
			array_push($_SESSION['spe_session'], array( 'estatus' => 'OK', 'co_usuario' => $data->id, 'co_rol' => $data->id_tab_rol, 'id_ejecutor' => $data->id_ejecutor, 'co_ejecutores' => $data->co_ejecutores, $credencial));
			//array_push($_SESSION['spe_session'], $credencial);

			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => true,
				'url' => 'seleccionarEF.php',
				'msg' => 'Usuario Validado!'
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
			$datos = array( 'codigoseg' => $codigoseg, 'codigoseg_confirmation' => $captcha );
			$mensajes = array( 'codigoseg.confirmed'=>'El captcha ingresado es incorrecto.');

			$validadorCaptcha = Validator::make($datos, tab_usuarios::$validarCaptcha, $mensajes);

			$validarUsuario = array('ERROR:'=> 'Las credenciales que has introducido no coinciden con nuestros registros. Intente de Nuevo.');
			$validacion = array_merge_recursive($validarUsuario , $validadorCaptcha->getMessageBag()->toArray());

			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validacion
			));
			exit();
	}
}
