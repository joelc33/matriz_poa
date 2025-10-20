<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../model/tab_proyecto.php');
require_once (__DIR__.'/../../model/tab_proyecto_vinculo.php');
require_once (__DIR__.'/../../model/tab_proyecto_localizacion.php');
require_once (__DIR__.'/../../model/tab_proyecto_responsable.php');

$router->post('/cerrar', function(){
	$co_proyectos = $_POST['co_proyectos'];
	$id_proyecto = $_POST['id_proyecto'];
	cerrarProyecto( $co_proyectos, $id_proyecto );
});

require_once (__DIR__.'/../../model/route.php');

function cerrarProyecto($co_proyectos, $id_proyecto) {

	DB::beginTransaction();
	try {

		$t26_proyectos = tab_proyecto::select('co_proyectos', 'id_ejercicio', 'id_ejecutor', 'id_proyecto', 'tipo_registro', 'nombre', 'status_registro', 'codigo_new_etapa', 'fecha_inicio', 'fecha_fin', 'objetivo', 'descripcion', 'sit_presupuesto', 'monto', 'clase_sector', 'clase_subsector', 'plan_operativo', 'co_estatus', 'edo_reg', DB::raw("monto_cargado(id_proyecto) as mo_cargado"))
		->where('co_proyectos', '=', $co_proyectos)
		->first();

		if($t26_proyectos->monto == $t26_proyectos->mo_cargado){
			$in_valido = 1;
		}else{
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

		$validacion = array_merge_recursive($validadorProyecto->getMessageBag()->toArray(), $validadorProyectoVinculo->getMessageBag()->toArray(), $validadorProyectoLocalizacion->getMessageBag()->toArray(), $validadorProyectoResponsable->getMessageBag()->toArray());

		if ($validadorProyecto->fails() || $validadorProyectoVinculo->fails() || $validadorProyectoLocalizacion->fails() || $validadorProyectoResponsable->fails()) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'msg' => $validacion
			)); 
			exit();
		}

		$proyecto_cerrar = tab_proyecto::find($co_proyectos);
		$proyecto_cerrar->co_estatus = 3;
		$proyecto_cerrar->save();

		DB::commit();
		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => true,
			'c'  => $co_proyectos,
			'msg' => 'Proyecto Cerrado con Exito!'
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
