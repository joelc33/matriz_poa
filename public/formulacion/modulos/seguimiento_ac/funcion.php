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

	$co_usuario = $_SESSION['co_usuario'];
	$co_rol = $_SESSION['co_rol'];
	$local = $co_rol > 2;
	$params = array();
    $selParams = array();

	$cuenta = 'select count(*) as c ';
	$select = <<<EOT
select t46.id, t52.de_nombre, tx_ejecutor, mo_ac, de_estatus,
coalesce(mo_calculado, 0) as monto_calc, id_tab_tipo_registro,
coalesce(?, t46.id_tab_estatus = 3) as reabrir,
coalesce(?, t46.id_tab_estatus = 1) as eliminar,
nu_codigo as codigo
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
 from ac_seguimiento.tab_ac as t46
	join mantenimiento.tab_ac_predefinida as t52 on t52.id = t46.id_tab_ac_predefinida
	join mantenimiento.tab_ejecutores as t24 on t24.id_ejecutor = t46.id_tab_ejecutores
	join mantenimiento.tab_estatus as t31 on t46.id_tab_estatus = t31.id
where t46.in_activo is true and t46.id_tab_ejercicio_fiscal = ?
EOT;
	}

	$order = ' ORDER BY t46.id_tab_ejecutores, id_tab_ac_predefinida ASC';

	$params[] = $_SESSION['ejercicio_fiscal'];

	if($_POST['BuscarBy'] == 'true' ){
		if( !empty( $_POST['variable'] ) ){
			$cuerpo .= <<<EOT
 and t52.de_nombre ILIKE '%' || ? || '%' or t24.tx_ejecutor ILIKE '%' || ? || '%'
or ('AC' || t24.id_ejecutor || id_tab_ejercicio_fiscal || lpad(id_tab_ac_predefinida::text, 5, '0'))
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

		$params[] = ( $_POST['limit'] == null ) ? 10 : intval( $_POST['limit'] );
		$params[] = ( $_POST['start'] == null ) ? 0 : intval( $_POST['start'] );
	}

	$result = $comunes->ObtenerFilasBySqlSelect(
		$select.$cuerpo.$order, array_merge( $selParams, $params )
	);

	$data = array();
	foreach( $result as $key => $row ) {
		$data[] = array(
			'id' => $row['codigo'],
			'codigo' => $row['codigo'],
			'nombre' => trim( $row['de_nombre'] ),
			'tx_ejecutor' => trim( $row['tx_ejecutor'] ),
			'monto' => $row['mo_ac'],
			'monto_calc' => $row['monto_calc'],
			'tx_estatus' => trim( $row['de_estatus'] ),
			'eliminar' => ( $row['eliminar'] == 't' ),
			'id_tab_tipo_registro' => ( $row['id_tab_tipo_registro'] ),
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
	case 2:
        $codigo = $_POST['codigo'];
            
        $id_tab_lapso = $_POST['id_tab_lapso'];
            
        if ($codigo!=''||$codigo!=null) {              

	$res = $comunes->ObtenerFilasBySqlSelect("select id, de_nombre, de_accion from mantenimiento.tab_ac_predefinida WHERE in_activo is true order by id;");
        }else{
            
        $res = $comunes->ObtenerFilasBySqlSelect("select id, de_nombre, de_accion from mantenimiento.tab_ac_predefinida WHERE in_activo is true and id not in (select id_tab_ac_predefinida from ac_seguimiento.tab_ac where id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." and in_activo = true and id_tab_lapso = ".$id_tab_lapso." and id_ejecutor = '".$_POST['id_ejecutor']."') order by id;");
             
        }
	if ($res) {
		$respuesta = re\Helpers::responder( true, null, array( 'data' => $res ) );
	} else {
		$respuesta = re\Helpers::responder( false, 'id no existe' );
	}

		break;
	case 3:

	$sql = "SELECT id_ejecutor,tx_ejecutor, (select inst_mision 
from public.t46_acciones_centralizadas 
where id_ejecutor = t1.id_ejecutor and id_ejercicio = ".$_SESSION['ejercicio_fiscal']." order by id desc limit 1 ), (select inst_vision 
from public.t46_acciones_centralizadas 
where id_ejecutor = t1.id_ejecutor and id_ejercicio = ".$_SESSION['ejercicio_fiscal']." order by id desc limit 1 ) , (select inst_objetivos 
from public.t46_acciones_centralizadas 
where id_ejecutor = t1.id_ejecutor and id_ejercicio = ".$_SESSION['ejercicio_fiscal']." order by id desc limit 1 )  
FROM mantenimiento.tab_ejecutores t1
WHERE in_activo is true order by id_ejecutor asc;";       
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$data= array();
	foreach($result as $key => $row){
		array_push($data,array(
			"id_ejecutor"	=> $row["id_ejecutor"], 
			"tx_ejecutor"	=> $row["tx_ejecutor"],
                        "inst_mision"	=> $row["inst_mision"],
                        "inst_vision"	=> $row["inst_vision"],
                        "inst_objetivos" => $row["inst_objetivos"],
		));
	}
	echo json_encode(
		array(  
		"success"	=> true,
		"data"		=> $data
	));

		break;
	case 4:

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
	case 5:

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
	case 6:

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
	case 7: //consultar vinculos

			$id_accion = $_REQUEST['id'];
                        $id_ejecutor = $_REQUEST['id_ejecutor'];
			if ($id_accion!=''||$id_accion!=null) {
				$sql = <<<EOT

SELECT id,
trim(co_objetivo_historico) as co_objetivo_historico,
trim(co_objetivo_nacional) as co_objetivo_nacional,
trim(co_objetivo_estrategico) as co_objetivo_estrategico,
trim(co_objetivo_general) as co_objetivo_general,
co_area_estrategica,
co_macroproblema, co_nodos as co_nodo, co_ambito_estado as co_ambito_zulia,
co_objetivo_estado as co_objetivo_zulia,true as actualizar
FROM ac_seguimiento.tab_ac_vinculo
WHERE id_tab_ac = ?
LIMIT 1;
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_accion ) );
				if ( $res ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res[0] ) );
				} else {
                                    
       if ($id_ejecutor!=''||$id_ejecutor!=null) {
     
           				$sql = <<<EOT
SELECT id_accion_centralizada,
trim(co_objetivo_historico) as co_objetivo_historico,
trim(co_objetivo_nacional) as co_objetivo_nacional,
trim(co_objetivo_estrategico) as co_objetivo_estrategico,
trim(co_objetivo_general) as co_objetivo_general,
co_area_estrategica,
co_macroproblema, co_nodos as co_nodo, co_ambito_estado as co_ambito_zulia,
co_objetivo_estado as co_objetivo_zulia,false as actualizar
FROM t49_ac_planes t49
join t46_acciones_centralizadas t46 on (t46.id = t49.id_accion_centralizada)
WHERE id_ejecutor = ? and id_ejercicio = ?
order by 1 asc LIMIT 1;
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_ejecutor,$_SESSION['ejercicio_fiscal'] ) );
           				if ( $res ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res[0] ) );
				} else {
                                $respuesta = re\Helpers::responder( true, null, array( 'data' => null ) );    
                                }
       }else{
       $respuesta = re\Helpers::responder( true, null, array( 'data' => null ) );    
       }                             
                                    
					
				}
			} else {
				$respuesta = re\Helpers::responder( false, 'id?' );
			}

		break;
	case 8:

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
	case 9:

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
	case 10:

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
	case 11:

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
	case 12:

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
	case 13:

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
	case 14:

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
	case 15:

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
	case 16:

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
	case 17: //actualizar vinculos

			$actualizar = ( array_key_exists( 'up', $_POST ) and $_POST['up'] === 't' );
			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id_tab_ac',
                                'co_objetivo_historico',
				'co_objetivo_nacional',
				'co_objetivo_estrategico',
				'co_objetivo_general',
				'co_area_estrategica',
				'co_ambito_zulia' => 'co_ambito_estado',
				'co_objetivo_zulia' => 'co_objetivo_estado',
				'co_macroproblema',
				'co_nodo' => 'co_nodos',
			) );
			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id'
			) );

			$clave = v::key( 'id', v::intero()->notEmpty() );

