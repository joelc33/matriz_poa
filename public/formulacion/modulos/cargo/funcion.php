<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT * FROM t28_cargo";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE co_cargo = co_cargo ";
		if($_POST['variable']!=""){$sql.=" and tx_cargo ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_cargo ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_cargo"     => trim($row["co_cargo"]),
		    "tx_cargo"     => trim($row["tx_cargo"]),
		    "edo_reg"     => trim($row["edo_reg"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$codigo = decode($_POST['co_cargo']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t28_cargo";
			$tquery="UPDATE";
			$id = 'co_cargo = '.$codigo;
			$variable["tx_cargo"] = decode($_POST['tx_cargo']);
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_cargo = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_cargo){
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
			$tabla="t28_cargo";
			$primaryKey="co_cargo";
			$variable["tx_cargo"] = decode($_POST['tx_cargo']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_cargo = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_cargo){
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
}
?>
