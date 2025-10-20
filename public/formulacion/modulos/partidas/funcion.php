<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT * FROM mantenimiento.tab_partidas";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE id = id ";
		if($_POST['variable']!=""){$sql.=" and tx_nombre ILIKE '%".$_POST['variable']."%'";}
		if($_POST['variable']!=""){$sql.=" or co_partida ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY id_tab_ejercicio_fiscal, co_partida ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_ejercicio_fiscal"     => trim($row["id_tab_ejercicio_fiscal"]),
		    "co_partida"     => trim($row["co_partida"]),
		    "tx_nombre"     => trim($row["tx_nombre"]),
		    "tx_abreviacion"     => trim($row["tx_abreviacion"]),
		    "ace_mov"     => trim($row["ace_mov"]),
		    "edo_reg"     => trim($row["in_activo"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$codigo = decode($_POST['co_partida']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t44_partidas";
			$tquery="UPDATE";
			$id = 'co_partida = '.$codigo;
			$variable["tx_descripcion"] = decode($_POST['tx_descripcion']);
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_partida = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_partida){
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
			$tabla="t44_partidas";
			$primaryKey="co_partida";
			$variable["tx_descripcion"] = decode($_POST['tx_descripcion']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_partida = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_partida){
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
}elseif($_GET['op']==3){
	$tabla="t44_partidas";
	$tquery="UPDATE";
	$id = "co_partida = '".$_POST['co_partida']."'";
	$variable["edo_reg"] = "true"; 
	$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Habilitado con Exito!'
	));
}elseif($_GET['op']==4){
	$tabla="t44_partidas";
	$tquery="UPDATE";
	$id = "co_partida = '".$_POST['co_partida']."'";
	$variable["edo_reg"] = "false"; 
	$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Deshabilitado con Exito!'
	));
}
?>
