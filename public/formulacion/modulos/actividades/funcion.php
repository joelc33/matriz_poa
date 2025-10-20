<?php
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT co_proyectos, t26.id_ejercicio, id_proyecto, nombre, tx_ejecutor, monto, tx_estatus, monto_cargado(id_proyecto) as mo_registrado, coalesce(null, t26.co_estatus = 3) as reabrir, coalesce(null, t26.co_estatus = 1) as eliminar
	FROM t26_proyectos as t26
	inner join mantenimiento.tab_ejecutores as t24 on t26.id_ejecutor=t24.id_ejecutor
	inner join t31_estatus as t31 on t26.co_estatus=t31.co_estatus"; 
	if($_SESSION['co_rol']>2){
		$sql.=" WHERE t26.id_ejecutor = '".$_SESSION['id_ejecutor']."' and t26.edo_reg is true and t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' ";	 
	}else{
		$sql.=" WHERE co_proyectos = co_proyectos and t26.edo_reg is true and t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' ";
	}      
	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" and tx_ejecutor ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 10: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t26.id_ejecutor, co_proyectos ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
			"co_proyectos"     => trim($row["co_proyectos"]),
			"id_ejercicio"     => trim($row["id_ejercicio"]),
			"id_proyecto"     => trim($row["id_proyecto"]),
			"nombre"     => trim($row["nombre"]),
			"tx_ejecutor"     => trim($row["tx_ejecutor"]),
			"monto"     => trim($row["monto"]),
			"mo_registrado"     => trim($row["mo_registrado"]),
			"tx_estatus"     => trim($row["tx_estatus"]),
			"eliminar" => ( $row["eliminar"] == 't' ),
			"reabrir" => ( $row["reabrir"] == 't' )
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
} else if( $_GET['op'] == 2 ) {
	$co_usuario = $_SESSION['co_usuario'];
	$co_rol = $_SESSION['co_rol'];
	$local = $co_rol > 2;
	$params = array();
    $selParams = array();

	$cuenta = 'select count(*) as c ';
	$select = <<<EOT
select t46.id, t52.nombre, tx_ejecutor, monto, tx_estatus,
coalesce(monto_calc, 0) as monto_calc,
coalesce(?, t46.id_estatus = 3) as reabrir,
coalesce(?, t46.id_estatus = 1) as eliminar,
'AC' || t24.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo
EOT;

	if ( $local ) { //planificador local sólo ve los de su ejecutor
		$selParams[] = 'f';
		$selParams[] = null;
		$params[] = $co_usuario;
		$cuerpo = <<<EOT
 from autenticacion.tab_usuarios as t01
	join mantenimiento.tab_funcionario as t29 on t29.id_tab_usuarios = t01.id
	join mantenimiento.tab_ejecutores as t24 on t24.id = t29.id_tab_ejecutores
	join t46_acciones_centralizadas as t46 on t46.id_ejecutor = t24.id_ejecutor
	join t52_ac_predefinidas as t52 on t52.id = t46.id_accion
	join t31_estatus as t31 on t46.id_estatus = t31.co_estatus
where t01.id = ?
	and t46.id_ejercicio = ?
	and t01.in_estatus
	and t46.edo_reg
EOT;
	} else {
		$selParams[] = null;
		$selParams[] = null; //se puede eliminar independiente del estado?
		$cuerpo = <<<EOT
 from t46_acciones_centralizadas as t46
	join t52_ac_predefinidas as t52 on t52.id = t46.id_accion
	join mantenimiento.tab_ejecutores as t24 on t24.id_ejecutor = t46.id_ejecutor
	join t31_estatus as t31 on t46.id_estatus = t31.co_estatus
where t46.edo_reg is true and t46.id_ejercicio = ?
EOT;
	}

	$order = ' ORDER BY t24.id_ejecutor, id_accion ASC';

			$params[] = $_SESSION['ejercicio_fiscal'];

	if($_POST['BuscarBy'] == 'true' ){
		if( !empty( $_POST['variable'] ) ){
			$cuerpo .= <<<EOT
 and t24.tx_ejecutor ILIKE '%' || ? || '%'
EOT;
			$params[] = $_POST['variable'];
			//$params[] = $_POST['variable'];
			//$params[] = $_POST['variable'];
		}
	}

	if( $_POST['paginar'] == 'si' ) {
		//total sin paginar
		$cuenta = $comunes->ObtenerFilasBySqlSelect( $cuenta.$cuerpo, $params );
		$resp['total'] = $cuenta[0]['c'];

		$order .= ' LIMIT ? OFFSET ?';

		$params[] = ( $_POST['limit'] == null ) ? 0 : intval( $_POST['limit'] );
		$params[] = ( $_POST['start'] == null ) ? 0 : intval( $_POST['start'] );
	}

	$result = $comunes->ObtenerFilasBySqlSelect(
		$select.$cuerpo.$order, array_merge( $selParams, $params )
	);

	$data = array();
	foreach( $result as $key => $row ) {
		$data[] = array(
			'id' => $row['id'],
			'codigo' => $row['codigo'],
			'nombre' => trim( $row['nombre'] ),
			'tx_ejecutor' => trim( $row['tx_ejecutor'] ),
			'monto' => $row['monto'],
			'monto_calc' => $row['monto_calc'],
			'tx_estatus' => trim( $row['tx_estatus'] ),
			'eliminar' => ( $row['eliminar'] == 't' ),
			'reabrir' => ( $row['reabrir'] == 't' )
		);
	}
	if ( !isset( $resp['total'] ) ) {
		$resp['total'] = count( $data );
	}
	$resp['success'] = true;
	$resp['data'] = $data;
	echo json_encode( $resp );
}

