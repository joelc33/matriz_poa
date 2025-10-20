<?php
require_once '../../comun.php';

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions as ve;
use Reingsys as re;

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

try {

function obtener_paginar($f, $l) {
	$start = intval( $f['start'] );
	$limit = intval( $f['limit'] );
	if ( $limit === 0 ) {
		$limit = 20;
	}
	return array($start, $limit);
}

$op = intval($_POST['op']);

switch ($op) {

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
	case 5:

	$co_proyecto_acc_espec = decode($_POST['co_proyecto_acc_espec']);

	$sqlAcc = "SELECT mo_ae_calculado as total FROM ac_seguimiento.tab_ac_ae where id=".$co_proyecto_acc_espec;
	$resultado = $comunes->ObtenerFilasBySqlSelect($sqlAcc);
	$resultadoReal = $resultado[0]['total'];
	$codigo = decode($_POST['co_metas']);
	if($codigo!=''||$codigo!=null){
		try{
			$paraTransaccion->BeginTrans();
			$tabla="ac_seguimiento.tab_meta_fisica";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["nb_meta"] = decode($_POST['nb_actividad']);
			$variable["id_tab_unidad_medida"] = decode($_POST['co_unidades_medida']);
			$variable["tx_prog_anual"] = decode($_POST['pr_anual']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fecha_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fecha_fin"] = $fecha_culminacion;
			$variable["nb_responsable"] = decode($_POST['nb_responsable']);
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$co_metas = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			$sql4 = "UPDATE ac_seguimiento.tab_meta_financiera SET in_activo = false, updated_at = '".date("Y-m-d H:i:s")."' WHERE id_tab_meta_fisica = '$codigo' and in_activo is true;";
			$comunes->EjecutarQuery($sql4);

			$detalle = json_decode($_POST['json_detalle'],true); 

			$mo_validar = 0;
			foreach ($detalle as $lista){
				$mo_validar = $mo_validar+$lista['mo_presupuesto'];
				$tabla1="ac_seguimiento.tab_meta_financiera";
				$primaryKey1="id";
				$variable1["id_tab_meta_fisica"] = decode($codigo);
				$variable1["id_tab_municipio_detalle"] = decode($lista['co_municipio']);
				$variable1["id_tab_parroquia_detalle"] = decode($lista['co_parroquia']);
				$variable1["mo_presupuesto"] = decode($lista['mo_presupuesto']);
				$variable1["co_partida"] = decode($lista['co_partida']);
				$variable1["id_tab_fuente_financiamiento"] = decode($lista['co_fuente_financiamiento']);
				$variable1["created_at"] = date("Y-m-d H:i:s");
				$variable1["in_activo"] = 'TRUE';
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
			$tabla="ac_seguimiento.tab_meta_fisica";
			$primaryKey="id";
			$variable["id_tab_ac_ae"] = decode($co_proyecto_acc_espec);
			$variable["nb_meta"] = decode($_POST['nb_actividad']);
			$variable["id_tab_unidad_medida"] = decode($_POST['co_unidades_medida']);
			$variable["tx_prog_anual"] = decode($_POST['pr_anual']);
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_inicio']);
			$fecha_inicio = $anio."-".$mes."-".$dia;
			$variable["fecha_inicio"] = $fecha_inicio;
			list($dia, $mes, $anio) = explode("/",$_POST['fecha_culminacion']);
			$fecha_culminacion = $anio."-".$mes."-".$dia;
			$variable["fecha_fin"] = $fecha_culminacion;
			$variable["nb_responsable"] = decode($_POST['nb_responsable']);
			$variable["created_at"] = date("Y-m-d H:i:s");
			$variable["in_activo"] = 'TRUE';
			$co_metas = $comunes->InsertConID($tabla,$variable,$primaryKey);

			$detalle = json_decode($_POST['json_detalle'],true); 

			$mo_validar = 0;
			foreach ($detalle as $lista){
				$mo_validar = $mo_validar+$lista['mo_presupuesto'];
				$tabla1="ac_seguimiento.tab_meta_financiera";
				$primaryKey1="id";
				$variable1["id_tab_meta_fisica"] = decode($co_metas);
				$variable1["id_tab_municipio_detalle"] = decode($lista['co_municipio']);
				$variable1["id_tab_parroquia_detalle"] = decode($lista['co_parroquia']);
				$variable1["mo_presupuesto"] = decode($lista['mo_presupuesto']);
				$variable1["co_partida"] = decode($lista['co_partida']);
				$variable1["id_tab_fuente_financiamiento"] = decode($lista['co_fuente_financiamiento']);
				$variable1["created_at"] = date("Y-m-d H:i:s");
				$variable1["in_activo"] = 'TRUE';
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

		break;
	case 6:

	$sql = "SELECT t68.*, de_municipio, de_parroquia, de_fuente_financiamiento FROM ac_seguimiento.tab_meta_financiera as t68
	left join mantenimiento.tab_municipio_detalle as t64 on t68.id_tab_municipio_detalle=t64.id
	left join mantenimiento.tab_parroquia_detalle as t65 on t68.id_tab_parroquia_detalle=t65.id
	inner join mantenimiento.tab_fuente_financiamiento as t66 on t68.id_tab_fuente_financiamiento=t66.id
	WHERE id_tab_meta_fisica='".$_POST['co_metas']."' AND t68.in_activo is true ";

	$cantidadTotal = $comunes->getFilas($sql);

	$start = ($_POST["start"] == null)? 0 : $_POST["start"];
	$limit = ($_POST["limit"] == null)? 20: $_POST["limit"];
	if($_POST['paginar']=='si'){$sql.= " ORDER BY t68.id ASC LIMIT ".$limit." OFFSET ".$start;}

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	$data= array();
	foreach($result as $key => $row){
		$data[] = array(
		    "co_metas_detalle"     => trim($row["id"]),
		    "co_metas"     => trim($row["id_tab_meta_fisica"]),
		    "co_municipio"     => trim($row["id_tab_municipio_detalle"]),
		    "tx_municipio"     => trim($row["de_municipio"]),
		    "co_parroquia"     => trim($row["id_tab_parroquia_detalle"]),
		    "tx_parroquia"     => trim($row["de_parroquia"]),
		    "mo_presupuesto"     => trim($row["mo_presupuesto"]),
		    "co_partida"     => trim($row["co_partida"]),
		    "co_fuente_financiamiento"     => trim($row["id_tab_fuente_financiamiento"]),
		    "tx_fuente_financiamiento"     => trim($row["de_fuente_financiamiento"]),
		);
	}
	echo json_encode(array(
		"success"   =>  true,
		"total"     =>  $cantidadTotal,
		"data"      =>  $data
	));

		break;
	case 7:

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
	case 8:

	$sql = "SELECT * FROM mantenimiento.tab_municipio_detalle where id_tab_estado=".$_POST['co_estado']." ORDER BY id ASC;";
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
	case 9:

	$sql = "SELECT * FROM mantenimiento.tab_parroquia_detalle where id_tab_municipio_detalle=".$_POST['co_municipio']." ORDER BY id ASC;";
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
	case 10:

	$sql = "SELECT * FROM mantenimiento.tab_fuente_financiamiento WHERE in_activo is true;";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"co_fuente_financiamiento" => $row["id"],
			"tx_fuente_financiamiento" => $row["de_fuente_financiamiento"], 
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 11:

	$sql = "SELECT t1.id_tab_ac, t1.id_tab_ac_ae_predefinida, left(co_partida, 3) as co_partida
  	FROM ac_seguimiento.tab_ac_ae_partida as t1
	inner join ac_seguimiento.tab_ac_ae as t2 on t1.id_tab_ac_ae_predefinida = t2.id_tab_ac_ae_predefinida
	WHERE t1.id_tab_ac='".$_POST['id_proyecto']."' AND t2.id='".$_POST['id_tab_ac_ae']."'
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

		break;
	case 12:

	$codigo = decode($_POST['co_metas']);
	try{
		$paraTransaccion->BeginTrans();
		$tabla="ac_seguimiento.tab_meta_fisica";
		$tquery="UPDATE";
		$id = 'id = '.$codigo;
		$variable["in_activo"] = 'FALSE';
		$variable["updated_at"] = date("Y-m-d H:i:s");
		$co_metas = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

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

		break;
	case 30: //actualizar distribucion
			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id'
			) );

			$clave = v::key( 'id', v::string()->notEmpty() );
			$clave->assert( $pk );

			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'data',
			) );
			$json = v::key( 'data', v::json()->notEmpty() );
			$json->assert( $params );
			$acciones = json_decode( $params['data'] );

			$contenido = v::object()->attribute( 'id', v::int()->positive()->notEmpty() );
			foreach ( range(1,12) as $i ) {
				$contenido = $contenido->attribute( "$i", v::int()->min( 0, true ), false );
			}
			if ( !is_array( $acciones ) ) {
				$acciones = array( $acciones );
			}

			$reglas = v::arr()->each( $contenido );
			$reglas->assert( $acciones );

			if ( $_GET['ae_dist'] == 3 ) {
				$sql = <<<EOT
update only ac_seguimiento.tab_ac_ae_distfin as t51
set monto = ?, updated_at = NOW()
from ac_seguimiento.tab_ac_ae as t53
where t51.id_tab_ac_ae = t53.id
and t51.mes = ? and t51.id_tab_ac_ae = ?;
EOT;
			} else {
				$sql = <<<EOT
update only ac_seguimiento.tab_ac_ae_disfisica as t55
set monto = ?, updated_at = NOW()
from ac_seguimiento.tab_ac_ae as t53
where t55.id_tab_ac_ae = t53.id
and t55.mes = ? and t55.id_tab_ac_ae = ?;
EOT;
			}

			$paraTransaccion->StartTrans();

			foreach( $acciones as $ac ) {
				$id_ae = $ac->{'id'};
				foreach( $ac as $k => $v ) {
					if( preg_match( '/^\d+$/', $k ) === 1 ) {
						$res = $comunes->EjecutarQuery(
							$sql, array( $v, $k, $id_ae )
						);
					}
				}
			}
			$res = $paraTransaccion->CompleteTrans();
			$respuesta = re\Helpers::responder( $res );
		break;
	default:
		$respuesta = re\Helpers::responder( false, 'Operación desconocida' );
	break;
}
} catch( ve\ValidationException $e ) {
	//sólo con assert
	error_log( json_encode( $e->getFullMessage() ) );
	$respuesta = re\Helpers::responder( false, 'Parámetros inválidos' );
} catch ( \ADODB_Exception $e ) {
	error_log( json_encode( re\Helpers::jTraceEx( $e ) ) );
	//FIXME feo
	$mensaje = 'ocurrió una falla trabajando con la base de datos';
	if ( $paraTransaccion->HasFailedTrans() ) {
		$paraTransaccion->CompleteTrans();
	}
	$ms = array();
	if ( preg_match( '/ERROR\:\ *(.*)\s*CONTEXT\:/', $e->getMessage(), $ms ) === 1 ) {
		$mensaje = $ms[1];
	}
	$respuesta = re\Helpers::responder( false,
		'Error en Transacción: ' . $mensaje
	);
} catch( \Exception $e ) {
	error_log( json_encode( re\Helpers::jTraceEx( $e ) ) );
	$respuesta = re\Helpers::responder( false,
		'Error procesando la solicitud'
	);
}

echo $respuesta;
