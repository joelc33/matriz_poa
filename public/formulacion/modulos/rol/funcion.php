<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

	$sql = "SELECT * FROM autenticacion.tab_rol";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE id = id ";
		if($_POST['variable']!=""){$sql.=" and de_rol ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 10: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_rol"     => trim($row["id"]),
		    "tx_rol"     => trim(utf8_encode($row["de_rol"])),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

?>
