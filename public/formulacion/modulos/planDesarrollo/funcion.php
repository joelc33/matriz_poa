<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT * FROM t45_planes_zulia";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE co_planes_zulia = co_planes_zulia ";
		if($_POST['variable']!=""){$sql.=" and tx_descripcion ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_planes_zulia ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_planes_zulia"     => trim($row["co_planes_zulia"]),
		    "co_ambito_zulia"     => trim($row["co_ambito_zulia"]),
		    "co_objetivo_zulia"     => trim($row["co_objetivo_zulia"]),
		    "co_macroproblema"     => trim($row["co_macroproblema"]),
		    "co_nodo"     => trim($row["co_nodo"]),
		    "nu_nivel"     => trim($row["nu_nivel"]),
		    "co_area_estrategica"     => trim($row["co_area_estrategica"]),
		    "tx_descripcion"     => trim($row["tx_descripcion"]),
		    "edo_reg"     => trim($row["edo_reg"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$codigo = decode($_POST['co_planes_zulia']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t45_planes_zulia";
			$tquery="UPDATE";
			$id = 'co_planes_zulia = '.$codigo;
			$variable["tx_descripcion"] = decode($_POST['tx_descripcion']);
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_planes_zulia = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_planes_zulia){
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
			$tabla="t45_planes_zulia";
			$primaryKey="co_planes_zulia";
			$variable["tx_descripcion"] = decode($_POST['tx_descripcion']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_planes_zulia = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_planes_zulia){
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
	$tabla="t45_planes_zulia";
	$tquery="UPDATE";
	$id = 'co_planes_zulia = '.$_POST['co_planes_zulia'];
	$variable["edo_reg"] = "false"; 
	$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Deshabilitado con Exito!'
	));
}
?>