//			$reglas = v::key( 'co_nodos', v::arr()->notEmpty()->each( v::int()->positive() ) );
			foreach( array(
				'co_objetivo_historico',
				'co_objetivo_nacional',
				'co_objetivo_estrategico',
				'co_objetivo_general',
				'co_area_estrategica',
				'co_ambito_estado',
				'co_objetivo_estado',
				'co_macroproblema'
			) as $campo ) {
//				$reglas = $reglas->key( $campo, v::intero()->notEmpty() );
			}

			$clave->assert( $pk );
//			$reglas->assert( $params );
//			$params['co_nodos'] = implode (',', $params['co_nodos'] );
			$params['updated_at'] = date( \DateTime::ISO8601 );
			$tabla = 'ac_seguimiento.tab_ac_vinculo';
			if ( $actualizar ) {
                            

				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'UPDATE',
					"id_tab_ac = '{$pk['id']}'"
				);
			} else {
				$params['id_accion_centralizada'] = $pk['id'];
				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'INSERT'
				);
			}

			if ( $resultado === 'Ok' ) {
				$respuesta = re\Helpers::responder( true );
			} else {
				$respuesta = re\Helpers::responder( false,
					'Error almacenando los datos'
				);
			}

		break;
	case 18:

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
	case 19:

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
	case 20://consultar localidades

			$id_accion = $_REQUEST['id'];
                        $id_ejecutor = $_REQUEST['id_ejecutor'];
			if ($id_accion!=''||$id_accion!=null) {
				$sql = <<<EOT
SELECT de_municipio as tx_municipio, t50.id_tab_municipio as co_municipio, de_parroquia as tx_parroquia, t50.id_tab_parroquia as co_parroquia
FROM ac_seguimiento.tab_ac_localizacion as t50
JOIN mantenimiento.tab_municipio as t13 on t13.id = t50.id_tab_municipio
LEFT JOIN mantenimiento.tab_parroquia as t14 on t14.id = t50.id_tab_parroquia
WHERE id_tab_ac = ?
ORDER BY t50.id_tab_municipio, t50.id_tab_parroquia NULLS FIRST
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_accion ) );
				if ( $res ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res ) );
				} else {
			if ($id_ejecutor!=''||$id_ejecutor!=null) {
				$sql = <<<EOT
SELECT distinct tx_municipio, t50.co_municipio, tx_parroquia, t50.co_parroquia
FROM t50_ac_localizacion as t50
join t46_acciones_centralizadas t46 on (t46.id = t50.id_accion_centralizada)
JOIN t13_municipio as t13 on t13.co_municipio = t50.co_municipio
LEFT JOIN t14_parroquia as t14 on t14.co_parroquia = t50.co_parroquia
WHERE id_ejecutor = ? and id_ejercicio = ?
ORDER BY t50.co_municipio, t50.co_parroquia NULLS FIRST
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_ejecutor,$_SESSION['ejercicio_fiscal'] ) );
				if ( $res ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res ) );
				} else {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => null ) );
				}
			}else{
                            $respuesta = re\Helpers::responder( true, null, array( 'data' => null ) );
                        }
				}
			} else {
				$respuesta = re\Helpers::responder( false, 'id?' );
			}

		break;
	case 21://actualizar localidades

			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id'
			) );
                        $clave = v::key( 'id', v::intero()->notEmpty() );
			$clave->assert( $pk );

			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'localidades',
			) );
			$json = v::key( 'localidades', v::json()->notEmpty() );
			$json->assert( $params );
			$localidades = json_decode( $params['localidades'] );

