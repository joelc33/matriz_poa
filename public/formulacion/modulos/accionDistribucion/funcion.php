<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT t41.*, tx_codigo, descripcion FROM t41_proyecto_acc_espec_dist as t41
	inner join t39_proyecto_acc_espec as t39 on t41.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	WHERE t41.id_proyecto='".$_POST['id_proyecto']."' AND t39.edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_proyecto_acc_espec_dist, tx_codigo ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec_dist"     => trim($row["co_proyecto_acc_espec_dist"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
		    "tx_codigo"     => trim($row["tx_codigo"]),
		    "descripcion"     => trim($row["descripcion"]),
		    "monto_401"     => trim($row["monto_401"]),
		    "monto_402"     => trim($row["monto_402"]),
		    "monto_403"     => trim($row["monto_403"]),
		    "monto_404"     => trim($row["monto_404"]),
		    "monto_405"     => trim($row["monto_405"]),
		    "monto_406"     => trim($row["monto_406"]),
		    "monto_407"     => trim($row["monto_407"]),
		    "monto_408"     => trim($row["monto_408"]),
		    "monto_409"     => trim($row["monto_409"]),
		    "monto_410"     => trim($row["monto_410"]),
		    "monto_411"     => trim($row["monto_411"]),
		    "monto_498"     => trim($row["monto_498"]),
		    "total"     => trim($row["total"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$sql = "SELECT t39.id_proyecto, tx_pa::integer, tx_nombre, SUM(nu_monto) AS nu_monto
		FROM t42_proyecto_acc_espec_partida as t42
		inner join t39_proyecto_acc_espec as t39 on t42.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
		inner join t26_proyectos as t26 on t39.id_proyecto = t26.id_proyecto
		inner join mantenimiento.tab_partidas as t44 on t42.tx_pa=t44.co_partida and t44.id_tab_ejercicio_fiscal = t26.id_ejercicio::int
		WHERE t39.id_proyecto='".$_POST['id_proyecto']."' AND t42.edo_reg is true AND t44.id_tab_ejercicio_fiscal=t42.id_tab_ejercicio_fiscal and sp_verificar_hijo_ae(t39.co_proyecto_acc_espec) is false
		GROUP BY 1,2,3";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY tx_pa ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "tx_partida"     => trim($row["tx_pa"].' '.$row["tx_nombre"]),
		    "nu_monto"     => trim($row["nu_monto"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==3){
	$codigo = decode($_POST['co_proyecto_acc_espec_rec']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t40_proyecto_acc_espec_rec";
			$tquery="UPDATE";
			$id = 'co_proyecto_acc_espec_rec = '.$codigo;
			$variable["fisico_01"] = decode($_POST['fisico_01']);
			$variable["fisico_02"] = decode($_POST['fisico_02']);
			$variable["fisico_03"] = decode($_POST['fisico_03']);
			$variable["fisico_04"] = decode($_POST['fisico_04']);
			$variable["fisico_05"] = decode($_POST['fisico_05']);
			$variable["fisico_06"] = decode($_POST['fisico_06']);
			$variable["fisico_07"] = decode($_POST['fisico_07']);
			$variable["fisico_08"] = decode($_POST['fisico_08']);
			$variable["fisico_09"] = decode($_POST['fisico_09']);
			$variable["fisico_10"] = decode($_POST['fisico_10']);
			$variable["fisico_11"] = decode($_POST['fisico_11']);
			$variable["fisico_12"] = decode($_POST['fisico_12']);
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_proyecto_acc_espec = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_proyecto_acc_espec){
				$paraTransaccion->CommitTrans();
				echo json_encode(array(
					    "success" => true,
					    "msg" => 'Modificación realizada exitosamente.'
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
	}else{
			try{
			$paraTransaccion->BeginTrans();
			$tabla="t39_proyecto_acc_espec";
			$primaryKey="co_proyecto_acc_espec";
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_proyecto_acc_espec = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_proyecto_acc_espec){
				$paraTransaccion->CommitTrans();
				echo json_encode(array(
					    "success" => true,
					    "msg" => 'Proceso realizado exitosamente.'
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
	}
}elseif($_GET['op']==4){
	$sql = "SELECT * FROM mantenimiento.tab_tipo_fondo WHERE in_activo is true;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_tipo_fondo"		=> $row["id"],
			"tx_tipo_fondo"	=> $row["de_tipo_fondo"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==5){
	$sql = "SELECT co_proyecto_distribucion, id_proyecto, de_tipo_fondo, mo_fondo, t62.edo_reg, de_tipo_recurso, de_codigo_recurso
		FROM t62_proyecto_distribucion as t62
		inner join mantenimiento.tab_tipo_fondo as t61 on t62.co_tipo_fondo=t61.id
		inner join mantenimiento.tab_tipo_recurso as t60 on t61.id_tab_tipo_recurso=t60.id
		WHERE id_proyecto='".$_POST['id_proyecto']."' AND t62.edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY de_codigo_recurso ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_distribucion"     => trim($row["co_proyecto_distribucion"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "tx_tipo_fondo"     => trim($row["de_tipo_fondo"]),
		    "tx_codigo_recurso"     => trim($row["de_codigo_recurso"]),
		    "mo_fondo"     => trim($row["mo_fondo"]),
		    "edo_reg"     => trim($row["edo_reg"]),
		    "tx_tipo_recurso"     => trim($row["de_codigo_recurso"].' '.$row["de_tipo_recurso"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==6){
	$info = $_POST["variables"];

	$data = json_decode(stripslashes($info));
	$co_proyecto_distribucion = $data->co_proyecto_distribucion;
	$mo_fondo = $data->mo_fondo;

	$codigo = decode($co_proyecto_distribucion);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t62_proyecto_distribucion";
			$tquery="UPDATE";
			$id = 'co_proyecto_distribucion = '.$codigo;
			$variable["mo_fondo"] = decode($mo_fondo);
			$co_proyecto_distribucion = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_proyecto_distribucion){
				$paraTransaccion->CommitTrans();
				echo json_encode(array(
					    "success" => true,
					    "msg" => 'Modificación realizada exitosamente.'
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
	}else{
			try{
			$paraTransaccion->BeginTrans();
			$tabla="t62_proyecto_distribucion";
			$primaryKey="co_proyecto_distribucion";
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_proyecto_distribucion = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_proyecto_distribucion){
				$paraTransaccion->CommitTrans();
				echo json_encode(array(
					    "success" => true,
					    "msg" => 'Proceso realizado exitosamente.'
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
	}
}elseif($_GET['op']==7){
	$sql = "SELECT co_proyecto_distribucion, id_proyecto, de_tipo_fondo, mo_fondo, t62.edo_reg, de_tipo_recurso, de_codigo_recurso
		FROM t62_proyecto_distribucion as t62
		inner join mantenimiento.tab_tipo_fondo as t61 on t62.co_tipo_fondo=t61.id
		inner join mantenimiento.tab_tipo_recurso as t60 on t61.id_tab_tipo_recurso=t60.id
		WHERE id_proyecto='".$_POST['id_proyecto']."' AND t62.edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY de_codigo_recurso ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_distribucion"     => trim($row["co_proyecto_distribucion"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "tx_tipo_fondo"     => trim($row["de_tipo_fondo"]),
		    "tx_codigo_recurso"     => trim($row["de_codigo_recurso"]),
		    "mo_fondo"     => trim($row["mo_fondo"]),
		    "edo_reg"     => trim($row["edo_reg"]),
		    "tx_tipo_recurso"     => trim($row["de_tipo_recurso"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==8){
	$codigo = decode($_POST['co_proyecto_acc_espec_rec']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t40_proyecto_acc_espec_rec";
			$tquery="UPDATE";
			$id = 'co_proyecto_acc_espec_rec = '.$codigo;
			$variable["presup_01"] = decode($_POST['presup_01']);
			$variable["presup_02"] = decode($_POST['presup_02']);
			$variable["presup_03"] = decode($_POST['presup_03']);
			$variable["presup_04"] = decode($_POST['presup_04']);
			$variable["presup_05"] = decode($_POST['presup_05']);
			$variable["presup_06"] = decode($_POST['presup_06']);
			$variable["presup_07"] = decode($_POST['presup_07']);
			$variable["presup_08"] = decode($_POST['presup_08']);
			$variable["presup_09"] = decode($_POST['presup_09']);
			$variable["presup_10"] = decode($_POST['presup_10']);
			$variable["presup_11"] = decode($_POST['presup_11']);
			$variable["presup_12"] = decode($_POST['presup_12']);
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_proyecto_acc_espec = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_proyecto_acc_espec){
				$paraTransaccion->CommitTrans();
				echo json_encode(array(
					    "success" => true,
					    "msg" => 'Modificación realizada exitosamente.'
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
	}else{
		try{

		}catch(Exception $e){
			echo json_encode(array(
				    "success" => false,
				    "msg" => "Error en Transaccion.\n".$e->getMessage()
			));
		}
	}
}
?>
