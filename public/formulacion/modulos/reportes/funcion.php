<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT co_proyectos, id_proyecto, nombre, tx_ejecutor, monto, tx_estatus, monto_cargado(id_proyecto) as mo_registrado, t26.id_ejecutor
	FROM t26_proyectos as t26
	inner join mantenimiento.tab_ejecutores as t24 on t26.id_ejecutor=t24.id_ejecutor
	inner join t31_estatus as t31 on t26.co_estatus=t31.co_estatus
	WHERE t26.edo_reg is true AND t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' ";    

	if($_SESSION['co_rol']>2){
	//if(in_array($_SESSION['co_rol'],$roles)){
		$sql.="AND t26.id_ejecutor = '".$_SESSION['id_ejecutor']."'";	 
	}else{
		$sql.="";
	}         

	if($_POST['BuscarBy']=="true"){
		//$sql.=" WHERE co_proyectos = co_proyectos ";
		if($_POST['variable']!=""){$sql.=" and nombre ILIKE '%".$_POST['variable']."%'";}
		/*or id_proyecto ILIKE '%".$_POST['variable']."%'
		or tx_ejecutor ILIKE '%".$_POST['variable']."%'
		";}*/
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 100: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_proyectos ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyectos"     => trim($row["co_proyectos"]),
		    "id_ejecutor"     => trim($row["id_ejecutor"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "nombre"     => trim($row["nombre"]),
		    "tx_ejecutor"     => trim($row["tx_ejecutor"]),
		    "monto"     => trim($row["monto"]),
		    "mo_registrado"     => trim($row["mo_registrado"]),
		    "tx_estatus"     => trim($row["tx_estatus"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$sql = "SELECT tx_codigo_recurso, tx_tipo_recurso, tx_tipo_fondo, sum(mo_fondo) as mo_fondo
		FROM t62_proyecto_distribucion as t62
		inner join t61_tipo_fondo as t61 on t62.co_tipo_fondo=t61.co_tipo_fondo
		inner join t60_tipo_recurso as t60 on t61.co_tipo_recurso=t60.co_tipo_recurso
		inner join t26_proyectos as t26 on t62.id_proyecto=t26.id_proyecto
		WHERE t61.edo_reg is true and t26.edo_reg is true AND t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' "; 

	if($_SESSION['co_rol']>2){
	//if(in_array($_SESSION['co_rol'],$roles)){
		$sql.="AND t26.id_ejecutor = '".$_SESSION['id_ejecutor']."'";	 
	}else{
		$sql.="";
	}           

	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" AND tx_tipo_fondo ILIKE '%".$_POST['variable']."%'";}
	}

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 100: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " group by 1,2,3 ORDER BY 1 ASC LIMIT ".$limit." OFFSET ".$start;}

	$cantidadTotal = $comunes->getFilas($sql);
	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_distribucion"     => trim($row["co_proyecto_distribucion"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "tx_tipo_fondo"     => trim($row["tx_tipo_fondo"]),
		    "tx_codigo_recurso"     => trim($row["tx_codigo_recurso"]),
		    "mo_fondo"     => trim($row["mo_fondo"]),
		    "edo_reg"     => trim($row["edo_reg"]),
		    "tx_tipo_recurso"     => trim($row["tx_tipo_recurso"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET["op"]==3){
	$sql = "SELECT * FROM mantenimiento.tab_ejecutores where mantenimiento.sp_in_ejecutor( id, ".$_SESSION['ejercicio_fiscal'].") is true order by id_ejecutor ASC;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_ejecutores"		=> $row["id"],
			"tx_ejecutor"	=> $row["id_ejecutor"].' - '.$row["tx_ejecutor"], 
			"id_ejecutor"	=> $row["id_ejecutor"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}
?>
