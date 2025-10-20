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

if($_GET['op']==1){
	$sql = "SELECT t67.*, de_unidad_medida, monto_cargado_proy_ae_meta(co_metas) AS mo_cargado FROM t67_metas as t67
	inner join mantenimiento.tab_unidad_medida as t21 on t67.co_unidades_medida=t21.id
	WHERE co_proyecto_acc_espec='".$_POST['co_proyecto_acc_espec']."' and t67.edo_reg is true ";

	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" and nb_meta ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_metas ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_metas"     => trim($row["co_metas"]),
		    "co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
		    "tx_codigo"     => trim($row["codigo"]),
		    "nb_meta"     => trim($row["nb_meta"]),
		    "co_unidades_medida"     => trim($row["de_unidad_medida"]),
		    "tx_prog_anual"     => trim($row["tx_prog_anual"]),
		    "fecha_inicio"     => trim(date_format(date_create($row["fecha_inicio"]),'d/m/Y')),
		    "fecha_fin"     => trim(date_format(date_create($row["fecha_fin"]),'d/m/Y')),
		    "nb_responsable"     => trim($row["nb_responsable"]),
		    "mo_cargado"     => trim($row["mo_cargado"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==2){
	$co_proyecto_acc_espec = decode($_POST['co_proyecto_acc_espec']);

	$sqlAcc = "SELECT total FROM t39_proyecto_acc_espec where co_proyecto_acc_espec=".$co_proyecto_acc_espec;
	$resultado = $comunes->ObtenerFilasBySqlSelect($sqlAcc);
	$resultadoReal = $resultado[0]['total'];
	$codigo = decode($_POST['co_metas']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t67_metas";
			$tquery="UPDATE";
			$id = 'co_metas = '.$codigo;
			$variable["nb_meta"] = decode($_POST['nb_actividad']);
			$variable["co_unidades_medida"] = decode($_POST['co_unidades_medida']);
			$variable["tx_prog_anual"] = decode($_POST['pr_anual']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fecha_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fecha_fin"] = $fecha_culminacion;
			$variable["nb_responsable"] = decode($_POST['nb_responsable']);
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_metas = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			$sql4 = "UPDATE t68_metas_detalle SET edo_reg = false WHERE co_metas = '$codigo';";
			$comunes->EjecutarQuery($sql4);

			$detalle = json_decode($_POST['json_detalle'],true); 

			$mo_validar = 0;
			foreach ($detalle as $lista){
				$mo_validar = $mo_validar+$lista['mo_presupuesto'];
				$tabla1="t68_metas_detalle";
				$primaryKey1="co_metas_detalle";
				$variable1["co_metas"] = decode($codigo);
				$variable1["co_municipio"] = decode($lista['co_municipio']);
				$variable1["co_parroquia"] = decode($lista['co_parroquia']);
				$variable1["mo_presupuesto"] = decode($lista['mo_presupuesto']);
				$variable1["co_partida"] = decode($lista['co_partida']);
				$variable1["co_fuente"] = decode($lista['co_fuente_financiamiento']);
				$variable1["fecha_creacion"] = date("Y-m-d H:i:s");
				$variable1["edo_reg"] = 'TRUE';
				$co_metas_detalle = $comunes->InsertConID($tabla1,$variable1,$primaryKey1);
			}
		
			if ($mo_validar>$resultadoReal){
				$paraTransaccion->RollbackTrans();
				echo json_encode(array(
					    "success" => false,
					    "msg" => '<span style="color:red;">Monto de Metas Financieras Supera al monto <br>de la Accion Especifica.</span>'
				));
			}else{

				if ($co_metas){
					$paraTransaccion->CommitTrans();
					echo json_encode(array(
						    "success" => true,
						    "msg" => 'Modificación realizada exitosamente.'
					));
				}
				else{
					$paraTransaccion->RollbackTrans();
				}
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
			$tabla="t67_metas";
			$primaryKey="co_metas";
			$variable["co_proyecto_acc_espec"] = decode($co_proyecto_acc_espec);
			$variable["nb_meta"] = decode($_POST['nb_actividad']);
			$variable["co_unidades_medida"] = decode($_POST['co_unidades_medida']);
			$variable["tx_prog_anual"] = decode($_POST['pr_anual']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fecha_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fecha_fin"] = $fecha_culminacion;
			$variable["nb_responsable"] = decode($_POST['nb_responsable']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_metas = $comunes->InsertConID($tabla,$variable,$primaryKey);

			$detalle = json_decode($_POST['json_detalle'],true); 

			$mo_validar = 0;
			foreach ($detalle as $lista){
				$mo_validar = $mo_validar+$lista['mo_presupuesto'];
				$tabla1="t68_metas_detalle";
				$primaryKey1="co_metas_detalle";
				$variable1["co_metas"] = decode($co_metas);
				$variable1["co_municipio"] = decode($lista['co_municipio']);
				$variable1["co_parroquia"] = decode($lista['co_parroquia']);
				$variable1["mo_presupuesto"] = decode($lista['mo_presupuesto']);
				$variable1["co_partida"] = decode($lista['co_partida']);
				$variable1["co_fuente"] = decode($lista['co_fuente_financiamiento']);
				$variable1["fecha_creacion"] = date("Y-m-d H:i:s");
				$variable1["edo_reg"] = 'TRUE';
				$co_metas_detalle = $comunes->InsertConID($tabla1,$variable1,$primaryKey1);
			}

			if ($mo_validar>$resultadoReal){
				$paraTransaccion->RollbackTrans();
				echo json_encode(array(
					    "success" => false,
					    "msg" => '<span style="color:red;">Monto de Metas Financieras Supera al monto <br>de la Accion Especifica.</span>'
				));
			}else{
				if ($co_metas){
					$paraTransaccion->CommitTrans();
					echo json_encode(array(
						    "success" => true,
						    "msg" => 'Proceso realizado exitosamente.'
					));
				}
				else{
					$paraTransaccion->RollbackTrans();
				}
			}
		}catch(Exception $e){
			echo json_encode(array(
				    "success" => false,
				    "msg" => "Error en Transaccion.\n".$e->getMessage()
			));
		}
	}
}elseif($_GET['op']==3){
	$sql = "SELECT *, tx_municipio, tx_parroquia, de_fuente_financiamiento FROM t68_metas_detalle as t68
	left join t64_municipio_detalle as t64 on t68.co_municipio=t64.co_municipio
	left join t65_parroquia_detalle as t65 on t68.co_parroquia=t65.co_parroquia
	inner join mantenimiento.tab_fuente_financiamiento as t66 on t68.co_fuente=t66.id
	WHERE co_metas='".$_POST['co_metas']."' AND t68.edo_reg is true ";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_metas_detalle ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_metas_detalle"     => trim($row["co_metas_detalle"]),
		    "co_metas"     => trim($row["co_metas"]),
		    "co_municipio"     => trim($row["co_municipio"]),
		    "tx_municipio"     => trim($row["tx_municipio"]),
		    "co_parroquia"     => trim($row["co_parroquia"]),
		    "tx_parroquia"     => trim($row["tx_parroquia"]),
		    "mo_presupuesto"     => trim($row["mo_presupuesto"]),
		    "co_partida"     => trim($row["co_partida"]),
		    "co_fuente_financiamiento"     => trim($row["co_fuente"]),
		    "tx_fuente_financiamiento"     => trim($row["de_fuente_financiamiento"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==4){
	$sql = "SELECT id_proyecto, tx_pa
		FROM t42_proyecto_acc_espec_partida as t42
		inner join t39_proyecto_acc_espec as t39 on t42.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
		inner join t44_partidas as t44 on t42.tx_pa=t44.co_partida
		WHERE t39.id_proyecto='".$_POST['id_proyecto']."' AND t42.edo_reg is true
		GROUP BY 1,2";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY tx_pa ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "co_partida"     => trim($row["tx_pa"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==5){
	$sql = "SELECT * FROM mantenimiento.tab_fuente_financiamiento where id_tab_tipo_fondo in (select t56.id_tipo_fondo
from t56_ac_ae_fuente as t56
where t56.id_ac = ".$_POST['id_accion_centralizada']." and t56.id_ae = ".$_POST['co_ac_acc_espec'].");";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_fuente_financiamiento"		=> $row["id"],
			"tx_fuente_financiamiento"	=> $row["de_fuente_financiamiento"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==6){
	$sql = "SELECT * FROM t64_municipio_detalle where co_estado=".$_POST['co_estado']." ORDER BY co_municipio ASC;";
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
}elseif($_GET['op']==7){
	$sql = "SELECT * FROM t65_parroquia_detalle where co_municipio=".$_POST['co_municipio']." ORDER BY co_parroquia ASC;";
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
}elseif($_GET['op']==8){
	$sql = "SELECT t69.*, de_unidad_medida, monto_cargado_ac_ae_meta(co_metas) AS mo_cargado FROM t69_metas_ac as t69
	inner join mantenimiento.tab_unidad_medida as t21 on t69.co_unidades_medida=t21.id
	WHERE id_accion_centralizada='".$_POST['id_accion_centralizada']."' and co_ac_acc_espec='".$_POST['co_proyecto_acc_espec']."' and t69.edo_reg is true ";

	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" and nb_meta ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY codigo ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_metas"     => trim($row["co_metas"]),
		    "co_proyecto_acc_espec"     => trim($row["co_ac_acc_espec"]),
		    "tx_codigo"     => trim($row["codigo"]),
		    "nb_meta"     => trim($row["nb_meta"]),
		    "co_unidades_medida"     => trim($row["de_unidad_medida"]),
		    "tx_prog_anual"     => trim($row["tx_prog_anual"]),
		    "fecha_inicio"     => trim(date_format(date_create($row["fecha_inicio"]),'d/m/Y')),
		    "fecha_fin"     => trim(date_format(date_create($row["fecha_fin"]),'d/m/Y')),
		    "nb_responsable"     => trim($row["nb_responsable"]),
		    "mo_cargado"     => trim($row["mo_cargado"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==9){
	$id_accion_centralizada = decode($_POST['id_accion_centralizada']);
	$co_ac_acc_espec = decode($_POST['co_ac_acc_espec']);
	$codigo = decode($_POST['co_metas']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t69_metas_ac";
			$tquery="UPDATE";
			$id = 'co_metas = '.$codigo;
			$variable["nb_meta"] = decode(str_replace('"', '', $_POST['nb_actividad']));
			$variable["co_unidades_medida"] = decode($_POST['co_unidades_medida']);
			$variable["tx_prog_anual"] = decode($_POST['pr_anual']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fecha_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fecha_fin"] = $fecha_culminacion;
                        $variable["nb_responsable"] = decode(str_replace('"', '', $_POST['nb_responsable']));
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_metas = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			$sql4 = "UPDATE t70_metas_ac_detalle SET edo_reg = false WHERE co_metas = '$codigo';";
			$comunes->EjecutarQuery($sql4);

			$detalle = json_decode($_POST['json_detalle'],true); 

			foreach ($detalle as $lista){
				$tabla1="t70_metas_ac_detalle";
				$primaryKey1="co_metas_detalle";
				$variable1["co_metas"] = decode($codigo);
				$variable1["co_municipio"] = decode($lista['co_municipio']);
				$variable1["co_parroquia"] = decode($lista['co_parroquia']);
				$variable1["mo_presupuesto"] = decode($lista['mo_presupuesto']);
				$variable1["co_partida"] = decode($lista['co_partida']);
				$variable1["co_fuente"] = decode($lista['co_fuente_financiamiento']);
				$variable1["fecha_creacion"] = date("Y-m-d H:i:s");
				$variable1["edo_reg"] = 'TRUE';
				$co_metas_detalle = $comunes->InsertConID($tabla1,$variable1,$primaryKey1);
			}

			if ($co_metas){
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
			$tabla="t69_metas_ac";
			$primaryKey="co_metas";
			$variable["id_accion_centralizada"] = decode($id_accion_centralizada);
			$variable["co_ac_acc_espec"] = decode($co_ac_acc_espec);
			$variable["nb_meta"] = decode(str_replace('"', '', $_POST['nb_actividad']));
			$variable["co_unidades_medida"] = decode($_POST['co_unidades_medida']);
			$variable["tx_prog_anual"] = decode($_POST['pr_anual']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fecha_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fecha_fin"] = $fecha_culminacion;
			$variable["nb_responsable"] = decode(str_replace('"', '', $_POST['nb_responsable']));
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_metas = $comunes->InsertConID($tabla,$variable,$primaryKey);

			$detalle = json_decode($_POST['json_detalle'],true); 

			foreach ($detalle as $lista){
				$tabla1="t70_metas_ac_detalle";
				$primaryKey1="co_metas_detalle";
				$variable1["co_metas"] = decode($co_metas);
				$variable1["co_municipio"] = decode($lista['co_municipio']);
				$variable1["co_parroquia"] = decode($lista['co_parroquia']);
				$variable1["mo_presupuesto"] = decode($lista['mo_presupuesto']);
				$variable1["co_partida"] = decode($lista['co_partida']);
				$variable1["co_fuente"] = decode($lista['co_fuente_financiamiento']);
				$variable1["fecha_creacion"] = date("Y-m-d H:i:s");
				$variable1["edo_reg"] = 'TRUE';
				$co_metas_detalle = $comunes->InsertConID($tabla1,$variable1,$primaryKey1);
			}

			if ($co_metas){
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
}elseif($_GET['op']==10){
	$sql = "SELECT *, tx_municipio, tx_parroquia, de_fuente_financiamiento FROM t70_metas_ac_detalle as t70
	left join t64_municipio_detalle as t64 on t70.co_municipio=t64.co_municipio
	left join t65_parroquia_detalle as t65 on t70.co_parroquia=t65.co_parroquia
	inner join mantenimiento.tab_fuente_financiamiento
 as t66 on t70.co_fuente=t66.id
	WHERE co_metas='".$_POST['co_metas']."' AND t70.edo_reg is true ";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_metas_detalle ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_metas_detalle"     => trim($row["co_metas_detalle"]),
		    "co_metas"     => trim($row["co_metas"]),
		    "co_municipio"     => trim($row["co_municipio"]),
		    "tx_municipio"     => trim($row["tx_municipio"]),
		    "co_parroquia"     => trim($row["co_parroquia"]),
		    "tx_parroquia"     => trim($row["tx_parroquia"]),
		    "mo_presupuesto"     => trim($row["mo_presupuesto"]),
		    "co_partida"     => trim($row["co_partida"]),
		    "co_fuente_financiamiento"     => trim($row["co_fuente"]),
		    "tx_fuente_financiamiento"     => trim($row["de_fuente_financiamiento"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==11){
	$sql = "SELECT id_accion_centralizada, id_accion, left(co_partida, 3) as co_partida
  	FROM t54_ac_ae_partidas
	WHERE id_accion_centralizada='".$_POST['id_accion_centralizada']."' AND id_accion='".$_POST['co_ac_acc_espec']."'
  	group by 1,2,3 order by 1,2,3 ASC;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_partida"		=> $row["co_partida"],
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));
}elseif($_GET['op']==12){
	$codigo = decode($_POST['co_metas']);
	try{
		$paraTransaccion->BeginTrans();
		$tabla="t69_metas_ac";
		$tquery="UPDATE";
		$id = 'co_metas = '.$codigo;
		$variable["edo_reg"] = 'FALSE';
		$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
		$co_metas = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

		$sql4 = "UPDATE t70_metas_ac_detalle SET edo_reg = false , fecha_actualizacion = '".date("Y-m-d H:i:s")."' WHERE co_metas = '$codigo';";
		$comunes->EjecutarQuery($sql4);

		if ($co_metas){
			$paraTransaccion->CommitTrans();
			echo json_encode(array(
				    "success" => true,
				    "msg" => 'Registro Borrado con Exito!'
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
}elseif($_GET['op']==13){
	$codigo = decode($_POST['co_metas']);
	try{
		$paraTransaccion->BeginTrans();
		$tabla="t67_metas";
		$tquery="UPDATE";
		$id = 'co_metas = '.$codigo;
		$variable["edo_reg"] = 'FALSE';
		$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
		$co_metas = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

		$sql4 = "UPDATE t68_metas_detalle SET edo_reg = false , fecha_actualizacion = '".date("Y-m-d H:i:s")."' WHERE co_metas = '$codigo';";
		$comunes->EjecutarQuery($sql4);

		if ($co_metas){
			$paraTransaccion->CommitTrans();
			echo json_encode(array(
				    "success" => true,
				    "msg" => 'Registro Borrado con Exito!'
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
?>