//			$reglas = v::arr()->each(
//				v::object()->attribute( 'co_municipio', v::int()->positive()->notEmpty() )
//					->attribute( 'co_parroquia', v::int()->positive(), false )
//			);
//			$reglas->assert( $localidades );

			$paraTransaccion->StartTrans();

			$res = $comunes->EjecutarQuery(
				'delete from ac_seguimiento.tab_ac_localizacion where id_tab_ac = ?;',
				array( $pk['id'] )
			);

			foreach ( $localidades as $lo ) {
				$mun = $lo->co_municipio;
				$par = $lo->co_parroquia ? $lo->co_parroquia : null;
				$estatus = 'TRUE';
				$fecha = date( \DateTime::ISO8601 );
				$res = $comunes->EjecutarQuery(<<<EOT
insert into ac_seguimiento.tab_ac_localizacion(id_tab_ac, id_tab_municipio, id_tab_parroquia, in_activo, created_at)
values(?,?,?,?,?);
EOT
				, array( $pk['id'], $mun, $par, $estatus, $fecha ) );
			}
			$paraTransaccion->CompleteTrans();
			$respuesta = re\Helpers::responder( true );

		break;
	case 22: //consulta responsables

			$respuesta = re\Helpers::responder( true, null, array( 'data' => null ) );
			$id_accion = $_REQUEST['id'];
                        $id_ejecutor = $_REQUEST['id_ejecutor'];
			if ($id_accion!=''||$id_accion!=null) {
				$sql = <<<EOT
SELECT id_tab_ac, realizador_nombres, realizador_cedula,
realizador_cargo, realizador_correo, realizador_telefono, realizador_unidad,
registrador_nombres, registrador_cedula, registrador_cargo, registrador_correo,
registrador_telefono, registrador_unidad, autorizador_nombres,
autorizador_cedula, autorizador_cargo, autorizador_correo,
autorizador_telefono, autorizador_unidad,true as actualizar
FROM ac_seguimiento.tab_ac_responsable
WHERE id_tab_ac = ?
LIMIT 1;
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_accion ) );
				if ( ! empty($res) ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res[0] ) );
				}else{
 			if ($id_ejecutor!=''||$id_ejecutor!=null) {
				$sql = <<<EOT
SELECT id_accion_centralizada, realizador_nombres, realizador_cedula,
realizador_cargo, realizador_correo, realizador_telefono, realizador_unidad,
registrador_nombres, registrador_cedula, registrador_cargo, registrador_correo,
registrador_telefono, registrador_unidad, autorizador_nombres,
autorizador_cedula, autorizador_cargo, autorizador_correo,
autorizador_telefono, autorizador_unidad,false as actualizar
FROM t48_ac_responsables t48
join t46_acciones_centralizadas t46 on (t46.id = t48.id_accion_centralizada)
WHERE id_ejecutor = ? and id_ejercicio = ?
order by 1 asc LIMIT 1;
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_ejecutor,$_SESSION['ejercicio_fiscal'] ) );
				if ( ! empty($res) ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res[0] ) );
				}
			}                                    
                                }
			}

		break;
	case 23: //insertar/actualizar responsables

			$actualizar = ( array_key_exists( 'up', $_POST ) and $_POST['up'] === 't' );

			$tipos = array( 'realizador', 'registrador', 'autorizador' );
			$datos = array(
				'nombres' => v::stringcadena()->length( 4, 80)->notEmpty(),
				'cedula' => v::regex( '/^[VvEe](\-)?(\d{4,8})$/' ),
				'cargo' => v::stringcadena()->length( 4, 50)->notEmpty(),
				'correo' => v::regex( '/^(\w+)([\-+.\'][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6}$/' )->notEmpty(),
				'telefono' => v::regex( '/^((((\+)(\d{2})|(\d{2}))(\-)?)(\d{4}(\-)?)|(\d{4}(\-)?))?(\d{7})$/' )->notEmpty(),
				'unidad' => v::stringcadena()->length( 3, 50)->notEmpty()
			);

			$campos = array();
			foreach( $tipos as $t ) {
				foreach( $datos as $d => $e ) {
					$campos[] = "{$t}_{$d}";
				}
			}
                        
                
			$params = re\Helpers::obtener_pertinentes( $_POST, $campos );

			$cadena = null;
			foreach( $campos as $c ) {
				$i = explode( '_', $c )[1];
				$v = $datos[ $i ];
				if ( is_null( $cadena ) ) {
					$cadena = v::key( $c, $v );
				} else {
					$cadena->key( $c, $v );
				}
			}


			$pk = re\Helpers::obtener_pertinentes( $_POST, array( 'id_accion_centralizada' ) );
			$params = array_merge( $params, $pk );
			$params['updated_at'] = date( \DateTime::ISO8601 );
			$cadena->key( 'id_accion_centralizada', v::intero()->notEmpty() );
                        


			$cadena->assert( $params );
			$tabla = 'ac_seguimiento.tab_ac_responsable';
			if ( $actualizar ) {
				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'UPDATE',
					"id_tab_ac = '{$pk['id_accion_centralizada']}'"
				);
			} else {
                            $params['id_tab_ac'] = $pk['id_accion_centralizada'];
                            
				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'INSERT'
				);
			}

			if ( $resultado === 'Ok' ) {
				$respuesta = re\Helpers::responder( true );
			} else {
				$respuesta = re\Helpers::responder( false,
					'Error almacenando los datos'
				);
			}

		break;
	case 24: //listado de acciones especificas

			$id_accion = $_REQUEST['id'];
			if ($id_accion!=''||$id_accion!=null) {
				$cuenta = <<<EOT
SELECT count(*) as c 
FROM ac_seguimiento.tab_ac_ae as t47
WHERE id_tab_ac = ? and t47.in_activo is true
EOT;
				$sql = <<<EOT
SELECT t47.id, id_tab_ac_ae_predefinida as id_accion, t53.nu_numero as numero, t53.de_nombre as nombre, bien_servicio, t47.mo_ae as monto,
	t47.mo_ae_calculado as monto_calc, to_char(fecha_inicio, 'DD/MM/YYYY') as fecha_inicio, to_char(fecha_fin, 'DD/MM/YYYY') as fecha_fin, t47.id_ejecutor as id_ejecutor, t24.tx_ejecutor,
	id_tab_unidad_medida as id_unidad_medida, de_unidad_medida, meta, objetivo_institucional,
	count(t54.id_tab_ac_ae) as npartidas,t47.id_tab_origen
FROM ac_seguimiento.tab_ac_ae as t47
	JOIN mantenimiento.tab_ejecutores as t24 on t47.id_ejecutor = t24.id_ejecutor
	JOIN mantenimiento.tab_ac_ae_predefinida as t53 on t53.id = t47.id_tab_ac_ae_predefinida
	JOIN mantenimiento.tab_unidad_medida as t21 on t21.id = t47.id_tab_unidad_medida
	LEFT JOIN ac_seguimiento.tab_ac_ae_partida as t54 on t54.id_tab_ac_ae = t47.id
WHERE t47.id_tab_ac = ? and t47.in_activo is true
GROUP BY t47.id, id_tab_ac_ae_predefinida, t53.nu_numero, t53.de_nombre, bien_servicio, t47.mo_ae,
	t47.mo_ae_calculado, fecha_inicio, fecha_fin, t47.id_tab_ejecutores, t24.tx_ejecutor,
	id_tab_unidad_medida, de_unidad_medida, meta, objetivo_institucional,t47.id_tab_origen
ORDER BY id_tab_ac_ae_predefinida LIMIT ? OFFSET ?;
EOT;
				$total = $comunes->ObtenerFilasBySqlSelect(
					$cuenta, array( $id_accion )
				)[0]['c'];

				list( $start, $limit ) = obtener_paginar( $_REQUEST, 20 );

				$res = $comunes->ObtenerFilasBySqlSelect(
					$sql, array( $id_accion, $limit, $start )
				);

				$respuesta = re\Helpers::responder( true, null,
					array(
						'data' => $res,
						'total' => $total
					)
				);
			}

		break;
	case 25: //consulta ae predefinidas

			$id_accion = intval( $_REQUEST['id_accion'] );
                        $ae = intval( $_REQUEST['ae'] );
			if ( $id_accion > 0 ) {
                            
                            if($ae > 0){
				$sql = 'select id, nu_numero as numero, de_nombre as nombre from mantenimiento.tab_ac_ae_predefinida where id_padre = ?;';
                            }else{
                             $sql = 'select id, nu_numero as numero, de_nombre as nombre from mantenimiento.tab_ac_ae_predefinida where id not in (select id_tab_ac_ae_predefinida from ac_seguimiento.tab_ac_ae where id_tab_ac = '.$_REQUEST['id_accion_centralizada'].' ) and id_padre = ?;';   
                            }
                                $res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_accion ) );
				if ( $res ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res ) );
				} else {
					$respuesta = re\Helpers::responder( false, 'id no existe' );
				}
			} else {
				$respuesta = re\Helpers::responder( false, 'id?' );
			}

		break;
	case 26: //consulta de unidad de medida

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
	case 27:

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
	case 28: //crear / actualizar acción específica
			$id = intval($_POST['id']);
			if($id!=''||$id!=null){

			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id_tab_ac',
				'id_accion' => 'id_tab_ac_ae_predefinida',
				'id' => 'id'
			));

			}else{

			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id_tab_ac',
				'id_accion' => 'id_tab_ac_ae_predefinida'
			));

			}
			$up = re\Helpers::obtener_pertinentes( $_POST, array(
				'up' => 'id_viejo'
			));
			$fondos = re\Helpers::obtener_pertinentes( $_POST, array(
				'fondos'
			));
                        
			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'bien_servicio',
				'objetivo_institucional',
				'monto' => 'mo_ae',
				'fondos',
				'id_unidad_medida' => 'id_tab_unidad_medida',
				'meta',
				'fecha_inicio',
				'fecha_fin'
			));

                        
			$primaria = v::key( 'id_tab_ac', v::intero()->notEmpty() )
				->key( 'id_tab_ac_ae_predefinida', v::intero()->notEmpty() );

			$actualiza = v::key( 'id_viejo', v::intero()->notEmpty() );

			$fechas = v::date( 'd-m-Y' )->notEmpty();
			$validador = v::key( 'id_tab_unidad_medida', v::intero()->positive()->notEmpty() )
				->key( 'mo_ae', v::numeric() )
				->key( 'meta', v::intero()->positive()->notEmpty() )
				->key( 'bien_servicio', v::stringcadena()->length( 3, 128 ) )
				->key( 'fecha_inicio',  $fechas )
				->key( 'fecha_fin', $fechas );

			$primaria->assert( $pk );
			$validador->assert( $params );

			$json = v::key( 'fondos', v::json()->notEmpty() );
			$json->assert( $fondos );
			$fondos = json_decode( $fondos['fondos'] );

