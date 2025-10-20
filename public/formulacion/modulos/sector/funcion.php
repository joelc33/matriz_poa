<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT * FROM t18_sectores";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE co_sectores = co_sectores ";
		if($_POST['variable']!=""){$sql.=" and nu_descripcion ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_sectores ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_sectores"     => trim($row["co_sectores"]),
		    "co_sector"     => trim($row["co_sector"]),
		    "co_sub_sector"     => trim($row["co_sub_sector"]),
		    "nu_nivel"     => trim($row["nu_nivel"]),
		    "tx_codigo"     => trim($row["tx_codigo"]),
		    "tx_descripcion"     => trim($row["tx_descripcion"]),
		    "nu_descripcion"     => trim($row["nu_descripcion"]),
		    "edo_reg"     => trim($row["edo_reg"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$codigo = decode($_POST['co_sectores']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t18_sectores";
			$tquery="UPDATE";
			$id = 'co_sectores = '.$codigo;
			$variable["co_sector"] = decode($_POST['co_sector']);
			$variable["co_sub_sector"] = decode($_POST['co_sub_sector']);
			$variable["nu_nivel"] = decode($_POST['nu_nivel']);
			$variable["tx_codigo"] = decode($_POST['co_sector'].''.$_POST['co_sub_sector']);
			$variable["tx_descripcion"] = decode($_POST['tx_descripcion']);
			$variable["nu_descripcion"] = ($_POST['co_sector'].''.$_POST['co_sub_sector']." - ".$_POST['tx_descripcion']);
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
			$tabla="t18_sectores";
			$primaryKey="co_sectores";
			$variable["co_sector"] = decode($_POST['co_sector']);
			$variable["co_sub_sector"] = decode($_POST['co_sub_sector']);
			$variable["nu_nivel"] = decode($_POST['nu_nivel']);
			$variable["tx_codigo"] = decode($_POST['co_sector'].''.$_POST['co_sub_sector']);
			$variable["tx_descripcion"] = decode($_POST['tx_descripcion']);
			$variable["nu_descripcion"] = ($_POST['co_sector'].''.$_POST['co_sub_sector']." - ".$_POST['tx_descripcion']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_sectores = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_sectores){
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
	$tabla="t18_sectores";
	$tquery="UPDATE";
	$id = 'co_sectores = '.$_POST['co_sectores'];
	$variable["edo_reg"] = "false"; 
	$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Deshabilitado con Exito!'
	));
}
?>
