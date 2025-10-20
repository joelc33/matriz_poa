<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
	$sql = "SELECT * FROM t16_estatus_proyecto;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_estatus_proyecto"		=> $row["co_estatus_proyecto"],
			"tx_estatus_proyecto"	=> utf8_encode($row["tx_estatus_proyecto"]), 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==2){
	$sql = "SELECT * FROM t17_situacion_presupuestaria;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_situacion_presupuestaria"		=> $row["co_situacion_presupuestaria"],
			"tx_situacion_presupuestaria"	=> utf8_encode($row["tx_situacion_presupuestaria"]), 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==3){
	$sql = "SELECT * FROM t18_sectores where nu_nivel=1 and edo_reg is true order by co_sector asc;";
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
}elseif($_GET['op']==4){
	$sql = "SELECT * FROM mantenimiento.tab_sectores where co_sector='".$_POST['co_sector']."' and nu_nivel=2 and in_activo = true order by co_sub_sector asc;";
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
}elseif($_GET['op']==5){
	$sql = "SELECT * FROM t19_plan_operativo;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_plan"		=> $row["co_plan_operativo"],
			"tx_plan"	=> $row["tx_plan_operativo"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==6){
	$sql = "SELECT * FROM t20_planes where nu_nivel=1 and edo_reg is true order by co_objetivo_historico asc;";
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
}elseif($_GET['op']==7){
	$sql = "SELECT * FROM t20_planes where co_objetivo_historico='".$_POST['co_objetivo_historico']."' and nu_nivel=2 and edo_reg is true order by co_objetivo_nacional asc;";
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
}elseif($_GET['op']==8){
	$sql = "SELECT * FROM t20_planes where co_objetivo_nacional='".$_POST['co_objetivo_nacional']."' and co_objetivo_historico='".$_POST['co_objetivo_historico']."' and nu_nivel=3 and edo_reg is true order by co_objetivo_estrategico asc;";
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
}elseif($_GET['op']==9){
	$sql = "SELECT * FROM t20_planes where co_objetivo_historico='".$_POST['co_objetivo_historico']."' and co_objetivo_nacional='".$_POST['co_objetivo_nacional']."' and co_objetivo_estrategico='".$_POST['co_objetivo_estrategico']."' and nu_nivel=4 and edo_reg is true order by co_objetivo_estrategico asc;";
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
}elseif($_GET['op']==10){
	$sql = "SELECT * FROM t10_ambito_localizacion order by co_ambito_localizacion asc;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_ambito"		=> $row["co_ambito_localizacion"],
			"tx_ambito"	=> $row["tx_ambito_localizacion"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==11){
	$sql = "SELECT * FROM t12_estado;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_estado"		=> $row["co_estado"],
			"tx_estado"	=> $row["tx_estado"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==12){
	$sql = "SELECT * FROM t13_municipio where co_estado=".$_POST['co_estado'].";";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_municipio"		=> $row["co_municipio"],
			"tx_municipio"	=> $row["tx_municipio"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==13){
	$sql = "SELECT * FROM t14_parroquia where co_municipio=".$_POST['co_municipio'].";";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_parroquia"		=> $row["co_parroquia"],
			"tx_parroquia"	=> $row["tx_parroquia"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==14){
	$sql = "SELECT * FROM t15_pais;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_pais"		=> $row["co_pais"],
			"tx_pais"	=> $row["tx_pais"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==15){
	$sql = "SELECT * FROM t27_vinculo_proyecto;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_vinculo_proyecto"		=> $row["co_vinculo_proyecto"],
			"tx_vinculo_proyecto"	=> $row["tx_vinculo_proyecto"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==16){
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
}elseif($_GET['op']==17){
	$sql = "SELECT co_proyecto_localizacion_nacional, tx_pais, tx_estado, tx_municipio, tx_parroquia FROM t34_proyecto_localizacion_nacional as t34
	inner join t15_pais as t15 on t34.co_pais=t15.co_pais
	inner join t12_estado as t12 on t34.co_estado=t12.co_estado
	inner join t13_municipio as t13 on t34.co_municipio=t13.co_municipio
	inner join t14_parroquia as t14 on t34.co_parroquia=t14.co_parroquia
	WHERE id_proyecto='".$_POST['id_proyecto']."' AND edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 10: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_proyecto_localizacion_nacional ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_localizacion_nacional"     => trim($row["co_proyecto_localizacion_nacional"]),
		    "tx_pais"     => trim($row["tx_pais"]),
		    "tx_estado"     => trim($row["tx_estado"]),
		    "tx_municipio"     => trim($row["tx_municipio"]),
		    "tx_parroquia"     => trim($row["tx_parroquia"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==18){
	$tabla="t34_proyecto_localizacion_nacional";
	$tquery="UPDATE";
	$id = 'co_proyecto_localizacion_nacional = '.$_POST['co_proyecto_localizacion_nacional'];
	$variable["edo_reg"] = "false"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Eliminado con Exito!'
	));
}elseif($_GET['op']==19){
	$sql = "SELECT co_proyecto_localizacion_comunal, tx_codigo_comuna, tx_agregacion_comunal, tx_estado, tx_municipio, tx_parroquia FROM t35_proyecto_localizacion_comunal as t35
	inner join t12_estado as t12 on t35.co_estado=t12.co_estado
	inner join t13_municipio as t13 on t35.co_municipio=t13.co_municipio
	inner join t14_parroquia as t14 on t35.co_parroquia=t14.co_parroquia
	WHERE id_proyecto='".$_POST['id_proyecto']."' AND edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 10: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_proyecto_localizacion_comunal ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_localizacion_comunal"     => trim($row["co_proyecto_localizacion_comunal"]),
		    "tx_codigo_comuna"     => trim($row["tx_codigo_comuna"]),
		    "tx_agregacion_comunal"     => trim($row["tx_agregacion_comunal"]),
		    "tx_estado"     => trim($row["tx_estado"]),
		    "tx_municipio"     => trim($row["tx_municipio"]),
		    "tx_parroquia"     => trim($row["tx_parroquia"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==20){
	$tabla="t35_proyecto_localizacion_comunal";
	$tquery="UPDATE";
	$id = 'co_proyecto_localizacion_comunal = '.$_POST['co_proyecto_localizacion_comunal'];
	$variable["edo_reg"] = "false"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Eliminado con Exito!'
	));
}elseif($_GET['op']==80){
	$codigo = decode($_POST['co_proyecto_localizacion_nacional']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t34_proyecto_localizacion_nacional";
			$tquery="UPDATE";
			$id = 'co_proyecto_localizacion_nacional = '.$codigo;
			$variable["co_pais"] = decode($_POST['co_pais']);
			$variable["co_estado"] = decode($_POST['co_estado']);
			$variable["co_municipio"] = decode($_POST['co_municipio']);
			$variable["co_parroquia"] = decode($_POST['co_parroquia']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
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
			$tabla="t34_proyecto_localizacion_nacional";
			$tquery="INSERT";
			$variable["id_proyecto"] = decode($_POST['id_proyecto']);
			$variable["co_pais"] = decode($_POST['co_pais']);
			$variable["co_estado"] = decode($_POST['co_estado']);
			$variable["co_municipio"] = decode($_POST['co_municipio']);
			$variable["co_parroquia"] = decode($_POST['co_parroquia']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = "TRUE";
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
}elseif($_GET['op']==21){
	$sql = "SELECT * FROM t39_proyecto_acc_espec WHERE id_proyecto='".$_POST['id_proyecto']."' AND edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 10: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_proyecto_acc_espec ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
		    "descripcion"     => trim($row["descripcion"]),
		    "co_unidades_medida"     => trim($row["co_unidades_medida"]),
		    "meta"     => trim($row["meta"]),
		    "ponderacion"     => trim($row["ponderacion"]),
		    "bien_servicio"     => trim($row["bien_servicio"]),
		    "total"     => trim($row["total"]),
		    "fec_inicio"     => trim($row["fec_inicio"]),
		    "fec_termino"     => trim($row["fec_termino"]),
		    "id_ejecutor"     => trim($row["id_ejecutor"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==22){
	$sql = "SELECT * FROM t45_planes_zulia where co_area_estrategica='".$_POST['co_area_estrategica']."' and nu_nivel=1 and edo_reg is true order by co_ambito_zulia asc;";
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
}elseif($_GET['op']==23){
	$sql = "SELECT * FROM t45_planes_zulia where co_ambito_zulia='".$_POST['co_ambito_zulia']."' and nu_nivel=2 and edo_reg is true order by co_objetivo_zulia asc;";
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
}elseif($_GET['op']==24){
	$sql = "SELECT * FROM t45_planes_zulia where co_ambito_zulia='".$_POST['co_ambito_zulia']."' and nu_nivel=3 and edo_reg is true order by co_macroproblema asc;";
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
}elseif($_GET['op']==25){
	$sql = "SELECT * FROM t45_planes_zulia where co_ambito_zulia='".$_POST['co_ambito_zulia']."' and nu_nivel=4 and edo_reg is true order by co_nodo asc;";
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
}elseif($_GET['op']==26){
	$sql = "SELECT * FROM t45_planes_zulia where nu_nivel=0 and edo_reg is true order by co_area_estrategica asc;";
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
}elseif($_GET['op']==81){
	$codigo = decode($_POST['co_proyecto_localizacion_comunal']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t35_proyecto_localizacion_comunal";
			$tquery="UPDATE";
			$id = 'co_proyecto_localizacion_comunal = '.$codigo;
			$variable["tx_codigo_comuna"] = decode($_POST['tx_codigo_comuna']);
			$variable["tx_agregacion_comunal"] = decode($_POST['tx_agregacion_comunal']);
			$variable["co_estado"] = decode($_POST['co_estado']);
			$variable["co_municipio"] = decode($_POST['co_municipio']);
			$variable["co_parroquia"] = decode($_POST['co_parroquia']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
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
			$tabla="t35_proyecto_localizacion_comunal";
			$tquery="INSERT";
			$variable["id_proyecto"] = decode($_POST['id_proyecto']);
			$variable["tx_codigo_comuna"] = decode($_POST['tx_codigo_comuna']);
			$variable["tx_agregacion_comunal"] = decode($_POST['tx_agregacion_comunal']);
			$variable["co_estado"] = decode($_POST['co_estado']);
			$variable["co_municipio"] = decode($_POST['co_municipio']);
			$variable["co_parroquia"] = decode($_POST['co_parroquia']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = "TRUE";
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
}elseif($_GET['op']==99){

try{
	$paraTransaccion->BeginTrans();

	$tabla="t26_proyectos";
	$primaryKey="co_proyectos";
	$variable["id_ejercicio"] = $_POST['id_ejercicio'];
	$variable["id_ejecutor"] = $_POST['id_ejecutor'];
	$variable["tipo_registro"] = 1;
	$variable["nombre"] = $_POST['nb_proyecto'];
	list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
	$fecha_inicio = $anio."-".$mes."-".$dia;
	$variable["fecha_inicio"] = $fecha_inicio;
	list($dia, $mes, $anio) = explode("/",$_POST['fecha_fin']);
	$fecha_fin = $anio."-".$mes."-".$dia;
	$variable["fecha_fin"] = $fecha_fin;
	$variable["status_registro"] = $_POST['co_estatus_proyecto'];
	$variable["objetivo"] = $_POST['tx_objetivo_general'];
	$variable["sit_presupuesto"] = $_POST['co_situacion_presupuestaria'];
	$variable["monto"] = $_POST['mo_total'];
	$variable["descripcion"] = $_POST['tx_descripcion_proyecto'];
	$variable["clase_sector"] = $_POST['co_sector'];
	$variable["clase_subsector"] = $_POST['co_sub_sector'];
	$variable["plan_operativo"] = $_POST['co_plan'];
	$variable["id_tab_ejecutor"] = $_POST['id_tab_ejecutor'];
	$variable["co_estatus"] = 1;
	$variable["fecha_creacion"] = date("Y-m-d H:i:s");
	$variable["edo_reg"] = 'TRUE';
	$co_proyectos = $comunes->InsertConID($tabla,$variable,$primaryKey);

	if ($co_proyectos){
		$paraTransaccion->CommitTrans();
		$sql4 = "SELECT id_proyecto FROM t26_proyectos WHERE co_proyectos=$co_proyectos;";
		$resultado4 = $comunes->ObtenerFilasBySqlSelect($sql4);
		echo json_encode(array(
			"success" => true,
			"c"  => $co_proyectos,
			"msg" => '<span style="color:green;font-size:13px,">Proceso realizado exitosamente.<br>
				CÓDIGO DEL PROYECTO <br><textarea readonly>'.$resultado4[0]['id_proyecto'].'</textarea></span>'
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
}elseif($_GET['op']==999){
	$codigo = decode($_POST['co_proyectos']);
	if($codigo!=''||$codigo!=null){

	try{
	$paraTransaccion->BeginTrans();
	$tabla="t26_proyectos";
	$tquery="UPDATE";
	$id = 'co_proyectos = '.$codigo;
	$variable["id_ejercicio"] = $_POST['id_ejercicio'];
	$variable["id_ejecutor"] = $_POST['id_ejecutor'];
	$variable["nombre"] = $_POST['nb_proyecto'];
	list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
	$fecha_inicio = $anio."-".$mes."-".$dia;
	$variable["fecha_inicio"] = $fecha_inicio;
	list($dia, $mes, $anio) = explode("/",$_POST['fecha_fin']);
	$fecha_fin = $anio."-".$mes."-".$dia;
	$variable["fecha_fin"] = $fecha_fin;
	$variable["status_registro"] = $_POST['co_estatus_proyecto'];
	$variable["objetivo"] = $_POST['tx_objetivo_general'];
	$variable["sit_presupuesto"] = $_POST['co_situacion_presupuestaria'];
	$variable["monto"] = $_POST['mo_total'];
	$variable["descripcion"] = $_POST['tx_descripcion_proyecto'];
	$variable["clase_sector"] = $_POST['co_sector'];
	$variable["clase_subsector"] = $_POST['co_sub_sector'];
	$variable["plan_operativo"] = $_POST['co_plan'];
	$variable["id_tab_ejecutor"] = $_POST['id_tab_ejecutor'];
	$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
	$id_proyecto = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	$tabla2="t32_proyecto_vinculos";
	$co_proyecto_vinculos = decode($_POST['co_proyecto_vinculos']);
	if($co_proyecto_vinculos!=''||$co_proyecto_vinculos!=null){
		$tquery2="UPDATE";
		$id2 = 'co_proyecto_vinculos = '.$co_proyecto_vinculos;
		$variable2["id_obj_historico"] = $_POST['co_objetivo_historico'];
		$variable2["id_obj_nacional"] = $_POST['co_objetivo_nacional'];
		$variable2["id_ob_estrategico"] = $_POST['co_objetivo_estrategico'];
		$variable2["id_obj_general"] = $_POST['co_objetivo_general'];
		$variable2["co_area_estrategica"] = ($_POST['co_area_estrategica']=($_POST['co_area_estrategica']!='')?$_POST['co_area_estrategica']:NULL);
		$variable2["co_ambito_estado"] = ($_POST['co_ambito_zulia']=($_POST['co_ambito_zulia']!='')?$_POST['co_ambito_zulia']:NULL);
		$variable2["co_objetivo_estado"] = ($_POST['co_objetivo_zulia']=($_POST['co_objetivo_zulia']!='')?$_POST['co_objetivo_zulia']:NULL);
		$variable2["co_macroproblema"] = ($_POST['co_macroproblema']=($_POST['co_macroproblema']!='')?$_POST['co_macroproblema']:NULL);
		$variable2["co_nodo"] = implode(",",$_POST['co_nodo']);
		$variable2["fecha_actualizacion"] = date("Y-m-d H:i:s");
	}else{
		$tquery2="INSERT";
		$id2 = 'co_proyecto_vinculos = '.$co_proyecto_vinculos;
		$variable2["id_proyecto"] = $_POST['id_proyecto'];
		$variable2["id_obj_historico"] = $_POST['co_objetivo_historico'];
		$variable2["id_obj_nacional"] = $_POST['co_objetivo_nacional'];
		$variable2["id_ob_estrategico"] = $_POST['co_objetivo_estrategico'];
		$variable2["id_obj_general"] = $_POST['co_objetivo_general'];
		$variable2["co_area_estrategica"] = ($_POST['co_area_estrategica']=($_POST['co_area_estrategica']!='')?$_POST['co_area_estrategica']:NULL);
		$variable2["co_ambito_estado"] = ($_POST['co_ambito_zulia']=($_POST['co_ambito_zulia']!='')?$_POST['co_ambito_zulia']:NULL);
		$variable2["co_objetivo_estado"] = ($_POST['co_objetivo_zulia']=($_POST['co_objetivo_zulia']!='')?$_POST['co_objetivo_zulia']:NULL);
		$variable2["co_macroproblema"] = ($_POST['co_macroproblema']=($_POST['co_macroproblema']!='')?$_POST['co_macroproblema']:NULL);
		$variable2["co_nodo"] = implode(",", $_POST['co_nodo']);
		$variable2["fecha_creacion"] = date("Y-m-d H:i:s");
		$variable2["edo_reg"] = 'TRUE';
	}
	$co_proyecto_vinculos = $comunes->InsertUpdate($tabla2,$variable2,$tquery2,$id2);

	$tabla3="t33_proyecto_localizacion";
	$co_proyecto_localizacion = decode($_POST['co_proyecto_localizacion']);
	if($co_proyecto_localizacion!=''||$co_proyecto_localizacion!=null){
		$tquery3="UPDATE";
		$id3 = 'co_proyecto_localizacion = '.$co_proyecto_localizacion;
		$variable3["co_ambito_localizacion"] = ($_POST['co_ambito']=($_POST['co_ambito']!='')?$_POST['co_ambito']:NULL);
		$variable3["tx_otra_locacion"] = $_POST['tx_locacion'];
		$variable3["fecha_actualizacion"] = date("Y-m-d H:i:s");
	}else{
		$tquery3="INSERT";
		$id3 = 'co_proyecto_localizacion = '.$co_proyecto_localizacion;
		$variable3["id_proyecto"] = $_POST['id_proyecto'];
		$variable3["co_ambito_localizacion"] = ($_POST['co_ambito']=($_POST['co_ambito']!='')?$_POST['co_ambito']:NULL);
		$variable3["tx_otra_locacion"] = $_POST['tx_locacion'];
		$variable3["fecha_creacion"] = date("Y-m-d H:i:s");
		$variable3["edo_reg"] = 'TRUE';
	}
	$co_proyecto_localizacion = $comunes->InsertUpdate($tabla3,$variable3,$tquery3,$id3);

	$tabla4="t36_proyecto_imagen";
	$co_proyecto_imagen = decode($_POST['co_proyecto_imagen']);
	if($co_proyecto_imagen!=''||$co_proyecto_imagen!=null){
		$tquery4="UPDATE";
		$id4 = 'co_proyecto_imagen = '.$co_proyecto_imagen;
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
		$variable4["fecha_actualizacion"] = date("Y-m-d H:i:s");
	}else{
		$tquery4="INSERT";
		$id4 = 'co_proyecto_imagen = '.$co_proyecto_imagen;
		$variable4["id_proyecto"] = $_POST['id_proyecto'];
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
		$variable4["fecha_creacion"] = date("Y-m-d H:i:s");
		$variable4["edo_reg"] = 'TRUE';
	}
	$co_proyecto_imagen = $comunes->InsertUpdate($tabla4,$variable4,$tquery4,$id4);

	$tabla5="t37_proyecto_responsables";
	$co_proyecto_responsables = decode($_POST['co_proyecto_responsables']);
	if($co_proyecto_responsables!=''||$co_proyecto_responsables!=null){
		$tquery5="UPDATE";
		$id5 = 'co_proyecto_responsables = '.$co_proyecto_responsables;
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
		$variable5["fecha_actualizacion"] = date("Y-m-d H:i:s");
	}else{
		$tquery5="INSERT";
		$id5 = 'co_proyecto_responsables = '.$co_proyecto_responsables;
		$variable5["id_proyecto"] = $_POST['id_proyecto'];
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
		$variable5["fecha_creacion"] = date("Y-m-d H:i:s");
		$variable5["edo_reg"] = 'TRUE';
	}
	$co_proyecto_responsables = $comunes->InsertUpdate($tabla5,$variable5,$tquery5,$id5);

	$tabla6="t38_proyecto_alcance";
	$co_proyecto_alcance = decode($_POST['co_proyecto_alcance']);
	if($co_proyecto_alcance!=''||$co_proyecto_alcance!=null){
		$tquery6="UPDATE";
		$id6 = 'co_proyecto_alcance = '.$co_proyecto_alcance;
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
		$variable6["co_unidades_medida"] = ($_POST['co_unidades_medida']=($_POST['co_unidades_medida']!='')?$_POST['co_unidades_medida']:NULL);
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
		$variable6["fecha_actualizacion"] = date("Y-m-d H:i:s");
	}else{
		$tquery6="INSERT";
		$id6 = 'co_proyecto_alcance = '.$co_proyecto_alcance;
		$variable6["id_proyecto"] = $_POST['id_proyecto'];
		$variable6["enunciado_inicial"] = $_POST['tx_epn'];
		$variable6["poblacion_afectada"] = $_POST['tx_pa'];
		$variable6["indicador_inicial"] = $_POST['tx_isi'];
		$variable6["formula_indicador"] = $_POST['tx_fi'];
		$variable6["fuente_indicador"] = $_POST['tx_fuentei'];
		list($dia, $mes, $anio) = explode("/",$_POST['fecha_sit_inicial']);
		$fecha_sit_inicial = $anio."-".$mes."-".$dia;
		$variable6["fecha_inisi"] = $fecha_sit_inicial;
		$variable6["enunciado_deseado"] = $_POST['tx_esd'];
		$variable6["poblacion_objetivo"] = $_POST['tx_po'];
		$variable6["indicador_deseado"] = $_POST['tx_isd'];
		$variable6["resultado_esperado"] = $_POST['tx_rebs'];
		$variable6["co_unidades_medida"] = ($_POST['co_unidades_medida']=($_POST['co_unidades_medida']!='')?$_POST['co_unidades_medida']:NULL);
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
		$variable6["fecha_creacion"] = date("Y-m-d H:i:s");
		$variable6["edo_reg"] = 'TRUE';
	}
	$co_proyecto_alcance = $comunes->InsertUpdate($tabla6,$variable6,$tquery6,$id6);

	$tabla7="t63_proyecto_financiamiento";
	$co_proyecto_financiamiento = decode($_POST['co_proyecto_financiamiento']);
	if($co_proyecto_financiamiento!=''||$co_proyecto_financiamiento!=null){
		$tquery7="UPDATE";
		$id7 = 'co_proyecto_financiamiento = '.$co_proyecto_financiamiento;
		$variable7["in_financiamiento"] = $_POST['in_financiamiento'];
		$variable7["in_tipo_financiamiento"] = $_POST['in_tipo_financiamiento'];
		$variable7["mo_parcial"] = $_POST['mo_parcial'];
		$variable7["co_tipo_fondo"] = ($_POST['co_tipo_fondo']=($_POST['co_tipo_fondo']!='')?$_POST['co_tipo_fondo']:NULL);
		$variable7["mo_financiar"] = $_POST['mo_financiar'];
		$variable7["tx_justificacion"] = $_POST['tx_justificacion'];
		$variable7["fecha_actualizacion"] = date("Y-m-d H:i:s");
	}else{
		$tquery7="INSERT";
		$id7 = 'co_proyecto_financiamiento = '.$co_proyecto_financiamiento;
		$variable7["id_proyecto"] = $_POST['id_proyecto'];
		$variable7["in_financiamiento"] = $_POST['in_financiamiento'];
		$variable7["in_tipo_financiamiento"] = $_POST['in_tipo_financiamiento'];
		$variable7["mo_parcial"] = $_POST['mo_parcial'];
		$variable7["co_tipo_fondo"] = ($_POST['co_tipo_fondo']=($_POST['co_tipo_fondo']!='')?$_POST['co_tipo_fondo']:NULL);
		$variable7["mo_financiar"] = $_POST['mo_financiar'];
		$variable7["tx_justificacion"] = $_POST['tx_justificacion'];
		$variable7["fecha_creacion"] = date("Y-m-d H:i:s");
		$variable7["edo_reg"] = 'TRUE';
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
}elseif($_GET['op']==9999){
	$tabla="t26_proyectos";
	$tquery="UPDATE";
	$id = 'co_proyectos = '.$_POST['co_proyectos'];
	$variable["edo_reg"] = "false"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Eliminado con Exito!'
	));
}elseif($_GET['op']==8888){
	$tabla="t26_proyectos";
	$tquery="UPDATE";
	$id = 'co_proyectos = '.$_POST['co_proyectos'];
	$variable["co_estatus"] = "1"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Proyecto Reaperturado con Exito!'
	));
}elseif($_GET['op']==7777){
	$codigo = decode($_POST['co_proyectos']);
	try{
		$paraTransaccion->BeginTrans();
		$tabla="t26_proyectos";
		$tquery="UPDATE";
		$id = 'co_proyectos = '.$codigo;
		$variable["co_estatus"] = "3"; 
		$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
		$co_metas = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

		if ($co_metas){
			$paraTransaccion->CommitTrans();
			echo json_encode(array(
				    "success" => true,
				    "c"  => $codigo,
				    "msg" => 'Proyecto Cerrado con Exito!'
			));
		}
		else{
			$paraTransaccion->RollbackTrans();
		}
	}catch(Exception $e){
		$mensaje = 'ocurrió una falla trabajando con la base de datos';
		$ms = array();
		if ( preg_match( '/ERROR\:\ *(.*)\s*CONTEXT\:/', $e->getMessage(), $ms ) === 1 ) {
			$mensaje = $ms[1];
		}
		echo json_encode(array(
			    "success" => false,
			    "msg" => "Error en Validacion: ".$mensaje
		));
	}
}
?>