//			$reglas = v::arr()->each(
//				v::object()->attribute( 'co_tipo_fondo', v::intero()->positive()->notEmpty() )
//					->attribute( 'monto', v::intero()->positive() )
//			);
                        
//			$reglas->assert( $fondos );
                        
                        
				$sql_ejecutor = <<<EOT
SELECT id_ejecutor,id_tab_ejecutores
FROM ac_seguimiento.tab_ac
WHERE id = ?;
EOT;
				$res_ejecutor = $comunes->ObtenerFilasBySqlSelect($sql_ejecutor, $pk['id_tab_ac']); 
                                $res_ejecutor = $res_ejecutor[0];                        
                        $params['id_tab_ejecutores'] = $res_ejecutor['id_tab_ejecutores'];
                        $params['id_ejecutor'] = $res_ejecutor['id_ejecutor'];
                        $params['mo_ae_calculado'] = $params['mo_ae'];
			$paraTransaccion->StartTrans();

			$llave = null;
			$tabla = 'ac_seguimiento.tab_ac_ae';
			if ( $actualiza->validate( $up ) ) {
				$params['id_tab_ac_ae_predefinida'] = $pk['id_tab_ac_ae_predefinida'];
				$params['updated_at'] = date( \DateTime::ISO8601 );
				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'UPDATE',
					"id = '{$pk['id']}'"
				);

				$llave = array(
					'id_tab_ac_ae' => $pk['id']
				);

				$comunes->EjecutarQuery(
					'delete from ac_seguimiento.tab_ac_ae_fuente where id_tab_ac_ae = ?;'
					, array( $llave['id_tab_ac_ae'] )
				);
			} else {
				$params = array_merge( $pk, $params );
				$params['in_activo'] = 'TRUE';
				$params['created_at'] = date( \DateTime::ISO8601 );
                                $params['id_tab_origen'] = 2;
				$id_tab_ac_ae = $comunes->InsertConID( $tabla , $params, 'id');

				$llave = array(
					'id_tab_ac_ae' => $id_tab_ac_ae
				);
			}

			foreach( $fondos as $f ) {
				$fuente = array(
					'id_tab_tipo_fondo' => $f->co_tipo_fondo,
					'mo_fondo' => $f->monto
				);
				$fuente['in_activo'] = 'TRUE';
				$fuente['created_at'] = date( \DateTime::ISO8601 );
				$fuente = array_merge($fuente, $llave);
				$comunes->InsertUpdate(
					'ac_seguimiento.tab_ac_ae_fuente',
					$fuente,
					'INSERT'
				);
			}
			$paraTransaccion->CompleteTrans();
			$respuesta = re\Helpers::responder( true );

		break;
	case 29: //consulta de fuentes de financiamiento

			$pk = re\Helpers::obtener_pertinentes(
				$_POST, array( 'id_tab_ac_ae' )
			);
			$existe = v::key( 'id_tab_ac_ae', v::intero()->notEmpty());

			$existe->assert( $pk );

			$res = $comunes->EjecutarQuery(<<<EOT
select id_tab_tipo_fondo as id_tipo_fondo, de_tipo_fondo as tx_tipo_fondo, mo_fondo as monto
from ac_seguimiento.tab_ac_ae_fuente as t56
join mantenimiento.tab_tipo_fondo as t61 on t61.id = t56.id_tab_tipo_fondo
where id_tab_ac_ae = ?;
EOT
				, array( $pk['id_tab_ac_ae'])
			);

			if ( $res ) {
				$fondos = array();
				foreach( $res as $v ) {
					$fondos[] = array(
						$v['id_tipo_fondo'], $v['tx_tipo_fondo'], $v['monto']
					);
				}
				$respuesta = re\Helpers::responder(
					true, null, array( 'data' => $fondos )
				);
			} else {
				$respuesta = re\Helpers::responder( false );
			}

		break;
	case 30:
			$pk = re\Helpers::obtener_pertinentes( $_POST, array( 'id_accion_centralizada' ) );
			$existe = v::key( 'id_accion_centralizada', v::string()->notEmpty() ) ;

			$existe->assert( $pk );
			if ($_GET['ae_dist'] == 1) {
				$sql = <<<EOT
SELECT id_tab_ac_ae, t54.nu_numero as numero, mes, round(t51.monto)::bigint monto,
min(mes) over (partition by nu_numero) as min,
max(mes) over (partition by nu_numero) as max
FROM ac_seguimiento.tab_ac_ae_distfin as t51
JOIN ac_seguimiento.tab_ac_ae as t53 on t53.id = t51.id_tab_ac_ae
JOIN mantenimiento.tab_ac_ae_predefinida as t54 on t54.id = t53.id_tab_ac_ae_predefinida
WHERE id_tab_ac = ?
ORDER BY nu_numero, mes;
EOT;
			} else {
				$sql = <<<EOT
SELECT id_tab_ac_ae, t54.nu_numero as numero, mes, round(t55.monto)::bigint monto,
min(mes) over (partition by nu_numero) as min,
max(mes) over (partition by nu_numero) as max
FROM ac_seguimiento.tab_ac_ae_disfisica as t55
JOIN ac_seguimiento.tab_ac_ae as t53 on t53.id = t55.id_tab_ac_ae
JOIN mantenimiento.tab_ac_ae_predefinida as t54 on t54.id = t53.id_tab_ac_ae_predefinida
WHERE id_tab_ac = ?
ORDER BY nu_numero, mes;
EOT;
			}

			$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( strval($pk['id_accion_centralizada'])) );

			if ($res !== FALSE) {
				//procesar
				$ae = null;
				$datos = array();
				$datos_c = array();
				foreach ( $res as $r ) {
					$numero = $r['id_tab_ac_ae'];
					$mes = intval( $r['mes'] );
					$monto = $r['monto'];
					if ( $ae !== $numero ) {
						$ae = $numero;
						//XXX por "culpa" del slice hay que empezar en 0
						$datos[$numero] = array_fill( 0, 13, 0 );
						$datos_c[$numero] = array_fill( 0, 13, 0 );
					}
					$datos[$numero][$mes] = $monto;
					$datos[$numero]['min'] = $r['min'];
					$datos[$numero]['max'] = $r['max'];
					$datos[$numero]['numero'] = $r['numero'];
					$datos_c[$numero][$mes] = $monto;
				}

				$trimestres = range( 0, 3 );
				foreach ( $datos_c as $ae => $r ) {
					$tot = 0;
					foreach ( $trimestres as $t ) {
						$tri = array_reduce(
							array_slice( $r, ( $t * 3 ) + 1, 3 ),
							function( $acu, $v ) {
								return $acu + intval( $v );
							}, 0 );
						$t++;
						$datos[$ae]["t$t"] = $tri;
						$tot += $tri;
					}
					$datos[$ae]['tot'] = $tot;
					$datos[$ae]['id'] = $ae;
				}

				$respuesta = re\Helpers::responder( true, null, array(
					'data' => array_values( $datos )
				) );
			} else {
				$respuesta = re\Helpers::responder( false,
					'Error obteniendo los datos'
				);
			}

		break;
	case 31: //elimina AE
			$pk = re\Helpers::obtener_pertinentes( $_POST, array( 'id_accion_centralizada',
				'id_accion_especifica' => 'numero' ) );
			$existe = v::key( 'id_accion_centralizada', v::intero()->notEmpty() )
				->key( 'numero', v::intero()->notEmpty() );

				$existe->assert( $pk );
				$sql = <<<EOT
