<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

function formatoDinero($numero, $fractional=true) {
    if ($fractional) {
        $numero = sprintf('%.2f', $numero);
    }
    while (true) {
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $numero);
        if ($replaced != $numero) {
            $numero = $replaced;
        } else {
            break;
        }
    }
    return "Bs. ".$numero;
}

switch ($_POST['op']) {

	case 1:

	$sql = "SELECT t26.id, nu_codigo, de_nombre, tx_ejecutor, mo_proyecto, de_estatus,
	proyecto_seguimiento.sp_proyecto_mo_cargado(nu_codigo) as mo_registrado, id_tab_tipo_registro
	FROM proyecto_seguimiento.tab_proyecto as t26
	inner join mantenimiento.tab_ejecutores as t24 on t26.id_tab_ejecutores = t24.id_ejecutor
	inner join mantenimiento.tab_estatus as t31 on t26.id_tab_estatus = t31.id"; 
	if($_SESSION['co_rol']>2){
		$sql.=" WHERE t26.id_tab_ejecutores = '".$_SESSION['id_ejecutor']."' and t26.in_activo is true and t26.id_tab_ejercicio_fiscal = '".$_SESSION['ejercicio_fiscal']."' ";	 
	}else{
		$sql.=" WHERE t26.id = t26.id and t26.in_activo is true and t26.id_tab_ejercicio_fiscal = '".$_SESSION['ejercicio_fiscal']."' ";
	}      
	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" and de_nombre ILIKE '%".$_POST['variable']."%'
		or nu_codigo ILIKE '%".$_POST['variable']."%'
		or tx_ejecutor ILIKE '%".$_POST['variable']."%'
		";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 10: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t26.id_tab_ejecutores, t26.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyectos"     => trim($row["id"]),
		    "id_proyecto"     => trim($row["nu_codigo"]),
		    "nombre"     => trim($row["de_nombre"]),
		    "tx_ejecutor"     => trim($row["tx_ejecutor"]),
		    "monto"     => trim($row["mo_proyecto"]),
		    "mo_registrado"     => trim($row["mo_registrado"]),
		    "tx_estatus"     => trim($row["de_estatus"]),
		    "id_tab_tipo_registro"     => trim($row["id_tab_tipo_registro"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 2:

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
 from t01_usuario as t01
	join t29_funcionario as t29 on t29.co_funcionario = t01.co_funcionario
	join t24_ejecutores as t24 on t24.co_ejecutores = t29.co_ejecutores
	join t46_acciones_centralizadas as t46 on t46.id_ejecutor = t24.id_ejecutor
	join t52_ac_predefinidas as t52 on t52.id = t46.id_accion
	join t31_estatus as t31 on t46.id_estatus = t31.co_estatus
where t01.co_usuario = ?
	and t01.edo_reg
	and t46.edo_reg
EOT;
	} else {
		$selParams[] = null;
		$selParams[] = null; //se puede eliminar independiente del estado?
		$cuerpo = <<<EOT
 from t46_acciones_centralizadas as t46
	join t52_ac_predefinidas as t52 on t52.id = t46.id_accion
	join t24_ejecutores as t24 on t24.id_ejecutor = t46.id_ejecutor
	join t31_estatus as t31 on t46.id_estatus = t31.co_estatus
where t46.edo_reg
EOT;
	}

	$order = ' ORDER BY t24.id_ejecutor, id_accion ASC';

	if($_POST['BuscarBy'] == 'true' ){
		if( !empty( $_POST['variable'] ) ){
			$cuerpo .= <<<EOT
 and t52.nombre ILIKE '%' || ? || '%' or t24.tx_ejecutor ILIKE '%' || ? || '%'
or ('AC' || t24.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0'))
  ILIKE '%' || ? || '%'
EOT;
			$params[] = $_POST['variable'];
			$params[] = $_POST['variable'];
			$params[] = $_POST['variable'];
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

		break;
	case 3:

	$sql = "SELECT * FROM mantenimiento.tab_ejecutores WHERE in_activo is true;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_ejecutores"		=> $row["id"],
			"id_ejecutor"	=> $row["id_ejecutor"], 
			"tx_ejecutor"	=> $row["tx_ejecutor"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 4:

	$sql = "SELECT * FROM mantenimiento.tab_estatus_proyecto WHERE in_activo is true;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_estatus_proyecto"		=> $row["id"],
			"tx_estatus_proyecto"	=> $row["de_estatus_proyecto"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 5:

	$sql = "SELECT * FROM mantenimiento.tab_situacion_presupuestaria WHERE in_activo is true;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_situacion_presupuestaria"		=> $row["id"],
			"tx_situacion_presupuestaria"	=> $row["de_situacion_presupuestaria"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 6:

	$sql = "SELECT * FROM mantenimiento.tab_sectores where nu_nivel=1 and in_activo is true order by co_sector asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_sector"		=> $row["co_sector"],
			"tx_descripcion"	=> $row["nu_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 7:

	$sql = "SELECT * FROM mantenimiento.tab_sectores where co_sector='".$_POST['co_sector']."' and nu_nivel=2 and in_activo is true order by co_sector asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_sectores" => $row["id"],
			"co_sub_sector"		=> $row["co_sub_sector"],
			"tx_sub_sector"	=> $row["nu_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 8:

	$sql = "SELECT * FROM mantenimiento.tab_plan_operativo where in_activo is true;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_plan"		=> $row["id"],
			"tx_plan"	=> $row["de_plan_operativo"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 9:

	$sql = "SELECT * FROM mantenimiento.tab_planes where nu_nivel=1 and in_activo is true order by co_objetivo_historico asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_objetivo_historico"		=> $row["co_objetivo_historico"],
			"tx_objetivo_historico"	=> $row["tx_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 10:

	$sql = "SELECT * FROM mantenimiento.tab_planes where co_objetivo_historico='".$_POST['co_objetivo_historico']."' and nu_nivel=2 and in_activo is true order by co_objetivo_nacional asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_objetivo_nacional"		=> $row["co_objetivo_nacional"],
			"tx_objetivo_nacional"	=> $row["tx_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 11:

	$sql = "SELECT * FROM mantenimiento.tab_planes where co_objetivo_nacional='".$_POST['co_objetivo_nacional']."' and co_objetivo_historico='".$_POST['co_objetivo_historico']."' and nu_nivel=3 and in_activo is true order by co_objetivo_estrategico asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_objetivo_estrategico"		=> $row["co_objetivo_estrategico"],
			"tx_objetivo_estrategico"	=> $row["tx_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 12:

	$sql = "SELECT * FROM mantenimiento.tab_planes where co_objetivo_historico='".$_POST['co_objetivo_historico']."' and co_objetivo_nacional='".$_POST['co_objetivo_nacional']."' and co_objetivo_estrategico='".$_POST['co_objetivo_estrategico']."' and nu_nivel=4 and in_activo is true order by co_objetivo_estrategico asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_objetivo_general"		=> $row["co_objetivo_general"],
			"tx_objetivo_general"	=> $row["tx_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 13:

	$sql = "SELECT * FROM mantenimiento.tab_planes_zulia where nu_nivel=0 and in_activo is true order by co_area_estrategica asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_area_estrategica"		=> $row["co_area_estrategica"],
			"tx_area_estrategica"	=> $row["tx_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 14:

	$sql = "SELECT * FROM mantenimiento.tab_planes_zulia where co_area_estrategica='".$_POST['co_area_estrategica']."' and nu_nivel=1 and in_activo is true order by co_ambito_zulia asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_ambito_zulia"		=> $row["co_ambito_zulia"],
			"tx_ambito_zulia"	=> $row["tx_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 15:

	$sql = "SELECT * FROM mantenimiento.tab_planes_zulia where co_ambito_zulia='".$_POST['co_ambito_zulia']."' and nu_nivel=2 and in_activo is true order by co_objetivo_zulia asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_objetivo_zulia"		=> $row["co_objetivo_zulia"],
			"tx_objetivo_zulia"	=> $row["tx_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 16:

	$sql = "SELECT * FROM mantenimiento.tab_planes_zulia where co_ambito_zulia='".$_POST['co_ambito_zulia']."' and nu_nivel=3 and in_activo is true order by co_macroproblema asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_macroproblema"		=> $row["co_macroproblema"],
			"tx_macroproblema"	=> $row["tx_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 17:

	$sql = "SELECT * FROM mantenimiento.tab_planes_zulia where co_macroproblema='".$_POST['co_macroproblema']."' and nu_nivel=4 and in_activo is true order by co_nodo asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_nodo"		=> $row["co_nodo"],
			"tx_nodo"	=> $row["tx_descripcion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 18:

	$sql = "SELECT * FROM mantenimiento.tab_ambito_localizacion order by id asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_ambito"		=> $row["id"],
			"tx_ambito"	=> $row["de_ambito_localizacion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 19:

	$sql = "SELECT t34.id, de_pais, de_estado, de_municipio, de_parroquia FROM proyecto_seguimiento.tab_proyecto_locnac as t34
	inner join mantenimiento.tab_pais as t15 on t34.id_tab_pais=t15.id
	inner join mantenimiento.tab_estado as t12 on t34.id_tab_estado=t12.id
	inner join mantenimiento.tab_municipio as t13 on t34.id_tab_municipio=t13.id
	inner join mantenimiento.tab_parroquia as t14 on t34.id_tab_parroquia=t14.id
	WHERE id_tab_proyecto='".$_POST['id_proyecto']."' AND t34.in_activo is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 10: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t34.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_localizacion_nacional"     => trim($row["id"]),
		    "tx_pais"     => trim($row["de_pais"]),
		    "tx_estado"     => trim($row["de_estado"]),
		    "tx_municipio"     => trim($row["de_municipio"]),
		    "tx_parroquia"     => trim($row["de_parroquia"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 20:

	$sql = "SELECT t35.id, tx_codigo_comuna, tx_agregacion_comunal, de_estado, de_municipio, de_parroquia FROM proyecto_seguimiento.tab_proyecto_loccom as t35
	inner join mantenimiento.tab_estado as t12 on t35.id_tab_estado=t12.id
	inner join mantenimiento.tab_municipio as t13 on t35.id_tab_municipio=t13.id
	inner join mantenimiento.tab_parroquia as t14 on t35.id_tab_parroquia=t14.id
	WHERE id_tab_proyecto='".$_POST['id_proyecto']."' AND t35.in_activo is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 10: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t35.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_localizacion_comunal"     => trim($row["id"]),
		    "tx_codigo_comuna"     => trim($row["tx_codigo_comuna"]),
		    "tx_agregacion_comunal"     => trim($row["tx_agregacion_comunal"]),
		    "tx_estado"     => trim($row["de_estado"]),
		    "tx_municipio"     => trim($row["de_municipio"]),
		    "tx_parroquia"     => trim($row["de_parroquia"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 21:

	$sql = "SELECT * FROM mantenimiento.tab_pais;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_pais"		=> $row["id"],
			"tx_pais"	=> $row["de_pais"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 22:

	$sql = "SELECT * FROM mantenimiento.tab_estado;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_estado"		=> $row["id"],
			"tx_estado"	=> $row["de_estado"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 23:

	$sql = "SELECT * FROM mantenimiento.tab_municipio where id_tab_estado=".$_POST['co_estado'].";";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_municipio"		=> $row["id"],
			"tx_municipio"	=> $row["de_municipio"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 24:

	$sql = "SELECT * FROM mantenimiento.tab_parroquia where id_tab_municipio=".$_POST['co_municipio'].";";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_parroquia"		=> $row["id"],
			"tx_parroquia"	=> $row["de_parroquia"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 25:

	$codigo = decode($_POST['co_proyecto_localizacion_nacional']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="proyecto_seguimiento.tab_proyecto_locnac";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["id_tab_pais"] = decode($_POST['co_pais']);
			$variable["id_tab_estado"] = decode($_POST['co_estado']);
			$variable["id_tab_municipio"] = decode($_POST['co_municipio']);
			$variable["id_tab_parroquia"] = decode($_POST['co_parroquia']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($query){
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
			$tabla="proyecto_seguimiento.tab_proyecto_locnac";
			$tquery="INSERT";
			$variable["id_tab_proyecto"] = decode($_POST['id_proyecto']);
			$variable["id_tab_pais"] = decode($_POST['co_pais']);
			$variable["id_tab_estado"] = decode($_POST['co_estado']);
			$variable["id_tab_municipio"] = decode($_POST['co_municipio']);
			$variable["id_tab_parroquia"] = decode($_POST['co_parroquia']);
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = "TRUE";
			$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($query){
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

		break;
	case 26:

	$tabla="proyecto_seguimiento.tab_proyecto_locnac";
	$tquery="UPDATE";
	$id = 'id = '.$_POST['co_proyecto_localizacion_nacional'];
	$variable["in_activo"] = "false"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Eliminado con Exito!'
	));

		break;
	case 27:

	$codigo = decode($_POST['co_proyecto_localizacion_comunal']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="proyecto_seguimiento.tab_proyecto_loccom";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["tx_codigo_comuna"] = decode($_POST['tx_codigo_comuna']);
			$variable["tx_agregacion_comunal"] = decode($_POST['tx_agregacion_comunal']);
			$variable["id_tab_estado"] = decode($_POST['co_estado']);
			$variable["id_tab_municipio"] = decode($_POST['co_municipio']);
			$variable["id_tab_parroquia"] = decode($_POST['co_parroquia']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($query){
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
			$tabla="proyecto_seguimiento.tab_proyecto_loccom";
			$tquery="INSERT";
			$variable["id_tab_proyecto"] = decode($_POST['id_proyecto']);
			$variable["tx_codigo_comuna"] = decode($_POST['tx_codigo_comuna']);
			$variable["tx_agregacion_comunal"] = decode($_POST['tx_agregacion_comunal']);
			$variable["id_tab_estado"] = decode($_POST['co_estado']);
			$variable["id_tab_municipio"] = decode($_POST['co_municipio']);
			$variable["id_tab_parroquia"] = decode($_POST['co_parroquia']);
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = "TRUE";
			$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($query){
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

		break;
	case 28:

	$tabla="proyecto_seguimiento.tab_proyecto_loccom";
	$tquery="UPDATE";
	$id = 'id = '.$_POST['co_proyecto_localizacion_comunal'];
	$variable["in_activo"] = "false"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Eliminado con Exito!'
	));

		break;
	case 29:

	$sql = "SELECT * FROM mantenimiento.tab_unidad_medida;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_unidades_medida"		=> $row["id"],
			"tx_unidades_medida"	=> $row["de_unidad_medida"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 30:

	$sql = "SELECT nu_codigo, de_nombre FROM proyecto_seguimiento.tab_proyecto WHERE in_activo is true ORDER BY nu_codigo ASC;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_vinculo_proyecto"		=> $row["nu_codigo"],
			"tx_vinculo_proyecto"	=> $row["nu_codigo"].'-'.$row["de_nombre"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 31:

	$sql = "SELECT t39.*, de_unidad_medida, tx_ejecutor, proyecto_seguimiento.sp_proyecto_ae_mo_cargado(t39.id) as mo_cargado FROM proyecto_seguimiento.tab_proyecto_ae as t39
	inner join mantenimiento.tab_unidad_medida as t21 on t39.id_tab_unidad_medida=t21.id
	inner join mantenimiento.tab_ejecutores as t24 on t39.co_ejecutores=t24.id
	WHERE id_tab_proyecto ='".$_POST['id_proyecto']."' AND t39.in_activo is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 5: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t39.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec"     => trim($row["id"]),
		    "id_proyecto"     => trim($row["id_tab_proyecto"]),
		    "tx_codigo"     => trim($row["tx_codigo"]),
		    "descripcion"     => trim($row["descripcion"]),
		    "co_unidades_medida"     => trim($row["de_unidad_medida"]),
		    "meta"     => trim($row["meta"]),
		    "ponderacion"     => trim($row["ponderacion"]),
		    "bien_servicio"     => trim($row["bien_servicio"]),
		    "total"     => trim($row["total"]),
		    "mo_cargado"     => trim($row["mo_cargado"]),
		    "fec_inicio"     => trim(date_format(date_create($row["fec_inicio"]),'d/m/Y')),
		    "fec_termino"     => trim(date_format(date_create($row["fec_termino"]),'d/m/Y')),
		    "co_ejecutores"     => trim($row["tx_ejecutor"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 32:

	$sql = "SELECT t40.id, t40.id_tab_proyecto, t40.id_tab_proyecto_ae, 'Presupuestaria (Bs)' AS tx_distribucion,
	presup_01, presup_02, presup_03, presup_04, presup_05, presup_06, 
	presup_07, presup_08, presup_09, presup_10, presup_11, presup_12, 
	t40.created_at, t40.in_activo, tx_codigo, descripcion
	FROM proyecto_seguimiento.tab_proyecto_aerec as t40
	inner join proyecto_seguimiento.tab_proyecto_ae as t39 on t40.id_tab_proyecto_ae=t39.id
	WHERE t40.id_tab_proyecto='".$_POST['id_proyecto']."' AND t40.in_activo is true
	UNION
	SELECT t40.id, t40.id_tab_proyecto, t40.id_tab_proyecto_ae,'Fisico' AS tx_distribucion, 
	fisico_01, fisico_02, fisico_03, fisico_04, fisico_05, fisico_06, 
	fisico_07, fisico_08, fisico_09, fisico_10, fisico_11, fisico_12,
	t40.created_at, t40.in_activo, tx_codigo, descripcion
	FROM proyecto_seguimiento.tab_proyecto_aerec as t40
	inner join proyecto_seguimiento.tab_proyecto_ae as t39 on t40.id_tab_proyecto_ae=t39.id
	WHERE t40.id_tab_proyecto='".$_POST['id_proyecto']."' AND t40.in_activo is true ";

	/*$sql = "SELECT t40.*, tx_codigo, descripcion FROM t40_proyecto_acc_espec_rec as t40
	inner join t39_proyecto_acc_espec as t39 on t40.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	WHERE t40.id_proyecto='".$_POST['id_proyecto']."' AND t40.edo_reg is true";*/

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY id, tx_distribucion, created_at ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$trimestre_01 = $row["presup_01"]+$row["presup_02"]+$row["presup_03"];
		$trimestre_02 = $row["presup_04"]+$row["presup_05"]+$row["presup_06"];
		$trimestre_03 = $row["presup_07"]+$row["presup_08"]+$row["presup_09"];
		$trimestre_04 = $row["presup_10"]+$row["presup_11"]+$row["presup_12"];
		$mo_total = $trimestre_01+$trimestre_02+$trimestre_03+$trimestre_04;
		$condicion = $row["tx_distribucion"];
		if($condicion=="Presupuestaria (Bs)"){
		$data[] = array(
		    "co_proyecto_acc_espec_rec"     => trim($row["id"]),
		    "id_proyecto"     => trim($row["id_tab_proyecto"]),
		    "co_proyecto_acc_espec"     => trim($row["id_tab_proyecto_ae"]),
		    "tx_codigo"     => trim($row["tx_codigo"]),
		    "tx_distribucion"     => trim($row["tx_distribucion"]),
		    "descripcion"     => trim($row["descripcion"]),
		    "presup_01"     => trim(formatoDinero($row["presup_01"])),
		    "presup_02"     => trim(formatoDinero($row["presup_02"])),
		    "presup_03"     => trim(formatoDinero($row["presup_03"])),
		    "trimestre_01"     => trim(formatoDinero($trimestre_01)),
		    "presup_04"     => trim(formatoDinero($row["presup_04"])),
		    "presup_05"     => trim(formatoDinero($row["presup_05"])),
		    "presup_06"     => trim(formatoDinero($row["presup_06"])),
		    "trimestre_02"     => trim(formatoDinero($trimestre_02)),
		    "presup_07"     => trim(formatoDinero($row["presup_07"])),
		    "presup_08"     => trim(formatoDinero($row["presup_08"])),
		    "presup_09"     => trim(formatoDinero($row["presup_09"])),
		    "trimestre_03"     => trim(formatoDinero($trimestre_03)),
		    "presup_10"     => trim(formatoDinero($row["presup_10"])),
		    "presup_11"     => trim(formatoDinero($row["presup_11"])),
		    "presup_12"     => trim(formatoDinero($row["presup_12"])),
		    "trimestre_04"     => trim(formatoDinero($trimestre_04)),
		    "mo_total"     => trim(formatoDinero($mo_total)),
		);
	}else{
		$data[] = array(
		    "co_proyecto_acc_espec_rec"     => trim($row["id"]),
		    "id_proyecto"     => trim($row["id_tab_proyecto"]),
		    "co_proyecto_acc_espec"     => trim($row["id_tab_proyecto_ae"]),
		    "tx_codigo"     => trim($row["tx_codigo"]),
		    "tx_distribucion"     => trim($row["tx_distribucion"]),
		    "descripcion"     => trim($row["descripcion"]),
		    "presup_01"     => trim($row["presup_01"]),
		    "presup_02"     => trim($row["presup_02"]),
		    "presup_03"     => trim($row["presup_03"]),
		    "trimestre_01"     => trim($trimestre_01),
		    "presup_04"     => trim($row["presup_04"]),
		    "presup_05"     => trim($row["presup_05"]),
		    "presup_06"     => trim($row["presup_06"]),
		    "trimestre_02"     => trim($trimestre_02),
		    "presup_07"     => trim($row["presup_07"]),
		    "presup_08"     => trim($row["presup_08"]),
		    "presup_09"     => trim($row["presup_09"]),
		    "trimestre_03"     => trim($trimestre_03),
		    "presup_10"     => trim($row["presup_10"]),
		    "presup_11"     => trim($row["presup_11"]),
		    "presup_12"     => trim($row["presup_12"]),
		    "trimestre_04"     => trim($trimestre_04),
		    "mo_total"     => trim($mo_total),
		);
	}	
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 33:

	$sql = "SELECT t41.id as co_proyecto_acc_espec_dist, t41.id_tab_proyecto_ae as co_proyecto_acc_espec, t41.*, tx_codigo, descripcion 
	FROM proyecto_seguimiento.tab_proyecto_aedist as t41
	inner join proyecto_seguimiento.tab_proyecto_ae as t39 on t41.id_tab_proyecto_ae=t39.id
	WHERE t41.id_tab_proyecto='".$_POST['id_proyecto']."' AND t39.in_activo is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t41.id, tx_codigo ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec_dist"     => trim($row["co_proyecto_acc_espec_dist"]),
		    "id_proyecto"     => trim($row["id_tab_proyecto"]),
		    "co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
		    "tx_codigo"     => trim($row["tx_codigo"]),
		    "descripcion"     => trim($row["descripcion"]),
		    "monto_401"     => trim($row["monto_401"]),
		    "monto_402"     => trim($row["monto_402"]),
		    "monto_403"     => trim($row["monto_403"]),
		    "monto_404"     => trim($row["monto_404"]),
		    "monto_405"     => trim($row["monto_405"]),
		    "monto_406"     => trim($row["monto_406"]),
		    "monto_407"     => trim($row["monto_407"]),
		    "monto_408"     => trim($row["monto_408"]),
		    "monto_409"     => trim($row["monto_409"]),
		    "monto_410"     => trim($row["monto_410"]),
		    "monto_411"     => trim($row["monto_411"]),
		    "monto_498"     => trim($row["monto_498"]),
		    "total"     => trim($row["total"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 34:

	$sql = "SELECT id_tab_proyecto, tx_pa::integer, tx_nombre, SUM(nu_monto) AS nu_monto
		FROM proyecto_seguimiento.tab_proyecto_aepartida as t42
		inner join proyecto_seguimiento.tab_proyecto_ae as t39 on t42.id_tab_proyecto_ae=t39.id
		inner join mantenimiento.tab_partidas as t44 on t42.tx_pa=t44.co_partida
		WHERE t39.id_tab_proyecto='".$_POST['id_proyecto']."' AND t42.in_activo is true
		GROUP BY 1,2,3";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY tx_pa ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "id_proyecto"     => trim($row["id_tab_proyecto"]),
		    "tx_partida"     => trim($row["tx_pa"].' '.$row["tx_nombre"]),
		    "nu_monto"     => trim($row["nu_monto"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 35:

	$sql = "SELECT t62.id, id_tab_proyecto, de_tipo_fondo, mo_fondo, t62.in_activo, de_tipo_recurso, de_codigo_recurso
		FROM proyecto_seguimiento.tab_proyecto_distribucion as t62
		inner join mantenimiento.tab_tipo_fondo as t61 on t62.id_tab_tipo_fondo=t61.id
		inner join mantenimiento.tab_tipo_recurso as t60 on t61.id_tab_tipo_recurso=t60.id
		WHERE id_tab_proyecto='".$_POST['id_proyecto']."' AND t62.in_activo is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY de_codigo_recurso, id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_distribucion"     => trim($row["id"]),
		    "id_proyecto"     => trim($row["id_tab_proyecto"]),
		    "tx_tipo_fondo"     => trim($row["de_tipo_fondo"]),
		    "tx_codigo_recurso"     => trim($row["de_codigo_recurso"]),
		    "mo_fondo"     => trim($row["mo_fondo"]),
		    "edo_reg"     => trim($row["in_activo"]),
		    "tx_tipo_recurso"     => trim($row["de_codigo_recurso"].' '.$row["de_tipo_recurso"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 36:

	$sql = "SELECT t62.id, id_tab_proyecto, de_tipo_fondo, mo_fondo, t62.in_activo, de_tipo_recurso, de_codigo_recurso
		FROM proyecto_seguimiento.tab_proyecto_distribucion as t62
		inner join mantenimiento.tab_tipo_fondo as t61 on t62.id_tab_tipo_fondo=t61.id
		inner join mantenimiento.tab_tipo_recurso as t60 on t61.id_tab_tipo_recurso=t60.id
		WHERE id_tab_proyecto='".$_POST['id_proyecto']."' AND t62.in_activo is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY de_codigo_recurso ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_distribucion"     => trim($row["id"]),
		    "id_proyecto"     => trim($row["id_tab_proyecto"]),
		    "tx_tipo_fondo"     => trim($row["de_tipo_fondo"]),
		    "tx_codigo_recurso"     => trim($row["de_codigo_recurso"]),
		    "mo_fondo"     => trim($row["mo_fondo"]),
		    "edo_reg"     => trim($row["in_activo"]),
		    "tx_tipo_recurso"     => trim($row["de_tipo_recurso"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 37:

	$sql = "SELECT * FROM mantenimiento.tab_tipo_fondo WHERE in_activo is true;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_tipo_fondo"		=> $row["id"],
			"tx_tipo_fondo"	=> $row["de_tipo_fondo"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 38:

	$info = $_POST["variables"];

	$data = json_decode(stripslashes($info));
	$co_proyecto_distribucion = $data->co_proyecto_distribucion;
	$mo_fondo = $data->mo_fondo;

	$codigo = decode($co_proyecto_distribucion);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="proyecto_seguimiento.tab_proyecto_distribucion";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["mo_fondo"] = decode($mo_fondo);
			$co_proyecto_distribucion = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_proyecto_distribucion){
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
			$tabla="proyecto_seguimiento.tab_proyecto_distribucion";
			$primaryKey="co_proyecto_distribucion";
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = 'TRUE';
			$co_proyecto_distribucion = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_proyecto_distribucion){
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

		break;
	case 99:

	try{
		$paraTransaccion->BeginTrans();

		$tabla="proyecto_seguimiento.tab_proyecto";
		$primaryKey="id";
		$variable["id_tab_ejercicio_fiscal"] = $_POST['id_ejercicio'];
		$variable["id_tab_ejecutores"] = $_POST['id_ejecutor'];
		$variable["id_tab_tipo_registro"] = 2;
		$variable["de_nombre"] = $_POST['nb_proyecto'];
		list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
		$fecha_inicio = $anio."-".$mes."-".$dia;
		$variable["fe_inicio"] = $fecha_inicio;
		list($dia, $mes, $anio) = explode("/",$_POST['fecha_fin']);
		$fecha_fin = $anio."-".$mes."-".$dia;
		$variable["fe_fin"] = $fecha_fin;
		$variable["id_tab_estatus_proyecto"] = $_POST['co_estatus_proyecto'];
		$variable["de_objetivo"] = $_POST['tx_objetivo_general'];
		$variable["id_tab_situacion_presupuestaria"] = $_POST['co_situacion_presupuestaria'];
		$variable["mo_proyecto"] = $_POST['mo_total'];
		$variable["de_proyecto"] = $_POST['tx_descripcion_proyecto'];
		$variable["clase_sector"] = $_POST['co_sector'];
		$variable["clase_subsector"] = $_POST['co_sub_sector'];
		$variable["plan_operativo"] = $_POST['co_plan'];
		$variable["id_tab_estatus"] = 1;
		$variable["created_at"] = date("Y-m-d H:i:s");
		$variable["in_activo"] = 'TRUE';
		$co_proyecto = $comunes->InsertConID($tabla,$variable,$primaryKey);

		if ($co_proyecto){
			$paraTransaccion->CommitTrans();
			$sql4 = "SELECT nu_codigo FROM proyecto_seguimiento.tab_proyecto WHERE id = $co_proyecto;";
			$resultado4 = $comunes->ObtenerFilasBySqlSelect($sql4);
			echo json_encode(array(
				"success" => true,
				"c"  => $co_proyectos,
				"msg" => '<span style="color:green;font-size:13px,">Proceso realizado exitosamente.<br>
					CÓDIGO DEL PROYECTO <br><textarea readonly>'.$resultado4[0]['nu_codigo'].'</textarea></span>'
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

		break;
	case 999:

	$codigo = decode($_POST['co_proyectos']);
	if($codigo!=''||$codigo!=null){

	try{
	$paraTransaccion->BeginTrans();
	$tabla="proyecto_seguimiento.tab_proyecto";
	$tquery="UPDATE";
	$id = 'id = '.$codigo;
	$variable["id_tab_ejercicio_fiscal"] = $_POST['id_ejercicio'];
	$variable["id_tab_ejecutores"] = $_POST['id_ejecutor'];
	$variable["de_nombre"] = $_POST['nb_proyecto'];
	list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
	$fecha_inicio = $anio."-".$mes."-".$dia;
	$variable["fe_inicio"] = $fecha_inicio;
	list($dia, $mes, $anio) = explode("/",$_POST['fecha_fin']);
	$fecha_fin = $anio."-".$mes."-".$dia;
	$variable["fe_fin"] = $fecha_fin;
	$variable["id_tab_estatus_proyecto"] = $_POST['co_estatus_proyecto'];
	$variable["de_objetivo"] = $_POST['tx_objetivo_general'];
	$variable["id_tab_situacion_presupuestaria"] = $_POST['co_situacion_presupuestaria'];
	$variable["mo_proyecto"] = $_POST['mo_total'];
	$variable["de_proyecto"] = $_POST['tx_descripcion_proyecto'];
	$variable["clase_sector"] = $_POST['co_sector'];
	$variable["clase_subsector"] = $_POST['co_sub_sector'];
	$variable["plan_operativo"] = $_POST['co_plan'];
	$variable["updated_at"] = date("Y-m-d H:i:s");
	$id_proyecto = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	$tabla2="proyecto_seguimiento.tab_proyecto_vinculo";
	$co_proyecto_vinculos = decode($_POST['co_proyecto_vinculos']);
	if($co_proyecto_vinculos!=''||$co_proyecto_vinculos!=null){
		$tquery2="UPDATE";
		$id2 = 'id = '.$co_proyecto_vinculos;
		$variable2["id_obj_historico"] = $_POST['co_objetivo_historico'];
		$variable2["id_obj_nacional"] = $_POST['co_objetivo_nacional'];
		$variable2["id_ob_estrategico"] = $_POST['co_objetivo_estrategico'];
		$variable2["id_obj_general"] = $_POST['co_objetivo_general'];
		$variable2["co_area_estrategica"] = ($_POST['co_area_estrategica']=($_POST['co_area_estrategica']!='')?$_POST['co_area_estrategica']:NULL);
		$variable2["co_ambito_estado"] = ($_POST['co_ambito_zulia']=($_POST['co_ambito_zulia']!='')?$_POST['co_ambito_zulia']:NULL);
		$variable2["co_objetivo_estado"] = ($_POST['co_objetivo_zulia']=($_POST['co_objetivo_zulia']!='')?$_POST['co_objetivo_zulia']:NULL);
		$variable2["co_macroproblema"] = ($_POST['co_macroproblema']=($_POST['co_macroproblema']!='')?$_POST['co_macroproblema']:NULL);
		$variable2["co_nodo"] = implode(",",$_POST['co_nodo']);
		$variable2["updated_at"] = date("Y-m-d H:i:s");
	}
	$co_proyecto_vinculos = $comunes->InsertUpdate($tabla2,$variable2,$tquery2,$id2);

	$tabla3="proyecto_seguimiento.tab_proyecto_localizacion";
	$co_proyecto_localizacion = decode($_POST['co_proyecto_localizacion']);
	if($co_proyecto_localizacion!=''||$co_proyecto_localizacion!=null){
		$tquery3="UPDATE";
		$id3 = 'id = '.$co_proyecto_localizacion;
		$variable3["id_tab_ambito_localizacion"] = ($_POST['co_ambito']=($_POST['co_ambito']!='')?$_POST['co_ambito']:NULL);
		$variable3["tx_otra_locacion"] = $_POST['tx_locacion'];
		$variable3["updated_at"] = date("Y-m-d H:i:s");
	}
	$co_proyecto_localizacion = $comunes->InsertUpdate($tabla3,$variable3,$tquery3,$id3);

	$tabla4="proyecto_seguimiento.tab_proyecto_imagen";
	$co_proyecto_imagen = decode($_POST['co_proyecto_imagen']);
	if($co_proyecto_imagen!=''||$co_proyecto_imagen!=null){
		$tquery4="UPDATE";
		$id4 = 'id = '.$co_proyecto_imagen;
		if(array_key_exists("fotoProyecto", $_FILES)){
		        if($_FILES["fotoProyecto"]["tmp_name"]!='')
		        {
		            # Variables del archivo
		            $type = $_FILES["fotoProyecto"]["type"];
		            $tmp_name = $_FILES["fotoProyecto"]["tmp_name"];
		            $size = $_FILES["fotoProyecto"]["size"];
		            $nombre = $_FILES["fotoProyecto"]["name"];
		            $nombre = basename($_FILES["fotoProyecto"]["name"]);
		            # Contenido del archivo
		            $fp = fopen($tmp_name, "rb");
		            $buffer = fread($fp, filesize($tmp_name));
		            fclose($fp);
			    $variable4["im_proyecto"] = pg_escape_bytea($buffer);
			    $variable4["mime_proyecto"] = $type;
			    $variable4["nb_archivo_proyecto"] = $nombre;
		        }
		}
		if(array_key_exists("fotoSatelital", $_FILES)){
		        if($_FILES["fotoSatelital"]["tmp_name"]!='')
		        {
		            # Variables del archivo
		            $type = $_FILES["fotoSatelital"]["type"];
		            $tmp_name = $_FILES["fotoSatelital"]["tmp_name"];
		            $size = $_FILES["fotoSatelital"]["size"];
		            $nombre = $_FILES["fotoSatelital"]["name"];
		            $nombre = basename($_FILES["fotoSatelital"]["name"]);
		            # Contenido del archivo
		            $fp = fopen($tmp_name, "rb");
		            $buffer = fread($fp, filesize($tmp_name));
		            fclose($fp);
			    $variable4["im_satelital"] = pg_escape_bytea($buffer);
			    $variable4["mime_satelital"] = $type;
			    $variable4["nb_archivo_satelital"] = $nombre;
		        }
		}
		$variable4["updated_at"] = date("Y-m-d H:i:s");
	}
	$co_proyecto_imagen = $comunes->InsertUpdate($tabla4,$variable4,$tquery4,$id4);

	$tabla5="proyecto_seguimiento.tab_proyecto_responsable";
	$co_proyecto_responsables = decode($_POST['co_proyecto_responsables']);
	if($co_proyecto_responsables!=''||$co_proyecto_responsables!=null){
		$tquery5="UPDATE";
		$id5 = 'id = '.$co_proyecto_responsables;
		$variable5["responsable_nombres"] = $_POST['nb_rp'];
		$variable5["reponsable_cedula"] = $_POST['nu_crp'];
		$variable5["responsable_correo"] = $_POST['tx_mailrp'];
		$variable5["responsable_telefono"] = $_POST['tx_tfrp'];
		$variable5["tecnico_nombres"] = $_POST['nb_rt'];
		$variable5["tecnico_cedula"] = $_POST['nu_crt'];
		$variable5["tecnico_correo"] = $_POST['tx_mailrt'];
		$variable5["tecnico_telefono"] = $_POST['tx_tfrt'];
		$variable5["tecnico_unidad"] = $_POST['tx_utrt'];
		$variable5["registrador_nombres"] = $_POST['nb_rr'];
		$variable5["registrador_cedula"] = $_POST['nu_crr'];
		$variable5["registrador_correo"] = $_POST['tx_mailrr'];
		$variable5["registrador_telefono"] = $_POST['tx_tfrr'];
		$variable5["administrador_nombres"] = $_POST['nb_ra'];
		$variable5["administrador_cedula"] = $_POST['nu_cra'];
		$variable5["administrador_correo"] = $_POST['tx_mailra'];
		$variable5["administrador_telefono"] = $_POST['tx_tfra'];
		$variable5["administrador_unidad"] = $_POST['tx_uara'];
		$variable5["updated_at"] = date("Y-m-d H:i:s");
	}
	$co_proyecto_responsables = $comunes->InsertUpdate($tabla5,$variable5,$tquery5,$id5);

	$tabla6="proyecto_seguimiento.tab_proyecto_alcance";
	$co_proyecto_alcance = decode($_POST['co_proyecto_alcance']);
	if($co_proyecto_alcance!=''||$co_proyecto_alcance!=null){
		$tquery6="UPDATE";
		$id6 = 'id = '.$co_proyecto_alcance;
		$variable6["enunciado_inicial"] = $_POST['tx_epn'];
		$variable6["poblacion_afectada"] = $_POST['tx_pa'];
		$variable6["indicador_inicial"] = $_POST['tx_isi'];
		$variable6["formula_indicador"] = $_POST['tx_fi'];
		$variable6["fuente_indicador"] = $_POST['tx_fuentei'];
		list($dia, $mes, $anio) = explode("/",$_POST['fecha_inisi']);
		$fecha_inisi = $anio."-".$mes."-".$dia;
		$variable6["fecha_sit_inicial"] = $fecha_inisi;
		$variable6["enunciado_deseado"] = $_POST['tx_esd'];
		$variable6["poblacion_objetivo"] = $_POST['tx_po'];
		$variable6["indicador_deseado"] = $_POST['tx_isd'];
		$variable6["resultado_esperado"] = $_POST['tx_rebs'];
		$variable6["id_tab_unidad_medida"] = ($_POST['co_unidades_medida']=($_POST['co_unidades_medida']!='')?$_POST['co_unidades_medida']:NULL);
		$variable6["meta"] = $_POST['mo_meta_proy'];
		$variable6["benef_femeninos"] = $_POST['nu_benf'];
		$variable6["benef_masculinos"] = $_POST['nu_benm'];
		$variable6["denominacion_benef"] = $_POST['tx_demb'];
		$variable6["emp_dir_feme"] = $_POST['nu_tedf'];
		$variable6["emp_dir_mascu"] = $_POST['nu_tedm'];
		$variable6["emp_new_feme"] = $_POST['nu_tednf'];
		$variable6["emp_new_mascu"] = $_POST['nu_tednm'];
		$variable6["emp_sos_feme"] = $_POST['nu_tedsf'];
		$variable6["emp_sos_mascu"] = $_POST['nu_tedsm'];
		$variable6["proy_vincu_otro"] = $_POST['in_pvo'];
		$variable6["id_si_es_si"] = $_POST['tx_vinculo_proyecto'];
		$variable6["inst_responsable"] = $_POST['tx_nipdv'];
		$variable6["instancia_responsable"] = $_POST['tx_nirpv'];
		$variable6["nombre_proy"] = $_POST['tx_nipcv'];
		$variable6["medida_vinculo"] = $_POST['tx_eqmvp'];
		$variable6["tx_re_esperado"] = $_POST['tx_re_esperado'];
		$variable6["updated_at"] = date("Y-m-d H:i:s");
	}
	$co_proyecto_alcance = $comunes->InsertUpdate($tabla6,$variable6,$tquery6,$id6);

	$tabla7="proyecto_seguimiento.tab_proyecto_financiamiento";
	$co_proyecto_financiamiento = decode($_POST['co_proyecto_financiamiento']);
	if($co_proyecto_financiamiento!=''||$co_proyecto_financiamiento!=null){
		$tquery7="UPDATE";
		$id7 = 'id = '.$co_proyecto_financiamiento;
		$variable7["in_financiamiento"] = $_POST['in_financiamiento'];
		$variable7["in_tipo_financiamiento"] = $_POST['in_tipo_financiamiento'];
		$variable7["mo_parcial"] = $_POST['mo_parcial'];
		$variable7["id_tab_tipo_fondo"] = ($_POST['co_tipo_fondo']=($_POST['co_tipo_fondo']!='')?$_POST['co_tipo_fondo']:NULL);
		$variable7["mo_financiar"] = $_POST['mo_financiar'];
		$variable7["tx_justificacion"] = $_POST['tx_justificacion'];
		$variable7["created_at"] = date("Y-m-d H:i:s");
	}
	$co_proyecto_financiamiento = $comunes->InsertUpdate($tabla7,$variable7,$tquery7,$id7);

	if ($id_proyecto){
		$paraTransaccion->CommitTrans();
		echo json_encode(array(
			"success" => true,
			"c"  => $codigo,
			"msg" => 'Registro Actualizado exitosamente.'
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

		break;
	default:
	}

