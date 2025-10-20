<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT id as co_ejercicio_fiscal, in_activo as edo_reg FROM mantenimiento.tab_ejercicio_fiscal";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE id = id ";
		if($_POST['variable']!=""){$sql.=" and id::text ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY id DESC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_ejercicio_fiscal"     => trim($row["co_ejercicio_fiscal"]),
		    "edo_reg"     => trim($row["edo_reg"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$codigo = decode($_POST['co_ejercicio_fiscal']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t25_ejercicio_fiscal";
			$tquery="UPDATE";
			$id = 'co_ejercicio_fiscal = '.$codigo;
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_ejercicio_fiscal = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_ejercicio_fiscal){
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
			$tabla="t25_ejercicio_fiscal";
			$primaryKey="co_ejercicio_fiscal";
			$variable["co_ejercicio_fiscal"] = decode($_POST['co_ejercicio_fiscal']);
			$variable["edo_reg"] = 'TRUE';
			$co_ejercicio_fiscal = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_ejercicio_fiscal){
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
	$tabla="mantenimiento.tab_ejercicio_fiscal";
	$tquery="UPDATE";
	$id = "id = '".$_POST['co_ejercicio_fiscal']."'";
	$variable["in_activo"] = "true"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Habilitado con Exito!'
	));
}elseif($_GET['op']==4){
	$tabla="mantenimiento.tab_ejercicio_fiscal";
	$tquery="UPDATE";
	$id = "id = '".$_POST['co_ejercicio_fiscal']."'";
	$variable["in_activo"] = "false"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Deshabilitado con Exito!'
	));
}
?>
