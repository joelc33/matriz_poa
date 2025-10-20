<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){

	$clave = $_POST['clave'];
	$confirmacion = $_POST['confirmacion'];
	$respuesta = 1;

        if($clave === $confirmacion){
		$tabla="autenticacion.tab_usuarios";
		$tquery="UPDATE";
		$id = 'id = '.$_SESSION['co_usuario'];
		$variable["da_password"] = md5($confirmacion); 
		$variable["remember_token"] = $confirmacion; 
		$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);
        }else{
            	$respuesta = "La contraseña y la confirmacion no coinciden";
        }

	/*$sql_correo = "SELECT co_usuario, tx_login, tx_clave_definina, tx_doc_rif, nb_razon_social, tx_direccion_fiscal, tx_correo FROM t01_usuario as t01
	inner join t15_contribuyente as t15 on t01.co_contribuyente=t15.co_contribuyente
	WHERE t01.co_usuario = ".$_SESSION['co_usuario'];
	$resultado_correo = $comunes->ObtenerFilasBySqlSelect($sql_correo);

	$motivo='CAMBIO DE CONTRASENA DE USUARIO';
	$cuerpo='Estimado(a) ciudadano(a), usted acaba de cambiar los datos de usuario en el Sistema del Servicio Desconcentrado De Administración Tributaria Del Estado Zulia. Sus datos de acceso son:
<ul><li><strong>Usuario:</strong> '.$resultado_correo[0]['tx_login'].'</li>
<li><strong>Nueva Clave:</strong> '.$resultado_correo[0]['tx_clave_definina'].'</li></ul>
A partir de este momento, con la utilización de su usuario y contraseña usted tiene la posibilidad de acceder a nuestros Servicios en Línea.

<br><br>La ruta de acceso al sistema es <a href="http://etrib.sedatez.zulia.gob.ve" target="_blank">http://etrib.sedatez.zulia.gob.ve</a>

<br><br>Gracias por utilizar nuestros servicios.

<br><br><i><font color="#9C9C9C">Esta es una cuenta de correo no monitoreada. Por favor, no responda ni reenvíe mensajes a esta cuenta.</font></i>';*/

	if($respuesta==1){
		//enviar_correo($resultado_correo[0]['tx_correo'],$resultado_correo[0]['nb_razon_social'],$motivo,$cuerpo);
		$info = array(
			'success' => true,
			'message' => 'La Contraseña se Cambio Satisfactoriamente'
		);
	}else{
		//header('HTTP/1.1 501 Error saving the record');
		$info = array(
			'failure' => false,
			'message' => $respuesta
		);
	}
	echo json_encode($info);
}elseif($_GET['op']==2){

	/*$sql = "SELECT t01.*,t29.co_documento, nu_cedula, nb_funcionario, ap_funcionario, 
        t29.co_ejecutores, co_cargo, tx_direccion, tx_telefono, tx_email, inicial, tx_ejecutor, tx_rol FROM t01_usuario as t01 
	inner join t02_rol as t02 on t01.co_rol=t02.co_rol
	left join t29_funcionario as t29 on t01.co_funcionario=t29.co_funcionario
	left join t24_ejecutores as t24 on t29.co_ejecutores=t24.co_ejecutores
	left join t11_documento as t11 on t29.co_documento=t11.co_documento";*/

	$sql = "SELECT t01.id, da_login, de_rol, nu_cedula, nb_funcionario, 
	ap_funcionario, t24.id_ejecutor , tx_email, inicial, tx_ejecutor , t01.created_at ,t01.in_estatus
	FROM autenticacion.tab_usuarios as t01 
	inner join autenticacion.tab_usuario_rol as t02 on t01.id = t02.id_tab_usuarios
	inner join autenticacion.tab_rol as t03 on t02.id_tab_rol = t03.id
	left join mantenimiento.tab_funcionario as t29 on t01.id=t29.id_tab_usuarios
	left join mantenimiento.tab_ejecutores as t24 on t29.id_tab_ejecutores=t24.id
	left join mantenimiento.tab_documento as t11 on t29.id_tab_documento=t11.id";

	if($_POST['BuscarBy']=="true"){
		$sql.=" WHERE t01.id = t01.id ";
		if($_POST['variable']!=""){$sql.=" and nb_funcionario ILIKE '%".$_POST['variable']."%'";}
		if($_POST['variable']!=""){$sql.=" or ap_funcionario ILIKE '%".$_POST['variable']."%'";}
		if($_POST['variable']!=""){$sql.=" or tx_ejecutor ILIKE '%".$_POST['variable']."%'";}
		if($_POST['variable']!=""){$sql.=" or de_rol ILIKE '%".$_POST['variable']."%'";}
		if($_POST['variable']!=""){$sql.=" or da_login ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t01.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_usuario"     => trim($row["id"]),
		    "nb_funcionario"     => trim(utf8_encode($row["nb_funcionario"])),
		    "ap_funcionario"     => trim(utf8_encode($row["ap_funcionario"])),
		    "nu_cedula"     => trim(utf8_encode($row["inicial"].'-'.$row["nu_cedula"])),
		    "tx_login"     => trim(utf8_encode($row["da_login"])),
		    "co_rol"     => trim(utf8_encode($row["de_rol"])),
		    "edo_reg"     => trim($row["in_estatus"]),
		    "tx_ejecutor"     => trim($row["tx_ejecutor"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==3){
	$sql = "SELECT * FROM mantenimiento.tab_documento where tipo='N';";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_documento"		=> $row["id"],
			"inicial"	=> utf8_encode($row["inicial"]), 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET["op"]==4){
	$sql = "SELECT * FROM autenticacion.tab_rol where in_estatus is true;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_rol"		=> $row["id"],
			"tx_rol"	=> utf8_encode($row["de_rol"]), 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET["op"]==5){
	$sql = "SELECT * FROM mantenimiento.tab_ejecutores where mantenimiento.sp_in_ejecutor( id, ".$_SESSION['ejercicio_fiscal'].") is true order by id_ejecutor ASC;";       
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
}elseif($_GET["op"]==6){
	$sql = "SELECT * FROM mantenimiento.tab_cargo where in_activo is true;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_cargo"		=> $row["id"],
			"tx_cargo"	=> $row["de_cargo"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==7){
	$codigo = decode($_POST['co_funcionario']);
	$co_usuario = decode($_POST['co_usuario']);
	if($codigo!=''||$codigo!=null){
		try{
			if($_POST["co_cargo"]!=''){
				if(!is_numeric($_POST["co_cargo"])){
					$paraTransaccion->BeginTrans();
					$tabla="mantenimiento.tab_cargo";
					$primaryKey="id";
					$variable["de_cargo"] = decode($_POST['co_cargo']);
					$variable["in_activo"] = 'TRUE';
					$variable["created_at"] = date("Y-m-d H:i:s");
					$cargo = $comunes->InsertConID($tabla,$variable,$primaryKey);
				}else{
					$cargo = $_POST["co_cargo"];
				}
			}
			$paraTransaccion->BeginTrans();
			$tabla="mantenimiento.tab_funcionario";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["id_tab_documento"] = decode($_POST['co_documento']);
			$variable["nu_cedula"] = decode($_POST['nu_cedula']);
			$variable["nb_funcionario"] = decode($_POST['nb_funcionario']);
			$variable["ap_funcionario"] = decode($_POST['ap_funcionario']);
			$variable["id_tab_ejecutores"] = decode($_POST['co_ejecutores']);
			$variable["id_tab_cargo"] = decode($cargo);
			$variable["tx_direccion"] = decode($_POST['tx_direccion']);
			$variable["tx_telefono"] = decode($_POST['tx_telefono']);
			$variable["tx_email"] = decode($_POST['tx_email']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$funcionario = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			$tabla="autenticacion.tab_usuario_rol";
			$tquery="UPDATE";
			$id = 'id_tab_usuarios = '.$co_usuario;
			$variable["id_tab_rol"] = decode($_POST['co_rol']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$rol = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			$tabla="autenticacion.tab_usuarios";
			$tquery="UPDATE";
			$id = 'id = '.$co_usuario;
			$variable["da_login"] = decode($_POST['tx_login']);
			$variable["da_password"] = md5($_POST['tx_password']);
			$variable["remember_token"] = decode($_POST['tx_password']);
			$variable["da_email"] = decode($_POST['tx_email']);
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

			$tabla="autenticacion.tab_usuarios";
			$primaryKey="id";
			$variable["da_login"] = decode($_POST['tx_login']);
			$variable["da_password"] = md5($_POST['tx_password']);
			$variable["remember_token"] = decode($_POST['tx_password']);
			$variable["da_email"] = decode($_POST['tx_email']);
			$variable["in_estatus"] = 'TRUE';
			$variable["created_at"] = date("Y-m-d H:i:s");
			$id_usuario = $comunes->InsertConID($tabla,$variable,$primaryKey);

			$tabla="autenticacion.tab_usuario_rol";
			$primaryKey="id";
			$variable["id_tab_usuarios"] = $id_usuario;
			$variable["id_tab_rol"] = decode($_POST['co_rol']);
			$variable["in_estatus"] = 'TRUE';
			$variable["created_at"] = date("Y-m-d H:i:s");
			$id_usuario_rol = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if($_POST["co_cargo"]!=''){
				if(!is_numeric($_POST["co_cargo"])){
					$paraTransaccion->BeginTrans();
					$tabla="mantenimiento.tab_cargo";
					$primaryKey="id";
					$variable["de_cargo"] = decode($_POST['co_cargo']);
					$variable["in_activo"] = 'TRUE';
					$variable["created_at"] = date("Y-m-d H:i:s");
					$cargo = $comunes->InsertConID($tabla,$variable,$primaryKey);
				}else{
					$cargo = $_POST["co_cargo"];
				}
			}
			$paraTransaccion->BeginTrans();
			$tabla="mantenimiento.tab_funcionario";
			$primaryKey="id";
			$variable["id_tab_documento"] = decode($_POST['co_documento']);
			$variable["id_tab_usuarios"] = $id_usuario;
			$variable["nu_cedula"] = decode($_POST['nu_cedula']);
			$variable["nb_funcionario"] = decode($_POST['nb_funcionario']);
			$variable["ap_funcionario"] = decode($_POST['ap_funcionario']);
			$variable["id_tab_ejecutores"] = decode($_POST['co_ejecutores']);
			$variable["id_tab_cargo"] = decode($cargo);
			$variable["tx_direccion"] = decode($_POST['tx_direccion']);
			$variable["tx_telefono"] = decode($_POST['tx_telefono']);
			$variable["tx_email"] = decode($_POST['tx_email']);
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = 'TRUE';
			$co_funcionario = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_funcionario){
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
}elseif($_GET['op']==8){
	$codigo = decode($_POST['co_funcionario']);
	$co_usuario = decode($_POST['co_usuario']);
	if($codigo!=''||$codigo!=null){
		try{
			if($_POST["co_cargo"]!=''){
				if(!is_numeric($_POST["co_cargo"])){
					$paraTransaccion->BeginTrans();
					$tabla="mantenimiento.tab_cargo";
					$primaryKey="id";
					$variable["de_cargo"] = decode($_POST['co_cargo']);
					$variable["in_activo"] = 'TRUE';
					$variable["created_at"] = date("Y-m-d H:i:s");
					$cargo = $comunes->InsertConID($tabla,$variable,$primaryKey);
				}else{
					$cargo = $_POST["co_cargo"];
				}
			}
			$paraTransaccion->BeginTrans();
			$tabla="mantenimiento.tab_funcionario";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["id_tab_documento"] = decode($_POST['co_documento']);
			$variable["nu_cedula"] = decode($_POST['nu_cedula']);
			$variable["nb_funcionario"] = decode($_POST['nb_funcionario']);
			$variable["ap_funcionario"] = decode($_POST['ap_funcionario']);
			$variable["id_tab_cargo"] = decode($cargo);
			$variable["tx_direccion"] = decode($_POST['tx_direccion']);
			$variable["tx_telefono"] = decode($_POST['tx_telefono']);
			$variable["tx_email"] = decode($_POST['tx_email']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$funcionario = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			$tabla="autenticacion.tab_usuarios";
			$tquery="UPDATE";
			$id = 'id = '.$co_usuario;
			$variable["da_login"] = decode($_POST['tx_login']);
			//$variable["tx_password"] = md5($_POST['tx_password']);
			//$variable["tx_clave_definida"] = decode($_POST['tx_password']);
			$variable["da_email"] = decode($_POST['tx_email']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			$tabla4="mantenimiento.tab_ejecutores";
			$tquery="UPDATE";
			$id4 = 'id = '.$_POST['co_ejecutores'];
			$variable4["updated_at"] = date("Y-m-d H:i:s");
			$variable4["de_correo"] = decode($_POST['de_correo']);
			$variable4["de_telefono"] = decode($_POST['de_telefono']);
			$co_ejecutores = $comunes->InsertUpdate($tabla4,$variable4,$tquery,$id4);

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
			echo json_encode(array(
				    "success" => false,
				    "msg" => "Error en Transaccion.\n"
			));
	}
}elseif($_GET['op']==9){
	$tabla="autenticacion.tab_usuarios";
	$tquery="UPDATE";
	$id = "id = '".$_POST['co_usuario']."'";
	$variable["da_password"] = md5(123456);
	$variable["remember_token"] = 123456;
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Clave reiniciada con Exito!'
	));
}elseif($_GET['op']==10){
	$tabla="autenticacion.tab_usuarios";
	$tquery="UPDATE";
	$id = "id = '".$_POST['co_usuario']."'";
	$variable["in_estatus"] = "false"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Deshabilitado con Exito!'
	));
}
?>
