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

	$sql = "SELECT t39.*, nu_numero, de_nombre, de_unidad_medida, tx_ejecutor, ac_seguimiento.sp_ac_ae_meta_cargado(t39.id) as mo_cargado FROM ac_seguimiento.tab_ac_ae as t39
	inner join mantenimiento.tab_ac_ae_predefinida as t52 on t39.id_tab_ac_ae_predefinida = t52.id
	inner join mantenimiento.tab_unidad_medida as t21 on t39.id_tab_unidad_medida=t21.id
	inner join mantenimiento.tab_ejecutores as t24 on t39.id_tab_ejecutores=t24.id_ejecutor
	WHERE id_tab_ac ='".$_POST['id_proyecto']."' AND t39.in_activo is true";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 5: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t39.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_proyecto_acc_espec"     => trim($row["id"]),
		    "id_proyecto"     => trim($row["id_tab_ac"]),
		    "tx_codigo"     => trim($row["nu_numero"]),
		    "descripcion"     => trim($row["de_nombre"]),
		    "co_unidades_medida"     => trim($row["meta"].' '.$row["de_unidad_medida"]),
		    "total"     => trim($row["mo_ae_calculado"]),
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

		break;
	case 2:

	$sql = "SELECT id_tab_meta_fisica, co_partida
  		FROM ac_seguimiento.tab_meta_financiera 
		WHERE id_tab_meta_fisica='".$_POST['id_tab_meta_fisica']."' AND in_activo is true 
		group by 1,2;";

	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"id_tab_meta_fisica"		=> $row["id_tab_meta_fisica"],
			"co_partida"	=> trim($row["co_partida"]), 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 3:

	$sql = "SELECT t67.*, de_unidad_medida, ac_seguimiento.sp_ac_ae_mo_metafin(t67.id) AS mo_cargado 
	FROM ac_seguimiento.tab_meta_fisica as t67
	inner join mantenimiento.tab_unidad_medida as t21 on t67.id_tab_unidad_medida=t21.id
	WHERE id_tab_ac_ae='".$_POST['co_proyecto_acc_espec']."' and t67.in_activo is true ";

	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" and nb_meta ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t67.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_metas"     => trim($row["id"]),
		    "co_proyecto_acc_espec"     => trim($row["id_tab_ac_ae"]),
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

		break;
	case 4:

	$sql = "SELECT * FROM ac_seguimiento.tab_meta_seguimiento
	WHERE id_tab_meta_fisica='".$_POST['id_tab_meta_fisica']."' and in_activo is true ";

	if($_POST['BuscarBy']=="true"){
		if($_POST['variable']!=""){$sql.=" and tx_observacion ILIKE '%".$_POST['variable']."%'";}
	}

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "id"     => trim($row["id"]),
		    "id_tab_meta_fisica"     => trim($row["id_tab_meta_fisica"]),
		    "fe_inicio"     => trim(date_format(date_create($row["fe_inicio"]),'d/m/Y')),
		    "fe_fin"     => trim(date_format(date_create($row["fe_fin"]),'d/m/Y')),
		    "mo_presupuesto"     => trim($row["mo_presupuesto"]),
		    "nu_ponderacion"     => trim($row["nu_ponderacion"]),
		    "nu_partida"     => trim($row["nu_partida"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 5:

	$codigo = decode($_POST['id_tab_meta_seguimiento']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="ac_seguimiento.tab_meta_seguimiento";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["nu_partida"] = decode($_POST['co_partida']);
			$variable["mo_presupuesto"] = decode($_POST['mo_presupuesto']);
			$variable["nu_ponderacion"] = decode($_POST['nu_ponderacion']);
			list($dia, $mes, $anio) = explode("/",$_POST['fe_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fe_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fe_fin']);
			$fecha_fin = $anio."-".$mes."-".$dia;
			$variable["fe_fin"] = $fecha_fin;
			$variable["tx_observacion"] = decode($_POST['tx_observacion']);
			$variable["nu_lapso"] = decode($_POST['nu_lapso']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$id_tab_meta_seguimiento = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			$array = '';
			if(array_key_exists("adjunto", $_FILES)){
				foreach ($_FILES["adjunto"] as $id => $value){
					$array = $value;
				}
				foreach ($array as $key => $valor)
				{
					if($_FILES["adjunto"]["tmp_name"][$key]!='')
					{
						# Variables del archivo
						$type = $_FILES["adjunto"]["type"][$key];
						$tmp_name = $_FILES["adjunto"]["tmp_name"][$key];
						$size = $_FILES["adjunto"]["size"][$key];
						$nombre = $_FILES["adjunto"]["name"][$key];
						$nombre = basename($_FILES["adjunto"]["name"][$key]);
						# Contenido del archivo
						$fp = fopen($tmp_name, "rb");
						$buffer = fread($fp, filesize($tmp_name));
						fclose($fp);

						$tabla1="ac_seguimiento.tab_meta_imagen";
						$tquery1="INSERT";
						$variable1["id_tab_meta_seguimiento"] = $codigo;
						$variable1["im_meta"] = pg_escape_bytea($buffer);
						$variable1["mime_meta"] = $type;
						$variable1["nb_archivo_meta"] = $nombre;
						$variable1["created_at"] = date("Y-m-d H:i:s");
						$variable1["in_activo"] = 'TRUE';
						$id_tab_meta_imagen = $comunes->InsertUpdate($tabla1,$variable1,$tquery1);
					}
				}
			}

			if ($id_tab_meta_seguimiento){
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
			$tabla="ac_seguimiento.tab_meta_seguimiento";
			$primaryKey="id";
			$variable["id_tab_meta_fisica"] = decode($_POST['id_tab_meta_fisica']);
			$variable["nu_partida"] = decode($_POST['co_partida']);
			$variable["mo_presupuesto"] = decode($_POST['mo_presupuesto']);
			$variable["nu_ponderacion"] = decode($_POST['nu_ponderacion']);
			list($dia, $mes, $anio) = explode("/",$_POST['fe_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fe_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fe_fin']);
			$fecha_fin = $anio."-".$mes."-".$dia;
			$variable["fe_fin"] = $fecha_fin;
			$variable["tx_observacion"] = decode($_POST['tx_observacion']);
			$variable["nu_lapso"] = decode($_POST['nu_lapso']);
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = 'TRUE';
			$id_tab_meta_seguimiento= $comunes->InsertConID($tabla,$variable,$primaryKey);

			$array = '';
			if(array_key_exists("adjunto", $_FILES)){
				foreach ($_FILES["adjunto"] as $id => $value){
					$array = $value;
				}
				foreach ($array as $key => $valor)
				{
					if($_FILES["adjunto"]["tmp_name"][$key]!='')
					{
						# Variables del archivo
						$type = $_FILES["adjunto"]["type"][$key];
						$tmp_name = $_FILES["adjunto"]["tmp_name"][$key];
						$size = $_FILES["adjunto"]["size"][$key];
						$nombre = $_FILES["adjunto"]["name"][$key];
						$nombre = basename($_FILES["adjunto"]["name"][$key]);
						# Contenido del archivo
						$fp = fopen($tmp_name, "rb");
						$buffer = fread($fp, filesize($tmp_name));
						fclose($fp);

						$tabla1="ac_seguimiento.tab_meta_imagen";
						$tquery1="INSERT";
						$variable1["id_tab_meta_seguimiento"] = $id_tab_meta_seguimiento;
						$variable1["im_meta"] = pg_escape_bytea($buffer);
						$variable1["mime_meta"] = $type;
						$variable1["nb_archivo_meta"] = $nombre;
						$variable1["created_at"] = date("Y-m-d H:i:s");
						$variable1["in_activo"] = 'TRUE';
						$id_tab_meta_imagen = $comunes->InsertUpdate($tabla1,$variable1,$tquery1);
					}
				}
			}

			if ($id_tab_meta_seguimiento){
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

	$sql = "SELECT id FROM ac_seguimiento.tab_meta_imagen
	WHERE id_tab_meta_seguimiento='".$_POST['id_tab_meta_seguimiento']."' and in_activo is true ";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 1: $_POST["limit"];
	$sql.= " ORDER BY id ASC LIMIT ".$limit." OFFSET ".$start;

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_img_avance"     => trim($row["id"])
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 7:

	$codigo = decode($_POST['co_img_avance']);

	$sql = "DELETE FROM ac_seguimiento.tab_meta_imagen WHERE id =".$codigo.";";
	$co_metas = $comunes->EjecutarQuery($sql);

	echo json_encode(array(
		"success" => true,
		"msg" => 'Registro Borrado con Exito!'
	));

		break;
	default:
	}