DELETE
FROM ac_seguimiento.tab_ac_ae
WHERE id_tab_ac = ? AND id = ?;
EOT;
				$res = $comunes->EjecutarQuery( $sql, array(
					$pk['id_accion_centralizada'],
					$pk['numero']
				) );

				if ( $res ) {
					$respuesta = re\Helpers::responder( true );
				} else {
					$respuesta = re\Helpers::responder( false,
						'Error almacenando los datos'
					);
				}
		break;
	case 32: //cerrar si cuadra
			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id' ) );
			$existe = v::key( 'id', v::string()->notEmpty() );
			$existe->assert( $pk );

			$paraTransaccion->StartTrans();

			$sql = <<<EOT
update ac_seguimiento.tab_ac
set id_tab_estatus = 3
where nu_codigo = ?;
EOT;
			$res = $comunes->EjecutarQuery( $sql, array( $pk['id'] ) );
			$paraTransaccion->CompleteTrans();
			$respuesta = re\Helpers::responder( true );
		break;
	case 33: //cargar partidas de AE
			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'accion_centralizada' => 'id_accion_centralizada'
			));
			$validador = v::key( 'id_accion_centralizada', v::string()->notEmpty() );

			$sql = <<<EOT
select id_tab_ac_ae_predefinida as id_accion
from ac_seguimiento.tab_ac_ae
where in_activo and id_tab_ac = ?
order by id_tab_ac_ae_predefinida;
EOT;
			$res = $comunes->ObtenerFilasBySqlSelect( $sql, $params );
			//$acciones_especificas = array_column( $res, 0 );
			$acciones_especificas = array_map( function( $r ) {
				return intval( $r['id_accion'] );
			}, $res );
			$num = count( $res );

			if ( $num < 1 ) {
				//FIXME
				die( re\Helpers::responder( false, 'no hay acciones especificas' ) );
			}

				//parametros
				$validador->assert( $params );

				//archivo
				if( array_key_exists( 'archivo', $_FILES ) ) {
					$archivo = $_FILES['archivo'];
					if( ! empty( $archivo['tmp_name'] ) ) {
						$ruta = $archivo['tmp_name'];
						$tipo = $archivo['type'];

						//TODO mover a algo mas genérico
						//ajustes de plantilla
						//filas
						$inicio = 11;
						$fin = 1000;
						//columnas
						$codigo_desde = 1;
						$codigo_hasta = 4;
						$acciones = 6;
						$cols = array_merge(
							range( $codigo_desde, $codigo_hasta ),
							range( $acciones, $acciones + $num - 1 )
						);

						//TODO esto no va aquí
						$deriva_codigo_partida = function( $arr ) {
							$res = array_reduce( $arr,
								function( $acu, $val ) {
									if ( $acu !== false ) {
										if ( is_numeric( $val ) ) {
											$num = intval( $val );
											if ( $acu ) { //si hay algo de a 2
												if ( $num >= 0 and $num < 100 ) {
													return $acu .=  str_pad( "$val", 2,
													   '0', STR_PAD_LEFT );
												}
											} else { //sino de a 3
												if ( $num >= 0 and $num < 1000 ) {
													return $acu .=  str_pad( "$val", 3,
													   '0', STR_PAD_LEFT );
												}
											}
										}
									}
									return false;
								}, '' );
							return $res;
						};
						//

						$lector = new re\LectorHojaCalculo( $ruta, $tipo );
						//TODO mejorar (?) usando getCellByColumnAndRow y una funcion
						$datos = $lector->leer( $inicio, $fin, $cols );

						$paraTransaccion->StartTrans();

						//TODO borrado lógico?
						$comunes->EjecutarQuery( 'DELETE FROM ac_seguimiento.tab_ac_ae_partida WHERE id_tab_ac = ?;',
							array( $params['id_accion_centralizada'] ) );

						foreach( $datos as $f ) {
							$partida = $deriva_codigo_partida(
								array_slice( $f, 0, $codigo_hasta - $codigo_desde + 1 ) );

							if ( empty ( $partida ) ) {
								continue;
							}

							$salida = array(
								$params['id_accion_centralizada'],
								$partida
							);
							for ( $i = $acciones; $i < $acciones + $num; $i++ ) {
								$val = intval( $f[$i] );
								if ( $val > 0 ) {
									$floatval = floatval( $f[$i] );

									if ( $val != $floatval ) {
										$respuesta = re\Helpers::responder( false,
											'no se permiten valores con decimales:'
											. " primer error en la partida {$partida}"
											. ', AE ' . ($i - $acciones + 1) );
										$paraTransaccion->FailTrans();
										die( $respuesta );
									}
									$ae = $acciones_especificas[ $i - $acciones ];

									//FIXME
									$comunes->InsertUpdate(
										'ac_seguimiento.tab_ac_ae_partida',
										array(
											'id_tab_ac' => $params['id_accion_centralizada'],
											'co_partida' => $partida,
											'id_tab_ac_ae_predefinida' => $ae,
											'monto' => $val,
											'in_activo' => 'TRUE',
											'created_at' => date( \DateTime::ISO8601 )
										),
										'INSERT'
									);
								}
							}
						}

						//actualiza los montos calculados
						$sql = <<<EOT
update ac_seguimiento.tab_ac as t
set mo_calculado = (select ac_seguimiento.sp_calcular_monto(t.nu_codigo)) where nu_codigo = ?;
EOT;
						$sql2 = <<<EOT
update ac_seguimiento.tab_ac_ae t
set mo_ae_calculado = (select ac_seguimiento.sp_calcular_monto(t.id_tab_ac, t.id_tab_ac_ae_predefinida::integer)) where id_tab_ac = ?;
EOT;

						$comunes->EjecutarQuery( $sql, array( $params['id_accion_centralizada'] ) );
						$comunes->EjecutarQuery( $sql2, array( $params['id_accion_centralizada'] ) );

						$paraTransaccion->CompleteTrans();
						$respuesta = re\Helpers::responder( true );
					} else {
						$respuesta = re\Helpers::responder( false, 'no se pudo cargar el archivo' );
					}
				} else {
					$respuesta = re\Helpers::responder( false, 'debe enviar un archivo' );
				}
		break;
	case 34: //consulta partidas
			$pk = re\Helpers::obtener_pertinentes( $_POST, array( 'id_accion_centralizada',
				'id_accion_especifica' => 'numero' ) );
			$existe = v::key( 'id_accion_centralizada', v::string()->notEmpty() )
				->key( 'numero', v::intero()->notEmpty() );

			$existe->assert( $pk );
			$cuenta = <<<EOT
