<?php
require_once '../../comun.php';
use Reingsys as re;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions as ve;

try {
	$nom = re\Helpers::obtener( $_GET, 'r' );
	if ( is_null( $nom ) ) {
		throw new Exception( 'no hay nombre de reporte' );
	} 

	if ( trim($nom) == 'exportacion_icp_ac') {
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		header("Location: ".$actual_link."/reporte/poa/ac/exportacion/ic/pac");
		die();
	}

	if ( trim($nom) == 'exportacion_icp_ac_desagregado') {
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		header("Location: ".$actual_link."/reporte/poa/ac/exportacion/ic/pac/desagregado");
		die();
	}

	$con = file_get_contents( 'jasper/mapa_'.$_SESSION['ejercicio_fiscal'].'.json' );
	$mapa = json_decode( $con, true );
	$reportes = array_filter( $mapa['reportes'],
		function( $r ) use( $nom ) {
			return $r['n'] == $nom;
		}
	);

	if ( empty( $reportes ) ) {
		throw new Exception( 'el nombre de reporte no es válido' );
	}

	$entorno = array();
	$ejecutar = false;

	$salida = $nom;
	$reporte = current( $reportes );
	$fuentes = array(
		"u" => $usuario
	);

	$perms_reporte = re\Helpers::obtener( $reporte, 'p' );
	if ( $perms_reporte ) {
		function procesar_parametros( $params_reporte, $fuentes, $entrada ) {
			$entorno = array();
			$lista = array_keys( $params_reporte );
			$entradas = re\Helpers::obtener_pertinentes(
				$entrada, $lista, true
			);
			if( count( $lista ) !== count( $entradas ) ) {
				throw new Exception( 'faltan parametros' );
			}
			foreach ( $params_reporte as $pnom => $prgx ) {
				$valor = $entradas[$pnom];
				$regex = '';
				$matches = array();
				$verificar = array();
				foreach ( $prgx as $seg ) {
					if ( is_array( $seg ) ) {
						list( $seg2, $val ) = $seg;
						list( $f, $k ) = $val;
						$verificar[] = $fuentes[$f]->$k;
						$seg = $seg2;
					}
					$regex .= $seg;
				}
				$res = preg_match( $regex, $valor, $matches );
				if ( $res !== 1 ) {
					throw new Exception(
						'parámetro mal formado: ' . $pnom );
				}
				if ( !empty( $verificar ) ) {
					$diff = array_diff_assoc(
						array_slice( $matches, 1 ), $verificar
					);
					if ( !empty( $diff ) ) {
						throw new Exception(
							'parámetros inválidos: ' . $pnom );
					}
				}
				$entorno[$pnom] = $valor;
			}
			return $entorno;
		}
		foreach ( $perms_reporte as $perm_reporte ) {
			switch ( $perm_reporte['c'] ) {
				case 'lista':
					list( $f, $k ) = $perm_reporte['v'];
					$v = $fuentes[$f]->$k;
					$pv = $perm_reporte['p'];
					if ( in_array( $v, $pv ) ) {
						$entorno = procesar_parametros(
							$perm_reporte['l'], $fuentes, $_GET );
						$ejecutar = true;
						break 2;
					}
					break;
				case 'resto':
					$entorno = procesar_parametros(
						$perm_reporte['l'], $fuentes, $_GET );
					$ejecutar = true;
					break;
			}
		}
		$salida = sha1( "blah_{$nom}_" . json_encode( $entorno ) );
	} else {
		$ejecutar = true;
	}
	
	if ( $ejecutar ) {
		$rj = new re\ReportesJasper( __DIR__.'/jasper/', '/tmp/' );
		if ( array_key_exists( 'x', $reporte ) ) {
			$res = $rj->generarXLS( $nom, $salida, $entorno );
			$mime = "application/vnd.ms-excel";
			$extension = ".xls";
		} else {
			$res = $rj->generar( $nom, $salida, $entorno );
			$mime = 'application/pdf';
			$extension = ".pdf";
		}
		$nom_salida = $reporte['t'] . ' ' . date( 'Y-m-d Hi' ).$extension;
		re\Helpers::send_file_http( $res, $nom_salida, $mime );
	} else {
		echo 'no se cumplieron todas las condiciones';
	}
} catch ( Exception $e ) {
	error_log( 'excepción: ' . $e->getMessage() );
	echo 'ocurrio un error procesando la solicitud';
}

