<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

function formatoDinero($numero, $fractional=true) {
    if ($fractional) {
        $numero = sprintf('%.2f', $numero);
    }
    while (true) {
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $numero);
        if ($replaced != $numero) {
            $numero = $replaced;
        } else {
            break;
        }
    }
    return "Bs. ".$numero;
}

switch ($_POST['op']) {

	case 1:

	$sql = "SELECT t39.*, de_unidad_medida, tx_ejecutor, proyecto_seguimiento.sp_proyecto_ae_meta_cargado(t39.id) as mo_cargado FROM proyecto_seguimiento.tab_proyecto_ae as t39
	inner join mantenimiento.tab_unidad_medida as t21 on t39.id_tab_unidad_medida=t21.id
	inner join mantenimiento.tab_ejecutores as t24 on t39.co_ejecutores=t24.id
	WHERE id_tab_proyecto ='".$_POST['id_proyecto']."' AND t39.in_activo is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 5: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t39.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec"     => trim($row["id"]),
		    "id_proyecto"     => trim($row["id_tab_proyecto"]),
		    "tx_codigo"     => trim($row["tx_codigo"]),
		    "descripcion"     => trim($row["descripcion"]),
		    "co_unidades_medida"     => trim($row["de_unidad_medida"]),
		    "meta"     => trim($row["meta"]),
		    "ponderacion"     => trim($row["ponderacion"]),
		    "bien_servicio"     => trim($row["bien_servicio"]),
		    "total"     => trim($row["total"]),
		    "mo_cargado"     => trim($row["mo_cargado"]),
		    "fec_inicio"     => trim(date_format(date_create($row["fec_inicio"]),'d/m/Y')),
		    "fec_termino"     => trim(date_format(date_create($row["fec_termino"]),'d/m/Y')),
		    "co_ejecutores"     => trim($row["tx_ejecutor"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 2:

	$sql = "SELECT t47.*, tx_unidades_medida, tx_ejecutor, ('AC' || t24.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0')) as id_ac, t53.numero, t53.nombre, mo_ac_ae_meta(id_accion_centralizada, t47.id_accion) AS mo_cargado FROM t47_ac_accion_especifica as t47
	inner join t21_unidades_medida as t21 on t47.id_unidad_medida=t21.co_unidades_medida
	inner join t24_ejecutores as t24 on t47.id_ejecutor=t24.id_ejecutor
	inner join t46_acciones_centralizadas as t46 on t47.id_accion_centralizada=t46.id
	inner join t53_ac_ae_predefinidas as t53 on t53.id = t47.id_accion
	WHERE ('AC' || t24.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0')) = '".$_POST['id_proyecto']."' AND t47.edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 5: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY id_accion ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec"     => trim($row["id_accion"]),
		    "id_accion_centralizada"     => trim($row["id_accion_centralizada"]),
		    "id_proyecto"     => trim($row["id_ac"]),
		    "tx_codigo"     => trim($row["numero"]),
		    "descripcion"     => trim($row["nombre"]),
		    "co_unidades_medida"     => trim($row["tx_unidades_medida"]),
		    "meta"     => trim($row["meta"]),
		    "ponderacion"     => trim($row["ponderacion"]),
		    "bien_servicio"     => trim($row["bien_servicio"]),
		    "total"     => trim($row["monto"]),
		    "mo_cargado"     => trim($row["mo_cargado"]),
		    "fec_inicio"     => trim(date_format(date_create($row["fecha_inicio"]),'d/m/Y')),
		    "fec_termino"     => trim(date_format(date_create($row["fecha_fin"]),'d/m/Y')),
		    "co_ejecutores"     => trim($row["tx_ejecutor"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 3:

	$sql = "SELECT t67.*, de_unidad_medida, proyecto_seguimiento.sp_proyecto_ae_mo_metafin(t67.id) AS mo_cargado 
	FROM proyecto_seguimiento.tab_meta_fisica as t67
	inner join mantenimiento.tab_unidad_medida as t21 on t67.id_tab_unidad_medida=t21.id
	WHERE id_tab_proyecto_ae='".$_POST['co_proyecto_acc_espec']."' and t67.in_activo is true ";

	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" and nb_meta ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t67.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_metas"     => trim($row["id"]),
		    "co_proyecto_acc_espec"     => trim($row["id_tab_proyecto_ae"]),
		    "tx_codigo"     => trim($row["codigo"]),
		    "nb_meta"     => trim($row["nb_meta"]),
		    "co_unidades_medida"     => trim($row["de_unidad_medida"]),
		    "tx_prog_anual"     => trim($row["tx_prog_anual"]),
		    "fecha_inicio"     => trim(date_format(date_create($row["fecha_inicio"]),'d/m/Y')),
		    "fecha_fin"     => trim(date_format(date_create($row["fecha_fin"]),'d/m/Y')),
		    "nb_responsable"     => trim($row["nb_responsable"]),
		    "mo_cargado"     => trim($row["mo_cargado"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 4:

	$sql = "SELECT * FROM mantenimiento.tab_unidad_medida;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_unidades_medida"		=> $row["id"],
			"tx_unidades_medida"	=> $row["de_unidad_medida"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 5:

	$co_proyecto_acc_espec = decode($_POST['co_proyecto_acc_espec']);

	$sqlAcc = "SELECT total FROM proyecto_seguimiento.tab_proyecto_ae where id=".$co_proyecto_acc_espec;
	$resultado = $comunes->ObtenerFilasBySqlSelect($sqlAcc);
	$resultadoReal = $resultado[0]['total'];
	$codigo = decode($_POST['co_metas']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="proyecto_seguimiento.tab_meta_fisica";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["nb_meta"] = decode($_POST['nb_actividad']);
			$variable["id_tab_unidad_medida"] = decode($_POST['co_unidades_medida']);
			$variable["tx_prog_anual"] = decode($_POST['pr_anual']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fecha_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fecha_fin"] = $fecha_culminacion;
			$variable["nb_responsable"] = decode($_POST['nb_responsable']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$co_metas = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			$sql4 = "UPDATE proyecto_seguimiento.tab_meta_financiera SET in_activo = false, updated_at = '".date("Y-m-d H:i:s")."' WHERE id_tab_meta_fisica = '$codigo' and in_activo is true;";
			$comunes->EjecutarQuery($sql4);

			$detalle = json_decode($_POST['json_detalle'],true); 

			$mo_validar = 0;
			foreach ($detalle as $lista){
				$mo_validar = $mo_validar+$lista['mo_presupuesto'];
				$tabla1="proyecto_seguimiento.tab_meta_financiera";
				$primaryKey1="id";
				$variable1["id_tab_meta_fisica"] = decode($codigo);
				$variable1["id_tab_municipio_detalle"] = decode($lista['co_municipio']);
				$variable1["id_tab_parroquia_detalle"] = decode($lista['co_parroquia']);
				$variable1["mo_presupuesto"] = decode($lista['mo_presupuesto']);
				$variable1["co_partida"] = decode($lista['co_partida']);
				$variable1["id_tab_fuente_financiamiento"] = decode($lista['co_fuente_financiamiento']);
				$variable1["created_at"] = date("Y-m-d H:i:s");
				$variable1["in_activo"] = 'TRUE';
				$co_metas_detalle = $comunes->InsertConID($tabla1,$variable1,$primaryKey1);
			}
		
			if ($mo_validar>$resultadoReal){
				$paraTransaccion->RollbackTrans();
				echo json_encode(array(
					    "success" => false,
					    "msg" => '<span style="color:red;">Monto de Metas Financieras Supera al monto <br>de la Accion Especifica.</span>'
				));
			}else{

				if ($co_metas){
					$paraTransaccion->CommitTrans();
					echo json_encode(array(
						    "success" => true,
						    "msg" => 'Modificación realizada exitosamente.'
					));
				}
				else{
					$paraTransaccion->RollbackTrans();
				}
			}
		}catch(Exception $e){
			echo json_encode(array(
				    "success" => false,
				    "msg" => "Error en Transaccion.\n".$e->getMessage()
			));
		}
	}else{
			try{
			$paraTransaccion->BeginTrans();
			$tabla="proyecto_seguimiento.tab_meta_fisica";
			$primaryKey="id";
			$variable["id_tab_proyecto_ae"] = decode($co_proyecto_acc_espec);
			$variable["nb_meta"] = decode($_POST['nb_actividad']);
			$variable["id_tab_unidad_medida"] = decode($_POST['co_unidades_medida']);
			$variable["tx_prog_anual"] = decode($_POST['pr_anual']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fecha_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fecha_fin"] = $fecha_culminacion;
			$variable["nb_responsable"] = decode($_POST['nb_responsable']);
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = 'TRUE';
			$co_metas = $comunes->InsertConID($tabla,$variable,$primaryKey);

			$detalle = json_decode($_POST['json_detalle'],true); 

			$mo_validar = 0;
			foreach ($detalle as $lista){
				$mo_validar = $mo_validar+$lista['mo_presupuesto'];
				$tabla1="proyecto_seguimiento.tab_meta_financiera";
				$primaryKey1="id";
				$variable1["id_tab_meta_fisica"] = decode($co_metas);
				$variable1["id_tab_municipio_detalle"] = decode($lista['co_municipio']);
				$variable1["id_tab_parroquia_detalle"] = decode($lista['co_parroquia']);
				$variable1["mo_presupuesto"] = decode($lista['mo_presupuesto']);
				$variable1["co_partida"] = decode($lista['co_partida']);
				$variable1["id_tab_fuente_financiamiento"] = decode($lista['co_fuente_financiamiento']);
				$variable1["created_at"] = date("Y-m-d H:i:s");
				$variable1["in_activo"] = 'TRUE';
				$co_metas_detalle = $comunes->InsertConID($tabla1,$variable1,$primaryKey1);
			}

			if ($mo_validar>$resultadoReal){
				$paraTransaccion->RollbackTrans();
				echo json_encode(array(
					    "success" => false,
					    "msg" => '<span style="color:red;">Monto de Metas Financieras Supera al monto <br>de la Accion Especifica.</span>'
				));
			}else{
				if ($co_metas){
					$paraTransaccion->CommitTrans();
					echo json_encode(array(
						    "success" => true,
						    "msg" => 'Proceso realizado exitosamente.'
					));
				}
				else{
					$paraTransaccion->RollbackTrans();
				}
			}
		}catch(Exception $e){
			echo json_encode(array(
				    "success" => false,
				    "msg" => "Error en Transaccion.\n".$e->getMessage()
			));
		}
	}

		break;
	case 6:

	$sql = "SELECT t68.*, de_municipio, de_parroquia, de_fuente_financiamiento FROM proyecto_seguimiento.tab_meta_financiera as t68
	left join mantenimiento.tab_municipio_detalle as t64 on t68.id_tab_municipio_detalle=t64.id
	left join mantenimiento.tab_parroquia_detalle as t65 on t68.id_tab_parroquia_detalle=t65.id
	inner join mantenimiento.tab_fuente_financiamiento as t66 on t68.id_tab_fuente_financiamiento=t66.id
	WHERE id_tab_meta_fisica='".$_POST['co_metas']."' AND t68.in_activo is true ";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t68.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_metas_detalle"     => trim($row["id"]),
		    "co_metas"     => trim($row["id_tab_meta_fisica"]),
		    "co_municipio"     => trim($row["id_tab_municipio_detalle"]),
		    "tx_municipio"     => trim($row["de_municipio"]),
		    "co_parroquia"     => trim($row["id_tab_parroquia_detalle"]),
		    "tx_parroquia"     => trim($row["de_parroquia"]),
		    "mo_presupuesto"     => trim($row["mo_presupuesto"]),
		    "co_partida"     => trim($row["co_partida"]),
		    "co_fuente_financiamiento"     => trim($row["id_tab_fuente_financiamiento"]),
		    "tx_fuente_financiamiento"     => trim($row["de_fuente_financiamiento"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 7:

	$sql = "SELECT * FROM mantenimiento.tab_estado;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_estado"		=> $row["id"],
			"tx_estado"	=> $row["de_estado"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 8:

	$sql = "SELECT * FROM mantenimiento.tab_municipio_detalle where id_tab_estado=".$_POST['co_estado']." ORDER BY id ASC;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_municipio"		=> $row["id"],
			"tx_municipio"	=> $row["de_municipio"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 9:

	$sql = "SELECT * FROM mantenimiento.tab_parroquia_detalle where id_tab_municipio_detalle=".$_POST['co_municipio']." ORDER BY id ASC;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_parroquia"		=> $row["id"],
			"tx_parroquia"	=> $row["de_parroquia"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 10:

	$sql = "SELECT * FROM mantenimiento.tab_fuente_financiamiento WHERE in_activo is true;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_fuente_financiamiento" => $row["id"],
			"tx_fuente_financiamiento" => $row["de_fuente_financiamiento"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 11:

	$sql = "SELECT id_tab_proyecto, tx_pa
		FROM proyecto_seguimiento.tab_proyecto_aepartida as t42
		inner join proyecto_seguimiento.tab_proyecto_ae as t39 on t42.id_tab_proyecto_ae=t39.id
		inner join mantenimiento.tab_partidas as t44 on t42.tx_pa=t44.co_partida
		WHERE t39.id_tab_proyecto='".$_POST['id_proyecto']."' AND t42.in_activo is true
		GROUP BY 1,2";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY tx_pa ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "id_proyecto"     => trim($row["id_tab_proyecto"]),
		    "co_partida"     => trim($row["tx_pa"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 12:

	$codigo = decode($_POST['co_metas']);
	try{
		$paraTransaccion->BeginTrans();
		$tabla="proyecto_seguimiento.tab_meta_fisica";
		$tquery="UPDATE";
		$id = 'id = '.$codigo;
		$variable["in_activo"] = 'FALSE';
		$variable["updated_at"] = date("Y-m-d H:i:s");
		$co_metas = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

		if ($co_metas){
			$paraTransaccion->CommitTrans();
			echo json_encode(array(
				    "success" => true,
				    "msg" => 'Registro Borrado con Exito!'
			));
		}
		else{
			$paraTransaccion->RollbackTrans();
		}
	}catch(Exception $e){
		echo json_encode(array(
			    "success" => false,
			    "msg" => "Error en Transaccion.\n".$e->getMessage()
		));
	}

		break;
	default:
	}

