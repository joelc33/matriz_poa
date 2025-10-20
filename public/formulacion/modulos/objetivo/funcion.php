<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT * FROM t20_planes";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE co_planes = co_planes ";
		if($_POST['variable']!=""){$sql.=" and tx_descripcion ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_planes ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_planes"     => trim($row["co_planes"]),
		    "co_objetivo_historico"     => trim($row["co_objetivo_historico"]),
		    "co_objetivo_nacional"     => trim($row["co_objetivo_nacional"]),
		    "co_objetivo_estrategico"     => trim($row["co_objetivo_estrategico"]),
		    "co_objetivo_general"     => trim($row["co_objetivo_general"]),
		    "nu_nivel"     => trim($row["nu_nivel"]),
		    "tx_codigo"     => trim($row["tx_codigo"]),
		    "nu_codigo"     => trim($row["nu_codigo"]),
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
	$codigo = decode($_POST['co_planes']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t20_planes";
			$tquery="UPDATE";
			$id = 'co_planes = '.$codigo;
			$variable["co_objetivo_historico"] = decode($_POST['co_objetivo_historico']);
			$variable["co_objetivo_nacional"] = decode($_POST['co_objetivo_nacional']);
			$variable["co_objetivo_estrategico"] = decode($_POST['co_objetivo_estrategico']);
			$variable["co_objetivo_general"] = decode($_POST['co_objetivo_general']);
			$variable["nu_nivel"] = decode($_POST['nu_nivel']);
			$variable["tx_codigo"] = decode($_POST['tx_codigo']);
			$variable["nu_codigo"] = decode($_POST['tx_codigo']);
			$variable["tx_descripcion"] = $_POST['tx_descripcion'];
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_planes = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_planes){
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
			$tabla="t20_planes";
			$primaryKey="co_planes";
			$variable["co_objetivo_historico"] = decode($_POST['co_objetivo_historico']);
			$variable["co_objetivo_nacional"] = decode($_POST['co_objetivo_nacional']);
			$variable["co_objetivo_estrategico"] = decode($_POST['co_objetivo_estrategico']);
			$variable["co_objetivo_general"] = decode($_POST['co_objetivo_general']);
			$variable["nu_nivel"] = decode($_POST['nu_nivel']);
			$variable["tx_codigo"] = decode($_POST['tx_codigo']);
			$variable["nu_codigo"] = decode($_POST['tx_codigo']);
			$variable["tx_descripcion"] = $_POST['tx_descripcion'];
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_planes = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_planes){
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
	$tabla="t20_planes";
	$tquery="UPDATE";
	$id = 'co_planes = '.$_POST['co_planes'];
	$variable["edo_reg"] = "false"; 
	$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Deshabilitado con Exito!'
	));
}
?>
