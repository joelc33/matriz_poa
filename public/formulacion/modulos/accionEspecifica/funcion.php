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
	$sql = "SELECT t39.*, de_unidad_medida, tx_ejecutor, monto_cargado_ae_proy(co_proyecto_acc_espec) as mo_cargado FROM t39_proyecto_acc_espec as t39
	inner join mantenimiento.tab_unidad_medida as t21 on t39.co_unidades_medida=t21.id
	inner join mantenimiento.tab_ejecutores as t24 on t39.co_ejecutores=t24.id
	WHERE id_proyecto='".$_POST['id_proyecto']."' AND t39.edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 10: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY tx_codigo ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
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
}elseif($_GET['op']==2){
	$codigo = decode($_POST['co_proyecto_acc_espec']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="t39_proyecto_acc_espec";
			$tquery="UPDATE";
			$id = 'co_proyecto_acc_espec = '.$codigo;
			$variable["descripcion"] = decode($_POST['nb_accion']);
			$variable["co_unidades_medida"] = decode($_POST['co_unidades_medida']);
			$variable["meta"] = decode($_POST['nu_meta']);
			$variable["ponderacion"] = decode($_POST['nu_ponderacion']);
			$variable["bien_servicio"] = decode($_POST['op_bien_servicio']);
			$variable["total"] = decode($_POST['mo_total_general']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fec_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fec_termino"] = $fecha_culminacion;
			$variable["co_ejecutores"] = decode($_POST['co_ejecutores']);
			$variable["tx_objetivo_institucional"] = decode($_POST['tx_objetivo_institucional']);
			$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$co_proyecto_acc_espec = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_proyecto_acc_espec){
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
			$tabla="t39_proyecto_acc_espec";
			$primaryKey="co_proyecto_acc_espec";
			$variable["id_proyecto"] = decode($_POST['id_proyecto']);
			$variable["descripcion"] = decode($_POST['nb_accion']);
			$variable["co_unidades_medida"] = decode($_POST['co_unidades_medida']);
			$variable["meta"] = decode($_POST['nu_meta']);
			$variable["ponderacion"] = decode($_POST['nu_ponderacion']);
			$variable["bien_servicio"] = decode($_POST['op_bien_servicio']);
			$variable["total"] = decode($_POST['mo_total_general']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fec_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fec_termino"] = $fecha_culminacion;
			$variable["co_ejecutores"] = decode($_POST['co_ejecutores']);
			$variable["tx_objetivo_institucional"] = decode($_POST['tx_objetivo_institucional']);
			$variable["fecha_creacion"] = date("Y-m-d H:i:s");
			$variable["edo_reg"] = 'TRUE';
			$co_proyecto_acc_espec = $comunes->InsertConID($tabla,$variable,$primaryKey);

			if ($co_proyecto_acc_espec){
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
	$codigo = decode($_POST['id_proyecto']);
	try{
		$paraTransaccion->BeginTrans();
		if(array_key_exists("archivo", $_FILES)){
		if($_FILES["archivo"]["tmp_name"]!='')
		{
		/** Incluir la clase PHPExcel_IOFactory agregada en el directorio /lib/vendor/PHPExcel */
		include("../../plugins/reader/Classes/PHPExcel/IOFactory.php");

			//Funciones extras

			function get_cell($cell, $objPHPExcel){
				//seleccionar una celda
				$objCell = ($objPHPExcel->getActiveSheet()->getCell($cell));
				//tomar valor de la celda
				return $objCell->getvalue();
			}

			function pp(&$var){
				$var = chr(ord($var)+1);
				return true;
			}

			$name	  = $_FILES['archivo']['name'];
			$tname 	  = $_FILES['archivo']['tmp_name'];
			$type 	  = $_FILES['archivo']['type'];

			if($type == 'application/vnd.ms-excel')
			{
				// Extension excel 97
				$ext = 'xls';
			}
			else if($type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
			{
				// Extension excel 2007 y 2010
				$ext = 'xlsx';
			}else{
				// Extension no valida
				echo -1;
				exit();
			}

			$xlsx = 'Excel2007';
			$xls  = 'Excel5';

			//creando el lector
			$objReader = PHPExcel_IOFactory::createReader($$ext);

			//cargamos el archivo
			$objPHPExcel = $objReader->load($tname);

			$dim = $objPHPExcel->getActiveSheet()->calculateWorksheetDimension();

			// list coloca en array $start y $end
			list($start, $end) = explode(':', $dim);

			if(!preg_match('#([A-Z]+)([0-9]+)#', $start, $rslt)){
				return false;
			}
			list($start, $start_h, $start_v) = $rslt;
			if(!preg_match('#([A-Z]+)([0-9]+)#', $end, $rslt)){
				return false;
			}
			list($end, $end_h, $end_v) = $rslt;

			$sql = "DELETE FROM t43_acc_espec_partida_tmp WHERE id_proyecto = '$codigo';";
			$comunes->EjecutarQuery($sql);
			$sql2 = "UPDATE t42_proyecto_acc_espec_partida SET edo_reg=false
				FROM t39_proyecto_acc_espec
				WHERE t42_proyecto_acc_espec_partida.co_proyecto_acc_espec = t39_proyecto_acc_espec.co_proyecto_acc_espec
				AND id_proyecto = '$codigo';";
			$comunes->EjecutarQuery($sql2);
			/*$tabla2="t42_proyecto_acc_espec_partida";
			$tquery2="UPDATE";
			$id2 = 'id_proyecto = '.$codigo;
			$variable2["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$variable2["edo_reg"] = 'FALSE';
			$co_update = $comunes->InsertUpdate($tabla2,$variable2,$tquery2,$id2);*/
			$contador = 0;
			$abecedario = range('F', 'Z');
			foreach($abecedario as $abc){
				$contenido = get_cell($abc.'9', $objPHPExcel);
					if($contenido!=''||$contenido!=null){
						$contador = $contador+1;
						$tx_codigo = str_pad($contador, 4, '0', STR_PAD_LEFT);
			/*$sqlSecuencia = "
			DROP TABLE IF EXISTS a_cursor;
			SELECT tx_codigo, lpad((row_number() OVER (ORDER BY tx_codigo))::text, 4, '0') as num_tabla into temp a_cursor
			FROM t39_proyecto_acc_espec
			WHERE edo_reg is true and id_proyecto='$codigo' order by 1 asc;
			select tx_codigo from a_cursor where num_tabla='$tx_codigo';";*/
			$sqlSecuencia = "
			DROP TABLE IF EXISTS a_cursor;
			SELECT tx_codigo, lpad((row_number() OVER (ORDER BY tx_codigo))::text, 4, '0') as num_tabla,
			sp_verificar_hijo_ae(co_proyecto_acc_espec) as in_foraneo into temp a_cursor
			FROM t39_proyecto_acc_espec
			WHERE edo_reg is true and id_proyecto='$codigo' and
			sp_verificar_hijo_ae(co_proyecto_acc_espec) is false order by 1 asc;
			select num_tabla,tx_codigo, in_foraneo from a_cursor where in_foraneo is false and num_tabla='$tx_codigo';";
			$resultadoSecuencia = $comunes->ObtenerFilasBySqlSelect($sqlSecuencia);
			$tx_codigo = $resultadoSecuencia[0]['tx_codigo'];

			//empieza  lectura vertical
			$start_v=10;
			$end_v=1923;
			for($v=$start_v; $v<=$end_v; $v++){
				//empieza lectura horizontal
				for($h=$start_h; ord($h)<=ord($end_h); pp($h)){
					$cellValue1 = get_cell("A".$v, $objPHPExcel);
					$cellValue2 = get_cell("B".$v, $objPHPExcel);
					$cellValue3 = get_cell("C".$v, $objPHPExcel);
					$cellValue4 = get_cell("D".$v, $objPHPExcel);
					$cellValue5 = get_cell("E".$v, $objPHPExcel);
					$cellValue6 = get_cell($abc.$v, $objPHPExcel);
				}

				$tabla="t43_acc_espec_partida_tmp";
				$tquery="INSERT";
				$variable["id_proyecto"] = $codigo;
				$variable["id_tab_ejercicio_fiscal"] = $_SESSION['ejercicio_fiscal'];
				$variable["tx_codigo"] = $tx_codigo;
				$variable["tx_pa"] = $cellValue1;
				$variable["tx_pa"] = $cellValue1;
				$variable["tx_ge"] = $cellValue2;
				$variable["tx_es"] = $cellValue3;
				$variable["tx_se"] = $cellValue4;
				$variable["tx_denominacion"] = $cellValue5;
				$variable["nu_monto"] = $cellValue6;
				$variable["fecha_creacion"] = date("Y-m-d H:i:s");
				$variable["edo_reg"] = 'TRUE';
				$co_partida_tmp = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);
			}

		}
		}


		}
	}


			if ($co_partida_tmp){
				$paraTransaccion->CommitTrans();
				echo json_encode(array(
					"success" => true,
					"msg" => 'Archivo procesado Exitosamente<br>Se leyeron '.$end_v.' Filas.'
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
}if($_GET['op']==4){
	$sql = "SELECT co_partida_acc_espec, co_proyecto_acc_espec, tx_pa, tx_ge, tx_es, tx_se,
	tx_sse, tx_denominacion, nu_monto, t42.edo_reg,
	t44.co_partida, tx_nombre
	FROM t42_proyecto_acc_espec_partida as t42
	inner join mantenimiento.tab_partidas as t44 on t42.tx_pa||tx_ge=t44.co_partida
	WHERE co_proyecto_acc_espec='".$_POST['co_proyecto_acc_espec']."' AND t42.edo_reg is true AND t44.id_tab_ejercicio_fiscal=t42.id_tab_ejercicio_fiscal";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 100: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY tx_pa,tx_ge,tx_es,tx_se ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_partida_acc_espec"     => trim($row["co_partida_acc_espec"]),
		    "tx_partida"     => trim($row["tx_pa"].'.'.$row["tx_ge"].'.'.$row["tx_es"].'.'.$row["tx_se"].'.'.$row["tx_sse"]),
		    "tx_denominacion"     => trim($row["tx_denominacion"]),
		    "nu_monto"     => trim($row["nu_monto"]),
		    "tx_partida_madre"     => $row["co_partida"].':'.$row["tx_nombre"],
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==5){

	$tabla="t39_proyecto_acc_espec";
	$tquery="UPDATE";
	$id = 'co_proyecto_acc_espec = '.$_POST['co_proyecto_acc_espec'];
	$variable["fecha_actualizacion"] = date("Y-m-d H:i:s");
	$variable["edo_reg"] = "false";
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	$tabla2="t40_proyecto_acc_espec_rec";
	$tquery2="UPDATE";
	$id2 = 'co_proyecto_acc_espec = '.$_POST['co_proyecto_acc_espec'];
	$variable2["fecha_actualizacion"] = date("Y-m-d H:i:s");
	$variable2["edo_reg"] = "false";
	$query2 = $comunes->InsertUpdate($tabla2,$variable2,$tquery2,$id2);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Eliminado con Exito!'
	));
}elseif($_GET['op']==6){
	$sql = "SELECT co_proyecto_acc_espec_rec, t40.id_proyecto, t40.co_proyecto_acc_espec, 'Financiera (Bs)' AS tx_distribucion,
	presup_01, presup_02, presup_03, presup_04, presup_05, presup_06,
	presup_07, presup_08, presup_09, presup_10, presup_11, presup_12,
	t40.fecha_creacion, t40.edo_reg, tx_codigo, descripcion
	FROM t40_proyecto_acc_espec_rec as t40
	inner join t39_proyecto_acc_espec as t39 on t40.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	WHERE t40.id_proyecto='".$_POST['id_proyecto']."' AND t40.edo_reg is true and t39.edo_reg is true
	UNION
	SELECT co_proyecto_acc_espec_rec, t40.id_proyecto, t40.co_proyecto_acc_espec,'Fisica' AS tx_distribucion,
	fisico_01, fisico_02, fisico_03, fisico_04, fisico_05, fisico_06,
	fisico_07, fisico_08, fisico_09, fisico_10, fisico_11, fisico_12,
	t40.fecha_actualizacion as fecha_creacion, t40.edo_reg, tx_codigo, descripcion
	FROM t40_proyecto_acc_espec_rec as t40
	inner join t39_proyecto_acc_espec as t39 on t40.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	WHERE t40.id_proyecto='".$_POST['id_proyecto']."' AND t40.edo_reg is true and t39.edo_reg is true and id_padre is null";

	/*$sql = "SELECT t40.*, tx_codigo, descripcion FROM t40_proyecto_acc_espec_rec as t40
	inner join t39_proyecto_acc_espec as t39 on t40.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	WHERE t40.id_proyecto='".$_POST['id_proyecto']."' AND t40.edo_reg is true";*/

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY tx_codigo, tx_distribucion, fecha_creacion ASC LIMIT ".$limit." OFFSET ".$start;}

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
		    "co_proyecto_acc_espec_rec"     => trim($row["co_proyecto_acc_espec_rec"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
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
		    "co_proyecto_acc_espec_rec"     => trim($row["co_proyecto_acc_espec_rec"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
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
}elseif($_GET['op']==7){
	$sql = "SELECT t40.*, tx_codigo, descripcion FROM t40_proyecto_acc_espec_rec as t40
	inner join t39_proyecto_acc_espec as t39 on t40.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	WHERE t40.id_proyecto='".$_POST['id_proyecto']."' AND t40.edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY co_proyecto_acc_espec_rec ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$trimestre_01 = $row["presup_01"]+$row["presup_02"]+$row["presup_03"];
		$trimestre_02 = $row["presup_04"]+$row["presup_05"]+$row["presup_06"];
		$trimestre_03 = $row["presup_07"]+$row["presup_08"]+$row["presup_09"];
		$trimestre_04 = $row["presup_10"]+$row["presup_11"]+$row["presup_12"];
		$mo_total = $trimestre_01+$trimestre_02+$trimestre_03+$trimestre_04;
		$data[] = array(
		    "co_proyecto_acc_espec_rec"     => trim($row["co_proyecto_acc_espec_rec"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
		    "tx_codigo"     => trim($row["tx_codigo"]),
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
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==8){
	$sql = "SELECT t47.*, tx_unidades_medida, tx_ejecutor, ('AC' || t24.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0')) as id_ac, t53.nu_numero as numero, t53.de_nombre as nombre, mo_ac_ae_meta(id_accion_centralizada, t47.id_accion) AS mo_cargado FROM t47_ac_accion_especifica as t47
	inner join t21_unidades_medida as t21 on t47.id_unidad_medida=t21.co_unidades_medida
	inner join t24_ejecutores as t24 on t47.id_ejecutor=t24.id_ejecutor
	inner join t46_acciones_centralizadas as t46 on t47.id_accion_centralizada=t46.id
	inner join mantenimiento.tab_ac_ae_predefinida as t53 on t53.id = t47.id_accion
	WHERE ('AC' || t24.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0')) = '".$_POST['id_proyecto']."' AND t47.edo_reg is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 5: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY id_accion ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec"     => trim($row["id_accion"]),
		    "id_accion_centralizada"     => trim($row["id_accion_centralizada"]),
		    "id_proyecto"     => trim($row["id_ac"]),
		    "tx_codigo"     => trim($row["numero"]),
		    "descripcion"     => trim($row["nombre"]),
		    "co_unidades_medida"     => trim($row["tx_unidades_medida"]),
		    "meta"     => trim($row["meta"]),
		    "ponderacion"     => trim($row["ponderacion"]),
		    "bien_servicio"     => trim($row["bien_servicio"]),
		    "total"     => trim($row["monto"]),
		    "mo_cargado"     => trim($row["mo_cargado"]),
		    "fec_inicio"     => trim(date_format(date_create($row["fecha_inicio"]),'d/m/Y')),
		    "fec_termino"     => trim(date_format(date_create($row["fecha_fin"]),'d/m/Y')),
		    "co_ejecutores"     => trim($row["tx_ejecutor"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==9){
	$sql = "SELECT t39.*, de_unidad_medida, t24.tx_ejecutor, mo_proy_ae_meta(co_proyecto_acc_espec) as mo_cargado, t24b.tx_ejecutor as ejecutor_resp, sp_verificar_hijo_ae(t39.co_proyecto_acc_espec) as cargar, t26.nombre as nb_proyecto
	FROM t39_proyecto_acc_espec as t39
	inner join mantenimiento.tab_unidad_medida as t21 on t39.co_unidades_medida=t21.id
	inner join mantenimiento.tab_ejecutores as t24 on t39.co_ejecutores=t24.id
	inner join t26_proyectos as t26 on t39.id_proyecto=t26.id_proyecto
	inner join mantenimiento.tab_ejecutores as t24b on t26.id_ejecutor=t24b.id_ejecutor
	";

	if($_SESSION['co_rol']>2){
		$sql.=" WHERE co_ejecutores='".$_SESSION['co_ejecutores']."' AND t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' AND t39.edo_reg is true AND t26.edo_reg is true ";
	}else{
		$sql.=" WHERE t26.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' AND t39.edo_reg is true AND t26.edo_reg is true ";
	}

	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" and t24b.tx_ejecutor ILIKE '%".$_POST['variable']."%'";}
		//if($_POST['variable']!=""){$sql.=" or t39.id_proyecto ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 15: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY id_proyecto, tx_codigo ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
		    "id_proyecto"     => trim($row["id_proyecto"]),
		    "nb_proyecto"     => ($row["id_proyecto"].'- '.$row["nb_proyecto"]),
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
		    "ejecutor_resp"     => trim($row["ejecutor_resp"]),
			"cargar" => ( $row["cargar"] == 'f' )
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==10){
	$sql = "SELECT t47.*, t52.nombre as nb_ac, de_unidad_medida as tx_unidades_medida, t24.tx_ejecutor, ('AC' || t24b.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0')) as id_ac, t53.nu_numero as numero, t53.de_nombre as nombre, mo_ac_ae_meta(id_accion_centralizada, t47.id_accion) AS mo_cargado, t24b.tx_ejecutor as ejecutor_resp FROM t47_ac_accion_especifica as t47
	inner join mantenimiento.tab_unidad_medida as t21 on t47.id_unidad_medida=t21.id
	inner join mantenimiento.tab_ejecutores as t24 on t47.id_ejecutor=t24.id_ejecutor
	inner join t46_acciones_centralizadas as t46 on t47.id_accion_centralizada=t46.id
	inner join mantenimiento.tab_ejecutores as t24b on t46.id_ejecutor=t24b.id_ejecutor
	inner join t52_ac_predefinidas as t52 on t52.id = t46.id_accion
	inner join mantenimiento.tab_ac_ae_predefinida as t53 on t53.id = t47.id_accion
	";

	if($_SESSION['co_rol']>2){
		$sql.=" WHERE t46.id_ejecutor = '".$_SESSION['id_ejecutor']."' AND t46.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' AND t47.edo_reg is true AND t46.edo_reg is true";
	}else{
		$sql.=" WHERE t46.id_ejercicio = '".$_SESSION['ejercicio_fiscal']."' AND t47.edo_reg is true AND t46.edo_reg is true";
	}

	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" and t24b.tx_ejecutor ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 15: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t46.id_ejecutor, t46.id_accion  ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec"     => trim($row["id_accion"]),
		    "id_accion_centralizada"     => trim($row["id_accion_centralizada"]),
		    "id_proyecto"     => trim($row["id_ac"]),
		    "nb_ac"     => trim($row["id_ac"].' - '.$row["nb_ac"]),
		    "tx_codigo"     => trim($row["numero"]),
		    "descripcion"     => trim($row["nombre"]),
		    "co_unidades_medida"     => trim($row["tx_unidades_medida"]),
		    "meta"     => trim($row["meta"]),
		    "ponderacion"     => trim($row["ponderacion"]),
		    "bien_servicio"     => trim($row["bien_servicio"]),
		    "total"     => trim($row["monto"]),
		    "mo_cargado"     => trim($row["mo_cargado"]),
		    "fec_inicio"     => trim(date_format(date_create($row["fecha_inicio"]),'d/m/Y')),
		    "fec_termino"     => trim(date_format(date_create($row["fecha_fin"]),'d/m/Y')),
		    "co_ejecutores"     => trim($row["tx_ejecutor"]),
		    "ejecutor_resp"     => trim($row["ejecutor_resp"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));
}elseif($_GET['op']==12){

	$ac = $_POST["id_accion_centralizada"];
	$ae = $_POST["codigo"];

	$sql = "DROP TABLE IF EXISTS orden_cursor_ac;
SELECT co_metas, codigo as original, lpad((row_number() OVER (ORDER BY codigo))::text, 3, '0') as corregido into temp orden_cursor_ac
FROM t69_metas_ac where id_accion_centralizada= ".$ac."  and co_ac_acc_espec = ".$ae." and edo_reg is true order by co_metas asc;
--select co_metas, original, corregido from orden_cursor_ac;

UPDATE t69_metas_ac t1
SET codigo = t2.corregido
FROM orden_cursor_ac t2 WHERE t1.co_metas = t2.co_metas;

DELETE FROM t69_metas_ac WHERE id_accion_centralizada= ".$ac."  and co_ac_acc_espec = ".$ae." and edo_reg is false;

--select co_metas, original, corregido from orden_cursor_ac;
	";

	$res = $comunes->EjecutarQuery($sql);
	if ($res) {
		$success = 'true';
		$msg = 'Actividades Reordenadas con Exito!';
	} else {
		$success = 'false';
		$msg = 'Hubo problemas con los datos!';
	}

	echo json_encode(array(
		    "success" => $success,
		    "msg" => $msg
	));
}elseif($_GET['op']==13){

	$ae = $_POST["co_proyecto_acc_espec"];

	$sql = "DROP TABLE IF EXISTS orden_cursor_pr;
SELECT co_metas, codigo as original, lpad((row_number() OVER (ORDER BY codigo))::text, 3, '0') as corregido into temp orden_cursor_pr
FROM t67_metas where co_proyecto_acc_espec= ".$ae."  and edo_reg is true order by co_metas asc;
--select co_metas, original, corregido from orden_cursor_pr;

UPDATE t67_metas t1
SET codigo = t2.corregido
FROM orden_cursor_pr t2 WHERE t1.co_metas = t2.co_metas;

DELETE FROM t67_metas WHERE co_proyecto_acc_espec= ".$ae." and edo_reg is false;

--select co_metas, original, corregido from orden_cursor_pr;
	";

	$res = $comunes->EjecutarQuery($sql);
	if ($res) {
		$success = 'true';
		$msg = 'Actividades Reordenadas con Exito!';
	} else {
		$success = 'false';
		$msg = 'Hubo problemas con los datos!';
	}

	echo json_encode(array(
		    "success" => $success,
		    "msg" => $msg
	));
}
?>
