<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT * FROM mantenimiento.tab_fuente_financiamiento";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE id = id ";
		if($_POST['variable']!=""){$sql.=" and de_fuente_financiamiento ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_fuente"     => trim($row["id"]),
		    "tx_fuente"     => trim($row["de_fuente_financiamiento"]),
		    "edo_reg"     => trim($row["in_activo"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$codigo = decode($_POST['co_fuente']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="mantenimiento.tab_fuente_financiamiento";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["de_fuente_financiamiento"] = decode($_POST['tx_fuente']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$co_fuente = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_fuente){
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
			$tabla="mantenimiento.tab_fuente_financiamiento";
			$primaryKey="id";
			$variable["de_fuente_financiamiento"] = decode($_POST['tx_fuente']);
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = 'TRUE';
			$co_fuente = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_fuente){
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
