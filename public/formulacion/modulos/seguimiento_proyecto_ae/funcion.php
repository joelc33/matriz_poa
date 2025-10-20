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

	$sql = "SELECT t42.id, id_tab_proyecto_ae, tx_pa, tx_ge, tx_es, tx_se, tx_denominacion, nu_monto, t42.in_activo,
	t44.co_partida, tx_nombre
	FROM proyecto_seguimiento.tab_proyecto_aepartida as t42
	inner join mantenimiento.tab_partidas as t44 on t42.tx_pa||tx_ge=t44.co_partida
	WHERE id_tab_proyecto_ae='".$_POST['co_proyecto_acc_espec']."' AND t42.in_activo is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 100: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY tx_pa,tx_ge,tx_es,tx_se ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_partida_acc_espec"     => trim($row["id"]),
		    "tx_partida"     => trim($row["tx_pa"].'.'.$row["tx_ge"].'.'.$row["tx_es"].'.'.$row["tx_se"]),
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

		break;
	case 2:

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

	$tabla="proyecto_seguimiento.tab_proyecto_ae";
	$tquery="UPDATE";
	$id = 'id = '.$_POST['co_proyecto_acc_espec'];
	$variable["updated_at"] = date("Y-m-d H:i:s");
	$variable["in_activo"] = "false"; 
	$query = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

	echo json_encode(array(
		    "success" => true,
		    "msg" => 'Registro Eliminado con Exito!'
	));

		break;
	case 5:

	$codigo = decode($_POST['co_proyecto_acc_espec_rec']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="proyecto_seguimiento.tab_proyecto_aerec";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["fisico_01"] = decode($_POST['fisico_01']);
			$variable["fisico_02"] = decode($_POST['fisico_02']);
			$variable["fisico_03"] = decode($_POST['fisico_03']);
			$variable["fisico_04"] = decode($_POST['fisico_04']);
			$variable["fisico_05"] = decode($_POST['fisico_05']);
			$variable["fisico_06"] = decode($_POST['fisico_06']);
			$variable["fisico_07"] = decode($_POST['fisico_07']);
			$variable["fisico_08"] = decode($_POST['fisico_08']);
			$variable["fisico_09"] = decode($_POST['fisico_09']);
			$variable["fisico_10"] = decode($_POST['fisico_10']);
			$variable["fisico_11"] = decode($_POST['fisico_11']);
			$variable["fisico_12"] = decode($_POST['fisico_12']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
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

		break;
	case 6:

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

			$sql = "DELETE FROM proyecto_seguimiento.tmp_proyecto_aepartida WHERE id_tab_proyecto = '$codigo';";
			$comunes->EjecutarQuery($sql);
			$sql2 = "UPDATE proyecto_seguimiento.tab_proyecto_aepartida SET in_activo=false 
				FROM proyecto_seguimiento.tab_proyecto_ae
				WHERE proyecto_seguimiento.tab_proyecto_aepartida.id_tab_proyecto_ae =
 				proyecto_seguimiento.tab_proyecto_ae.id
				AND id_tab_proyecto = '$codigo';";
			$comunes->EjecutarQuery($sql2);
			/*$tabla2="t42_proyecto_acc_espec_partida";
			$tquery2="UPDATE";
			$id2 = 'id_proyecto = '.$codigo;
			$variable2["fecha_actualizacion"] = date("Y-m-d H:i:s");
			$variable2["edo_reg"] = 'FALSE';
			$co_update = $comunes->InsertUpdate($tabla2,$variable2,$tquery2,$id2);*/
			$contador = 0;
			$abecedario = range('G', 'Z');
			foreach($abecedario as $abc){
				$contenido = get_cell($abc.'10', $objPHPExcel);
					if($contenido!=''||$contenido!=null){
						$contador = $contador+1;
						$tx_codigo = str_pad($contador, 4, '0', STR_PAD_LEFT);
			$sqlSecuencia = "
			DROP TABLE IF EXISTS a_cursor;
			SELECT tx_codigo, lpad((row_number() OVER (ORDER BY tx_codigo))::text, 4, '0') as num_tabla into temp a_cursor
			FROM proyecto_seguimiento.tab_proyecto_ae
			WHERE in_activo is true and id_tab_proyecto='$codigo' order by 1 asc;
			select tx_codigo from a_cursor where num_tabla='$tx_codigo';";
			$resultadoSecuencia = $comunes->ObtenerFilasBySqlSelect($sqlSecuencia);
			$tx_codigo = $resultadoSecuencia[0]['tx_codigo'];

			//empieza  lectura vertical
			$start_v=11;
			$end_v=473;
			for($v=$start_v; $v<=$end_v; $v++){
				//empieza lectura horizontal
				for($h=$start_h; ord($h)<=ord($end_h); pp($h)){
					$cellValue1 = get_cell("B".$v, $objPHPExcel);
					$cellValue2 = get_cell("C".$v, $objPHPExcel);
					$cellValue3 = get_cell("D".$v, $objPHPExcel);
					$cellValue4 = get_cell("E".$v, $objPHPExcel);
					$cellValue5 = get_cell("F".$v, $objPHPExcel);
					$cellValue6 = get_cell($abc.$v, $objPHPExcel);
				}

				$tabla="proyecto_seguimiento.tmp_proyecto_aepartida";
				$tquery="INSERT";
				$variable["id_tab_proyecto"] = $codigo;
				$variable["tx_codigo"] = $tx_codigo;
				$variable["tx_pa"] = $cellValue1;
				$variable["tx_pa"] = $cellValue1;
				$variable["tx_ge"] = $cellValue2;
				$variable["tx_es"] = $cellValue3;
				$variable["tx_se"] = $cellValue4;
				$variable["tx_denominacion"] = $cellValue5;
				$variable["nu_monto"] = $cellValue6;
				$variable["created_at"] = date("Y-m-d H:i:s");
				$variable["in_activo"] = 'TRUE';
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

		break;
	case 99:

	$codigo = decode($_POST['co_proyecto_acc_espec']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="proyecto_seguimiento.tab_proyecto_ae";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["descripcion"] = decode($_POST['nb_accion']);
			$variable["id_tab_unidad_medida"] = decode($_POST['co_unidades_medida']);
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
			$variable["updated_at"] = date("Y-m-d H:i:s");
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
			$tabla="proyecto_seguimiento.tab_proyecto_ae";
			$primaryKey="id";
			$variable["id_tab_proyecto"] = decode($_POST['id_proyecto']);
			$variable["descripcion"] = decode($_POST['nb_accion']);
			$variable["id_tab_unidad_medida"] = decode($_POST['co_unidades_medida']);
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
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = 'TRUE';
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

		break;
	default:
	}