select count(*) as c
from ac_seguimiento.tab_ac_ae_partida as t54
where t54.id_tab_ac = ? and t54.id_tab_ac_ae_predefinida = ?
EOT;
			$sql = <<<EOT
select t44.co_partida, t44.tx_nombre, t54.monto
from ac_seguimiento.tab_ac_ae_partida as t54
left join ac_seguimiento.tab_ac as t46 on t46.nu_codigo = t54.id_tab_ac
left join mantenimiento.tab_partidas as t44 on t44.co_partida = t54.co_partida and t44.id_tab_ejercicio_fiscal = t46.id_tab_ejercicio_fiscal
where t54.id_tab_ac = ? and t54.id_tab_ac_ae_predefinida = ? and t46.in_activo and t44.in_activo
order by t44.co_partida::int limit ? offset ?;
EOT;

			$total = $comunes->ObtenerFilasBySqlSelect( $cuenta, array(
				$pk['id_accion_centralizada'], $pk['numero']
			))[0]['c'];

			list($start, $limit) = obtener_paginar($_REQUEST, 100);
			$res = $comunes->ObtenerFilasBySqlSelect( $sql, array(
				$pk['id_accion_centralizada'], $pk['numero'], $limit, $start
			) );

			if ( $res ) {
				$respuesta = re\Helpers::responder( true, null, array(
					'total' => $total,
					'data' => $res
				));
			} else {
				$respuesta = re\Helpers::responder( false, 'Error almacenando los datos' );
			}
		break;
	case 99:

			$pk = re\Helpers::obtener_pertinentes( $_POST, array( 'id' ) );
			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_ejercicio' => 'id_tab_ejercicio_fiscal',
				'id_accion' => 'id_tab_ac_predefinida',
				'descripcion' => 'de_ac',
				'id_subsector' => 'id_tab_sectores',
				'fecha_inicio' => 'fe_inicio',
				'fecha_fin' => 'fe_fin',
				'inst_mision',
                                'id_tab_lapso',
				'inst_vision',
				'inst_objetivos',
				'nu_po_beneficiar',
				'nu_em_previsto',
                                'tx_pr_objetivo' => 'pp_anual',
				'tx_re_esperado',
                                'id_ejecutores' => 'id_ejecutor',                            
				'co_situacion_presupuestaria' => 'id_tab_situacion_presupuestaria',
				'monto' => 'mo_ac'
			));
