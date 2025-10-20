<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT id, id_ejecutor, tx_ejecutor, car_01, car_02, car_03, car_04, 
       id_tab_tipo_ejecutor, id_tab_ambito_ejecutor, codigo_01, codigo_eje, 
       mantenimiento.sp_in_ejecutor( id, ".$_SESSION['ejercicio_fiscal'].") as in_activo, created_at, updated_at, de_correo, de_telefono FROM mantenimiento.tab_ejecutores ";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE id = id ";
		if($_POST['variable']!=""){$sql.=" and tx_ejecutor ILIKE '%".$_POST['variable']."%'";}
		if($_POST['variable']!=""){$sql.=" or id_ejecutor ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY id_ejecutor ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_ejecutores"     => trim($row["id"]),
		    "id_ejecutor"     => trim($row["id_ejecutor"]),
		    "tx_ejecutor"     => trim($row["tx_ejecutor"]),
		    "car_01"     => trim($row["car_01"]),
		    "car_02"     => trim($row["car_02"]),
		    "car_03"     => trim($row["car_03"]),
		    "car_04"     => trim($row["car_04"]),
		    "co_tipo_ejecutor"     => trim($row["id_tab_tipo_ejecutor"]),
		    "id_ambito_ejecutor"     => trim($row["id_tab_ambito_ejecutor"]),
		    "codigo_01"     => trim($row["codigo_01"]),
		    "codigo_eje"     => trim($row["codigo_eje"]),
		    "edo_reg"     => trim($row["in_activo"]),
		    "de_correo"     => trim($row["de_correo"]),
		    "de_telefono"     => trim($row["de_telefono"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$codigo = decode($_POST['co_ejecutores']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="mantenimiento.tab_ejecutores";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["id_ejecutor"] = decode($_POST['id_ejecutor']);
			$variable["tx_ejecutor"] = decode($_POST['tx_ejecutor']);
			$variable["car_01"] = decode($_POST['car_01']);
			$variable["car_02"] = decode($_POST['car_02']);
			$variable["car_03"] = decode($_POST['car_03']);
			$variable["car_04"] = decode($_POST['car_04']);
			$variable["id_tab_tipo_ejecutor"] = decode($_POST['co_tipo_ejecutor']);
			$variable["id_tab_ambito_ejecutor"] = decode($_POST['id_ambito_ejecutor']);
			$variable["codigo_01"] = decode($_POST['codigo_01']);
			$variable["codigo_eje"] = decode($_POST['codigo_eje']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$variable["de_correo"] = decode($_POST['de_correo']);
			$variable["de_telefono"] = decode($_POST['de_telefono']);
			$co_ejecutores = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_ejecutores){
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
			$tabla="mantenimiento.tab_ejecutores";
			$primaryKey="id";
			$variable["id_ejecutor"] = decode($_POST['id_ejecutor']);
			$variable["tx_ejecutor"] = decode($_POST['tx_ejecutor']);
			$variable["car_01"] = decode($_POST['car_01']);
			$variable["car_02"] = decode($_POST['car_02']);
			$variable["car_03"] = decode($_POST['car_03']);
			$variable["car_04"] = decode($_POST['car_04']);
			$variable["id_tab_tipo_ejecutor"] = decode($_POST['co_tipo_ejecutor']);
			$variable["id_tab_ambito_ejecutor"] = decode($_POST['id_ambito_ejecutor']);
			$variable["codigo_01"] = decode($_POST['codigo_01']);
			$variable["codigo_eje"] = decode($_POST['codigo_eje']);
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = 'TRUE';
			$variable["in_verificado"] = 'FALSE';
			$variable["de_correo"] = decode($_POST['de_correo']);
			$variable["de_telefono"] = decode($_POST['de_telefono']);
			$co_ejecutores = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_ejecutores){
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
	$sql = "SELECT id, de_tipo_ejecutor, in_activo, created_at, updated_at
  FROM mantenimiento.tab_tipo_ejecutor WHERE in_activo is true;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_tipo_ejecutor"		=> $row["id"],
			"tx_tipo_ejecutor"	=> utf8_encode($row["de_tipo_ejecutor"]), 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==4){
	$sql = "SELECT id, de_ambito_ejecutor, in_activo, created_at, updated_at
  FROM mantenimiento.tab_ambito_ejecutor WHERE in_activo is true;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"id_ambito_ejecutor"		=> $row["id"],
			"tx_ambito_ejecutor"	=> utf8_encode($row["de_ambito_ejecutor"]), 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==5){

	/*$tabla="mantenimiento.tab_ejecutores";
	$tquery="UPDATE";
	$id = 'id = '.$_POST['co_ejecutores'];
	$variable["in_activo"] = "false"; 
	$variable["updated_at"] = date("Y-m-d H:i:s");
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);*/

	$tabla="mantenimiento.tab_ejecutores_ef";
	$tquery="UPDATE";
	$id = 'id_tab_ejecutores = '.$_POST['co_ejecutores'].' and id_tab_ejercicio_fiscal = '.$_SESSION['ejercicio_fiscal'];
	$variable["in_activo"] = "false"; 
	$variable["updated_at"] = date("Y-m-d H:i:s");
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Deshabilitado con Exito!'
	));
}elseif($_GET['op']==6){

	/*$tabla="mantenimiento.tab_ejecutores";
	$tquery="UPDATE";
	$id = 'id = '.$_POST['co_ejecutores'];
	$variable["in_activo"] = "true"; 
	$variable["updated_at"] = date("Y-m-d H:i:s");
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);*/

	$tabla="mantenimiento.tab_ejecutores_ef";
	$tquery="UPDATE";
	$id = 'id_tab_ejecutores = '.$_POST['co_ejecutores'].' and id_tab_ejercicio_fiscal = '.$_SESSION['ejercicio_fiscal'];
	$variable["in_activo"] = "true"; 
	$variable["updated_at"] = date("Y-m-d H:i:s");
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Habilitado con Exito!'
	));
}
?>