//$respuesta = re\Helpers::responder( false, $mensaje, array( 'data' => $params));
//echo $respuesta;
//exit();

			$params['id_tab_estatus'] = 1; //FIXME asi no
			$existe = v::key( 'id', v::intero()->notEmpty() );

			$ejercicio = intval($params['id_tab_ejercicio_fiscal']);
			$fechas = v::date( 'd-m-Y' )->between( '01-01-' . $ejercicio, '31-12-' . $ejercicio, true )->notEmpty();
                        

			$validador = v::key( 'id_tab_ejercicio_fiscal', v::intero()->notEmpty() )
				->key( 'id_tab_estatus', v::intero()->notEmpty() )
                                ->key( 'id_tab_lapso', v::intero()->notEmpty() )
				->key( 'id_tab_ac_predefinida', v::intero()->notEmpty() )
				->key( 'id_tab_sectores', v::intero()->notEmpty() )
				->key( 'de_ac', v::stringcadena()  )
				->key( 'inst_mision', v::stringcadena()  )
				->key( 'inst_vision', v::stringcadena()  )
				->key( 'inst_objetivos', v::stringcadena()  )
				->key( 'fe_inicio',  $fechas )
				->key( 'fe_fin', $fechas )
				->key( 'id_tab_situacion_presupuestaria', v::intero()->notEmpty() )
				->key( 'nu_po_beneficiar', v::numeric() )
				->key( 'nu_em_previsto', v::numeric() )
				->key( 'tx_re_esperado', v::stringcadena()  )
                                ->key( 'pp_anual', v::stringcadena() )
                                ->key( 'mo_ac', v::numeric() );
                        
                      

			if ( $usuario->co_rol > 2 ) { //es local
				$params['id_ejecutor'] = $usuario->id_ejecutor;
			} else {
				$validador = $validador->key(
					'id_ejecutor',v::stringcadena()->length( 4, 4, true )
				);
			}
   			
			$validador->assert($params);
                        
			$tabla = 'ac_seguimiento.tab_ac';
			$mensaje = null;

			$paraTransaccion->BeginTrans();

			if ( $existe->validate( $pk ) ) {
                            
				$sql_sum_ae = <<<EOT
SELECT sum(mo_ae) as mo_total_ae
FROM ac_seguimiento.tab_ac_ae
WHERE id_tab_ac = ?;
EOT;
				$res_sum_ae = $comunes->ObtenerFilasBySqlSelect($sql_sum_ae, $pk['id']); 
                                $res_sum_ae = $res_sum_ae[0];  
                                
				if ($params['mo_ac']<$res_sum_ae['mo_total_ae'] ) {
                                    
                                $mensaje = '<span>El monto de la accion centralizada no puede ser menor que la suma de las acciones especifcas, verifique!</span>';                                    
					$respuesta = re\Helpers::responder( false, $mensaje, array( 'data' => $params['mo_ac']));
					die($respuesta);
				}                                
                            
				$sql_desc = <<<EOT
SELECT de_nombre
FROM mantenimiento.tab_ac_predefinida
WHERE id = ?;
EOT;
				$res_desc = $comunes->ObtenerFilasBySqlSelect($sql_desc, $params['id_tab_ac_predefinida']); 
                                $res_desc = $res_desc[0];                             
                                $params['de_ac'] = $res_desc['de_nombre'];                        
				$params['updated_at'] = date( \DateTime::ISO8601 );
				$resultado = $comunes->InsertUpdate( $tabla, $params, 'UPDATE', 'id = '.$pk['id']);
				$resultado = $resultado === 'Ok';
			} else {
                            
                            
				$sql_ejecutor = <<<EOT
SELECT id,tx_ejecutor
FROM mantenimiento.tab_ejecutores
WHERE id_ejecutor = ?;
EOT;
				$res_ejecutor = $comunes->ObtenerFilasBySqlSelect($sql_ejecutor, $params['id_ejecutor']); 
                                $res_ejecutor = $res_ejecutor[0];
                                
				$sql_desc = <<<EOT
SELECT de_nombre
FROM mantenimiento.tab_ac_predefinida
WHERE id = ?;
EOT;
				$res_desc = $comunes->ObtenerFilasBySqlSelect($sql_desc, $params['id_tab_ac_predefinida']); 
                                $res_desc = $res_desc[0];   
                                
				$sql_lapso = <<<EOT
SELECT id
FROM mantenimiento.tab_lapso
WHERE id_tab_ejercicio_fiscal = ? and NOW() between fe_inicio and fe_fin;
EOT;
				$res_lapso = $comunes->ObtenerFilasBySqlSelect($sql_lapso, $params['id_tab_ejercicio_fiscal']); 
                                $res_lapso = $res_lapso[0];                                

				$params['id_tab_tipo_registro'] = 2;
				$params['in_activo'] = 'TRUE';
                                $params['id_tab_ejecutores'] = $res_ejecutor['id'];
                                $params['tx_ejecutor_ac'] = $res_ejecutor['tx_ejecutor'];
                                $params['de_ac'] = $res_desc['de_nombre'];
                                $params['mo_calculado'] = $params['mo_ac'];
                                $params['id_tab_origen'] = 2;
                                $params['nu_codigo'] = 'AC'.$params['id_ejecutor'].$params['id_tab_ejercicio_fiscal'].str_pad($params['id_tab_ac_predefinida'], 5,'0', STR_PAD_LEFT );
				$params['created_at'] = date( \DateTime::ISO8601 );
                                $params['updated_at'] = date( \DateTime::ISO8601 );
                      
				$res = $comunes->InsertConID( $tabla, $params, 'id');
 

				$sql = <<<EOT
SELECT nu_codigo,id
FROM ac_seguimiento.tab_ac
WHERE id = ? and in_activo is true LIMIT 1;
EOT;
				$resultado = $comunes->ObtenerFilasBySqlSelect($sql, array($res));
                                
                                

				if ( ! empty( $resultado ) ) {
					$resultado = $resultado[0];
					$mensaje = '<span style="color:green;font-size:13px,">El registro ha sido almacenado con el Código: <br><br><textarea readonly>'. $resultado['nu_codigo'].'</textarea></span>';
				} else {
					$resultado = false;
				}
			}

			if ($resultado) {
				$res = $paraTransaccion->CommitTrans();
				if ( $res ) {
					$respuesta = re\Helpers::responder( true, $mensaje, array( 'data' => $resultado));
					die($respuesta);
				}
			}
			$paraTransaccion->RollbackTrans();
			$respuesta = re\Helpers::responder( false, 'Error almacenando los datos');

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
	$mensaje = 'ocurrió una falla trabajando con la base de datos'.$e->getMessage();
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
