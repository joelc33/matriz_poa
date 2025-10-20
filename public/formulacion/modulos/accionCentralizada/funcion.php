<?php
require_once '../../comun.php';
require_once (__DIR__.'/../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../model/mantenimiento/tab_ac_ae_partida.php');
require_once (__DIR__.'/../../model/mantenimiento/tab_ac_ae_predefinida.php');

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions as ve;
use Reingsys as re;

$op = intval( $_REQUEST['op'] );

function obtener_paginar($f, $l) {
	$start = intval( $f['start'] );
	$limit = intval( $f['limit'] );
	if ( $limit === 0 ) {
		$limit = 20;
	}
	return array($start, $limit);
}

try {
	switch ( $op ) {
		case 1:
			$res = $comunes->ObtenerFilasBySqlSelect(
				'select id, nombre from t52_ac_predefinidas;'
			);

			if ( $res ) {
				$respuesta = re\Helpers::responder( true, null, array( 'data' => $res ) );
			} else {
				$respuesta = re\Helpers::responder( false, 'id no existe' );
			}
			break;

		case 2: //consusta de AC
			$id_accion = intval( $_REQUEST['id'] );
			if ( $id_accion > 0 ) {
				$sql = <<<EOT
SELECT id, id_ejercicio, id_ejecutor, nombre, descripcion,
	clase_subsector as co_sub_sector, fecha_inicio, fecha_fin,
	sit_presupuesto as co_situacion_presupuestaria, monto, t18.co_sector
FROM t46_acciones_centralizadas as t46
	JOIN t18_sectores as t18 on t46.clase_subsector = t18.co_sectores
WHERE id = ?;
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_accion ) );
				if ( $res ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res[0] ) );
				} else {
					$respuesta = re\Helpers::responder( false, 'id no existe' );
				}
			} else {
				$respuesta = re\Helpers::responder( false, 'id?' );
			}
			break;

		case 3: //listado de acciones especificas
			$id_accion = intval($_REQUEST['id']);
			if ( $id_accion > 0 ) {
				$cuenta = <<<EOT
SELECT count(*) as c
FROM t47_ac_accion_especifica as t47
WHERE id_accion_centralizada = ? and t47.edo_reg
EOT;
				$sql = <<<EOT
SELECT id_accion, t53.nu_numero as numero, t53.de_nombre as nombre, bien_servicio, t47.monto,
	t47.monto_calc, fecha_inicio, fecha_fin, t47.id_ejecutor, t24.tx_ejecutor,
	id_unidad_medida, de_unidad_medida as tx_unidades_medida, meta, objetivo_institucional,
	count(t54.id_accion) as npartidas
FROM t47_ac_accion_especifica as t47
	JOIN mantenimiento.tab_ejecutores as t24 on t47.id_ejecutor = t24.id_ejecutor
	JOIN mantenimiento.tab_ac_ae_predefinida as t53 on t53.id = t47.id_accion
	JOIN mantenimiento.tab_unidad_medida as t21 on t21.id = id_unidad_medida
	LEFT JOIN t54_ac_ae_partidas as t54 using (id_accion_centralizada, id_accion)
WHERE id_accion_centralizada = ? and t47.edo_reg
GROUP BY id_accion, t53.nu_numero, t53.de_nombre, bien_servicio, t47.monto,
	t47.monto_calc, fecha_inicio, fecha_fin, t47.id_ejecutor, t24.tx_ejecutor,
	id_unidad_medida, tx_unidades_medida, meta, objetivo_institucional
ORDER BY id_accion LIMIT ? OFFSET ?;
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

		case 4: //crear / actualizar acción específica
			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada',
				'id_accion'
			));
			$up = re\Helpers::obtener_pertinentes( $_POST, array(
				'up' => 'id_viejo'
			));
			$fondos = re\Helpers::obtener_pertinentes( $_POST, array(
				'fondos'
			));
			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'bien_servicio',
				'objetivo_institucional',
				'monto',
				'fondos',
				'id_unidad_medida',
				'meta',
				'fecha_inicio',
				'fecha_fin'
			));

			$primaria = v::key( 'id_accion_centralizada', v::intero()->notEmpty() )
				->key( 'id_accion', v::intero()->notEmpty() );

			$actualiza = v::key( 'id_viejo', v::intero()->notEmpty() );

			$fechas = v::date( 'd-m-Y' )->notEmpty();
			$validador = v::key( 'id_unidad_medida', v::intero()->positive()->notEmpty() )
				->key( 'monto', v::numeric()->positive()->notEmpty() )
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
SELECT id_ejecutor
FROM t46_acciones_centralizadas
WHERE id = ?;
EOT;
				$res_ejecutor = $comunes->ObtenerFilasBySqlSelect($sql_ejecutor, $pk['id_accion_centralizada']); 
                                $res_ejecutor = $res_ejecutor[0];                        
                        $params['id_ejecutor'] = $res_ejecutor['id_ejecutor'];                        

			$paraTransaccion->StartTrans();

			$llave = null;
			$tabla = 't47_ac_accion_especifica';
			if ( $actualiza->validate( $up ) ) {
				$params['id_accion'] = $pk['id_accion'];
				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'UPDATE',
					"id_accion_centralizada = {$pk['id_accion_centralizada']}"
					." and id_accion = {$up['id_viejo']}"
				);

				$llave = array(
					'id_ac' => $pk['id_accion_centralizada'],
					'id_ae' => $up['id_viejo']
				);

				$comunes->EjecutarQuery(
					'delete from t56_ac_ae_fuente where id_ac = ? and id_ae = ?;'
					, array( $llave['id_ac'], $llave['id_ae'] )
				);
			} else {
				$params = array_merge( $pk, $params );
				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'INSERT'
				);

				$llave = array(
					'id_ac' => $pk['id_accion_centralizada'],
					'id_ae' => $pk['id_accion']
				);
			}

			foreach( $fondos as $f ) {
				$fuente = array(
					'id_tipo_fondo' => $f->co_tipo_fondo,
					'monto' => $f->monto
				);
				$fuente = array_merge($fuente, $llave);
				$comunes->InsertUpdate(
					't56_ac_ae_fuente',
					$fuente,
					'INSERT'
				);
			}
			$paraTransaccion->CompleteTrans();

			$respuesta = re\Helpers::responder( true );
			break;

		case 5: //consultar estatuses
			$sql = 'SELECT * FROM t31_estatus';
			$res = $comunes->ObtenerFilasBySqlSelect( $sql );
			$respuesta = re\Helpers::responder( true, null, array( 'data' => $res ) );
			break;

		case 6: //cargar partidas de AE
			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'accion_centralizada' => 'id_accion_centralizada'
			));
			$validador = v::key( 'id_accion_centralizada', v::intero()->notEmpty() );

			$sql = <<<EOT
select id_accion
from t47_ac_accion_especifica
where edo_reg and id_accion_centralizada = ?
order by id_accion;
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
						$inicio = 10;
						$fin = 1973;
						//columnas
						$codigo_desde = 0;
						$codigo_hasta = 3;
						$acciones = 5;
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
						$comunes->EjecutarQuery( 'DELETE FROM t54_ac_ae_partidas WHERE id_accion_centralizada = ?;',
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

									if (tab_ac_ae_partida::where('id_tab_ac_ae_predefinida', '=', $ae)
									->where('nu_partida', '=', $partida)
									->where('in_activo', '=', true)
									->exists()) {

									}else {

										$validar_ae = tab_ac_ae_predefinida::select( 'id', 'nu_numero', 'de_nombre')
										->where('id', '=', $ae)
										->first();

										header('Content-Type: text/html');
										echo json_encode(array(
											'success' => false,
											'msg' => 'La Partida: '.$partida.', Monto: '.$val.', No se encuentra dentro de las partidas admitidas para: <br>'.$validar_ae->nu_numero.' - '.$validar_ae->de_nombre
										));
										exit();

									}

									//FIXME
									$comunes->InsertUpdate(
										't54_ac_ae_partidas',
										array(
											'id_accion_centralizada' => $params['id_accion_centralizada'],
											'co_partida' => $partida,
											'id_accion' => $ae,
											'monto' => $val,
											'id_tab_ejercicio_fiscal' =>  $_SESSION['ejercicio_fiscal']
										),
										'INSERT'
									);
								}
							}
						}

						//actualiza los montos calculados
						$sql = <<<EOT
update t46_acciones_centralizadas as t
set monto_calc = (select calcular_monto(t.id)) where id = ?;
EOT;
						$sql2 = <<<EOT
update t47_ac_accion_especifica t
set monto_calc = (select calcular_monto(t.id_accion_centralizada, t.id_accion)) where id_accion_centralizada = ?;
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

		case 7: //elimina AE
			$pk = re\Helpers::obtener_pertinentes( $_POST, array( 'id_accion_centralizada',
				'id_accion_especifica' => 'numero' ) );
			$existe = v::key( 'id_accion_centralizada', v::intero()->notEmpty() )
				->key( 'numero', v::intero()->notEmpty() );

				$existe->assert( $pk );
				$sql = <<<EOT
DELETE
FROM t47_ac_accion_especifica
WHERE id_accion_centralizada = ? AND id_accion = ?;
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

		case 8:
        case 9:
			$pk = re\Helpers::obtener_pertinentes( $_POST, array( 'id_accion_centralizada' ) );
			$existe = v::key( 'id_accion_centralizada', v::intero()->notEmpty() ) ;

			$existe->assert( $pk );
			if ( $op == 8 ) {
				$sql = <<<EOT
SELECT t53.nu_numero as numero, mes, round(t51.monto)::bigint monto,
min(mes) over (partition by nu_numero) as min,
max(mes) over (partition by nu_numero) as max
FROM t51_ac_ae_distribucion_financiera as t51
JOIN mantenimiento.tab_ac_ae_predefinida as t53 on t53.id = t51.id_ae
WHERE id_ac = ?
ORDER BY nu_numero, mes;
EOT;
			} else {
				$sql = <<<EOT
SELECT t53.nu_numero as numero, mes, round(t55.monto)::bigint monto,
min(mes) over (partition by nu_numero) as min,
max(mes) over (partition by nu_numero) as max
FROM t55_ac_ae_distribucion_fisica as t55
JOIN mantenimiento.tab_ac_ae_predefinida as t53 on t53.id = t55.id_ae
WHERE id_ac = ?
ORDER BY nu_numero, mes;
EOT;
			}

			$res = $comunes->ObtenerFilasBySqlSelect( $sql, array(
				$pk['id_accion_centralizada']
			) );

			if ($res !== FALSE) {
				//procesar
				$ae = null;
				$datos = array();
				$datos_c = array();
				foreach ( $res as $r ) {
					$numero = $r['numero'];
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

		case 10: //consulta partidas
			$pk = re\Helpers::obtener_pertinentes( $_POST, array( 'id_accion_centralizada',
				'id_accion_especifica' => 'numero' ) );
			$existe = v::key( 'id_accion_centralizada', v::intero()->notEmpty() )
				->key( 'numero', v::intero()->notEmpty() );

			$existe->assert( $pk );
			$cuenta = <<<EOT
select count(*) as c
from t54_ac_ae_partidas as t54
where t54.id_accion_centralizada = ? and t54.id_accion = ?
EOT;
			$sql = <<<EOT
select t44.co_partida, t44.tx_nombre, t54.monto
from t54_ac_ae_partidas as t54
left join t46_acciones_centralizadas as t46 on t46.id = t54.id_accion_centralizada
left join mantenimiento.tab_partidas as t44 on t44.co_partida = t54.co_partida and t44.id_tab_ejercicio_fiscal = t46.id_ejercicio
where t54.id_accion_centralizada = ? and t54.id_accion = ? and t46.edo_reg and t44.in_activo
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

		case 11: //cerrar si cuadra
			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id' ) );
			$existe = v::key( 'id', v::intero()->notEmpty() );
			$existe->assert( $pk );

			$paraTransaccion->StartTrans();

			$sql = <<<EOT
update t46_acciones_centralizadas
set id_estatus = 3
where id = ?;
EOT;
			$res = $comunes->EjecutarQuery( $sql, array( $pk['id'] ) );
			$paraTransaccion->CompleteTrans();
			$respuesta = re\Helpers::responder( true );
			break;

		case 12: //consulta responsables
			$respuesta = re\Helpers::responder( true, null, array( 'data' => null ) );
			$id_accion = intval( $_REQUEST['id'] );
			if ( $id_accion > 0 ) {
				$sql = <<<EOT
SELECT id_accion_centralizada, realizador_nombres, realizador_cedula,
realizador_cargo, realizador_correo, realizador_telefono, realizador_unidad,
registrador_nombres, registrador_cedula, registrador_cargo, registrador_correo,
registrador_telefono, registrador_unidad, autorizador_nombres,
autorizador_cedula, autorizador_cargo, autorizador_correo,
autorizador_telefono, autorizador_unidad
FROM t48_ac_responsables
WHERE id_accion_centralizada = ?
LIMIT 1;
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_accion ) );
				if ( ! empty($res) ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res[0] ) );
				}
			}
			break;

		case 13: //insertar/actualizar responsables
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
			$cadena->key( 'id_accion_centralizada', v::intero()->notEmpty() );

			$cadena->assert( $params );
			$tabla = 't48_ac_responsables';
			if ( $actualizar ) {
				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'UPDATE',
					"id_accion_centralizada = {$pk['id_accion_centralizada']}"
				);
			} else {
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

		case 14: //consulta ae predefinidas
			$id_accion = intval( $_REQUEST['id_accion'] );
			if ( $id_accion > 0 ) {
				$sql = 'select id, numero, nombre from t53_ac_ae_predefinidas where padre = ?;';
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

		case 15: //consultar vinculos
			$id_accion = intval( $_REQUEST['id'] );
			if ( $id_accion > 0 ) {
				$sql = <<<EOT

SELECT id_accion_centralizada,
trim(co_objetivo_historico) as co_objetivo_historico,
trim(co_objetivo_nacional) as co_objetivo_nacional,
trim(co_objetivo_estrategico) as co_objetivo_estrategico,
trim(co_objetivo_general) as co_objetivo_general,
co_area_estrategica,
co_macroproblema, co_nodos as co_nodo, co_ambito_estado as co_ambito_zulia,
co_objetivo_estado as co_objetivo_zulia
FROM t49_ac_planes
WHERE id_accion_centralizada = ?
LIMIT 1;
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_accion ) );
				if ( $res ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res[0] ) );
				} else {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => null ) );
				}
			} else {
				$respuesta = re\Helpers::responder( false, 'id?' );
			}
			break;

		case 16: //actualizar vinculos
			$actualizar = ( array_key_exists( 'up', $_POST ) and $_POST['up'] === 't' );
			$params = re\Helpers::obtener_pertinentes( $_POST, array(
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

			$clave = v::key( 'id', v::intero()->positive()->notEmpty() );
			//$reglas = v::key( 'co_nodos', v::arr()->notEmpty()->each( v::intero()->positive() ) );
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
				//$reglas = $reglas->key( $campo, v::intero()->positive()->notEmpty() );
			}

			$clave->assert( $pk );
			//$reglas->assert( $params );
			//$params['co_nodos'] = implode (',', $params['co_nodos'] );
			$tabla = 't49_ac_planes';
			if ( $actualizar ) {
				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'UPDATE',
					"id_accion_centralizada = {$pk['id']}"
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

		case 17: //consultar localidades
			$id_accion = intval( $_REQUEST['id'] );
			if ( $id_accion > 0 ) {
				$sql = <<<EOT
SELECT tx_municipio, t50.co_municipio, tx_parroquia, t50.co_parroquia
FROM t50_ac_localizacion as t50
JOIN t13_municipio as t13 on t13.co_municipio = t50.co_municipio
LEFT JOIN t14_parroquia as t14 on t14.co_parroquia = t50.co_parroquia
WHERE id_accion_centralizada = ?
ORDER BY t50.co_municipio, t50.co_parroquia NULLS FIRST
EOT;
				$res = $comunes->ObtenerFilasBySqlSelect( $sql, array( $id_accion ) );
				if ( $res ) {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => $res ) );
				} else {
					$respuesta = re\Helpers::responder( true, null, array( 'data' => null ) );
				}
			} else {
				$respuesta = re\Helpers::responder( false, 'id?' );
			}
			break;

		case 18: //actualizar localidades
			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id'
			) );

			$clave = v::key( 'id', v::intero()->positive()->notEmpty() );
			$clave->assert( $pk );

			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'localidades',
			) );
			$json = v::key( 'localidades', v::json()->notEmpty() );
			$json->assert( $params );
			$localidades = json_decode( $params['localidades'] );

//			$reglas = v::arr()->each(
//				v::object()->attribute( 'co_municipio', v::intero()->positive()->notEmpty() )
//					->attribute( 'co_parroquia', v::intero()->positive(), false )
//			);
//			$reglas->assert( $localidades );

			$paraTransaccion->StartTrans();

			$res = $comunes->EjecutarQuery(
				'delete from t50_ac_localizacion where id_accion_centralizada = ?;',
				array( $pk['id'] )
			);

			foreach ( $localidades as $lo ) {
				$mun = $lo->co_municipio;
				$par = $lo->co_parroquia ? $lo->co_parroquia : null;
				$res = $comunes->EjecutarQuery(<<<EOT
insert into t50_ac_localizacion(id_accion_centralizada, co_municipio, co_parroquia)
values(?,?,?);
EOT
				, array( $pk['id'], $mun, $par ) );
			}
			$paraTransaccion->CompleteTrans();
			$respuesta = re\Helpers::responder( true );
			break;

		//actualizar distribucion
		case 19:
		case 20:
			$pk = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_accion_centralizada' => 'id'
			) );

			$clave = v::key( 'id', v::intero()->positive()->notEmpty() );
			$clave->assert( $pk );

			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'data',
			) );
			$json = v::key( 'data', v::json()->notEmpty() );
			$json->assert( $params );
			$acciones = json_decode( $params['data'] );

//			$contenido = v::object()->attribute( 'id', v::intero()->positive()->notEmpty() );

//			foreach ( range(1,12) as $i ) {
//				$contenido = $contenido->attribute( "$i", v::intero()->min( 0, true ), false );
//			}
			if ( !is_array( $acciones ) ) {
				$acciones = array( $acciones );
			}

			$reglas = v::arr()->each( $contenido );
			$reglas->assert( $acciones );

			if ( $op == 19 ) {
				$sql = <<<EOT
update only t51_ac_ae_distribucion_financiera as t51
set monto = ?
from mantenimiento.tab_ac_ae_predefinida as t53
where t51.id_ae = t53.id
and t51.mes = ? and t51.id_ac = ? and t53.nu_numero = ?;
EOT;
			} else {
				$sql = <<<EOT
update only t55_ac_ae_distribucion_fisica as t55
set monto = ?
from mantenimiento.tab_ac_ae_predefinida as t53
where t55.id_ae = t53.id
and t55.mes = ? and t55.id_ac = ? and t53.nu_numero = ?;
EOT;
			}

			$paraTransaccion->StartTrans();

			foreach( $acciones as $ac ) {
				$id_ae = $ac->{'id'};
				foreach( $ac as $k => $v ) {
					if( preg_match( '/^\d+$/', $k ) === 1 ) {
						$res = $comunes->EjecutarQuery(
							$sql, array( $v, $k, $pk['id'], $id_ae )
						);
					}
				}
			}
			$res = $paraTransaccion->CompleteTrans();
			$respuesta = re\Helpers::responder( $res );
			break;

		case 21: //consulta de fuentes de financiamiento
			$pk = re\Helpers::obtener_pertinentes(
				$_POST, array( 'id_ac', 'id_ae' )
			);
			$existe = v::key( 'id_ac', v::intero()->notEmpty() )
				->key( 'id_ae', v::intero()->notEmpty() );

			$existe->assert( $pk );

			$res = $comunes->EjecutarQuery(<<<EOT
select t56.id_tipo_fondo, t61.de_tipo_fondo as tx_tipo_fondo, t56.monto
from t56_ac_ae_fuente as t56
	join mantenimiento.tab_tipo_fondo as t61 on t61.id = t56.id_tipo_fondo
where t56.id_ac = ? and t56.id_ae = ?;
EOT
				, array( $pk['id_ac'], $pk['id_ae'] )
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

		case 97: //reabrir
			$pk = re\Helpers::obtener_pertinentes( $_POST,
				array( 'id_accion_centralizada' => 'id' ) );

			$existe = v::key( 'id', v::intero()->notEmpty() );
			$existe->assert( $pk );

			$params = array(
				$pk['id']
			);

			$sql = <<<EOT
UPDATE t46_acciones_centralizadas
SET id_estatus = 1
WHERE id = ?
	AND edo_reg
EOT;

			if ( $usuario->co_rol > 2 ) { //es local
				$params[] = $usuario->id_ejecutor;
				$sql .= ' and id_ejecutor = ?';
			}

			$paraTransaccion->StartTrans();
			$paraTransaccion->Execute( $sql, $params);
			$res = $paraTransaccion->CompleteTrans();
			if ( $res ) {
				if ( $paraTransaccion->Affected_Rows() === 1 ) {
					$respuesta = re\Helpers::responder( true );
				} else {
					$respuesta = re\Helpers::responder(
						false, 'No se encontró la AC referida'
					);
				}
			} else {
				$respuesta = re\Helpers::responder( false,
					'Error realizando el cambio'
				);
			}
			break;

		case 98: // eliminar AC
			$pk = re\Helpers::obtener_pertinentes( $_POST,
				array( 'id_accion_centralizada' => 'id' ) );

			$existe = v::key( 'id', v::intero()->notEmpty() );
			$existe->assert( $pk );

			$params = array(
				$pk['id']
			);

			$sql = <<<EOT
DELETE
FROM t46_acciones_centralizadas
WHERE id = ?
	AND edo_reg
	AND id_estatus = 1
EOT;

			if ( $usuario->co_rol > 2 ) { //es local
				$params[] = $usuario->id_ejecutor;
				$sql .= ' and id_ejecutor = ?';
			}

			$res = $comunes->EjecutarQuery( $sql, $params);

			if ( $res ) {
				$respuesta = re\Helpers::responder( true );
			} else {
				$respuesta = re\Helpers::responder( false,
					'Error almacenando los datos'
				);
			}
			break;

		case 99: // crear / actualizar accion centralizada
			$pk = re\Helpers::obtener_pertinentes( $_POST, array( 'id' ) );
			$params = re\Helpers::obtener_pertinentes( $_POST, array(
				'id_ejercicio',
				'id_ejecutor',
				'id_accion',
				'descripcion',
				'id_subsector',
				'fecha_inicio',
				'fecha_fin',
				'inst_mision',
				'inst_vision',
				'inst_objetivos',
				'nu_po_beneficiar',
				'nu_em_previsto',
				'tx_re_esperado',
				'tx_pr_objetivo',
				'co_situacion_presupuestaria' => 'sit_presupuesto',
				'monto'
			));

			$params['id_estatus'] = 1; //FIXME asi no
			$existe = v::key( 'id', v::intero()->notEmpty() );

			$ejercicio = intval( $params['id_ejercicio'] );
			$fechas = v::date( 'd-m-Y' )->between( '01-01-' . $ejercicio,
				'31-12-' . $ejercicio, true )->notEmpty();

			$validador = v::key( 'id_ejercicio', v::intero()->notEmpty() )
				->key( 'id_estatus', v::intero()->notEmpty() )
				->key( 'id_accion', v::intero()->notEmpty() )
				->key( 'id_subsector', v::intero()->notEmpty() )
				->key( 'descripcion', v::stringcadena() )
				->key( 'inst_mision', v::stringcadena() )
				->key( 'inst_vision', v::stringcadena() )
				->key( 'inst_objetivos', v::stringcadena() )
				->key( 'fecha_inicio',  $fechas )
				->key( 'fecha_fin', $fechas )
				->key( 'sit_presupuesto', v::intero()->notEmpty() )
				->key( 'nu_po_beneficiar', v::numeric() )
				->key( 'nu_em_previsto', v::numeric() )
				->key( 'tx_re_esperado', v::stringcadena() )
				->key( 'tx_pr_objetivo', v::stringcadena() )
				->key( 'monto', v::numeric()->notEmpty() );

			if ( $usuario->co_rol > 2 ) { //es local
				$params['id_ejecutor'] = $usuario->id_ejecutor;
			} else {
				$validador = $validador->key(
					'id_ejecutor', v::stringcadena()->length( 4, 4, true )
				);
			}

			$validador->assert( $params );
			$tabla = 't46_acciones_centralizadas';
			$mensaje = null;

			$paraTransaccion->BeginTrans();

			if ( $existe->validate( $pk ) ) {
				$params['fecha_actualizacion'] = date( \DateTime::ISO8601 );
				$resultado = $comunes->InsertUpdate(
					$tabla,
					$params,
					'UPDATE',
					'id = ' . $pk['id']
				);
				$resultado = $resultado === 'Ok';
			} else {
                            
				$sql_ejecutor = <<<EOT
SELECT id,tx_ejecutor
FROM mantenimiento.tab_ejecutores
WHERE id_ejecutor = ?;
EOT;
				$res_ejecutor = $comunes->ObtenerFilasBySqlSelect($sql_ejecutor, $params['id_ejecutor']); 
                                $res_ejecutor = $res_ejecutor[0];
                                $params['tx_ejecutor_poa'] = $res_ejecutor['tx_ejecutor'];
                            
				$res = $comunes->InsertConID(
					$tabla,
					$params,
					'id'
				);

				$sql = <<<EOT
SELECT id, 'AC' || id_ejecutor || id_ejercicio ||
	lpad(id_accion::text, 5, '0') as codigo
FROM t46_acciones_centralizadas
WHERE id = ? and edo_reg LIMIT 1;
EOT;
				$resultado = $comunes->ObtenerFilasBySqlSelect( $sql,
					array( $res ) );

				if ( ! empty( $resultado ) ) {
					$resultado = $resultado[0];
					$mensaje = 'El registro ha sido almacenado con el Código: '
						. $resultado['codigo'];
				} else {
					$resultado = false;
				}
			}

			if ( $resultado ) {
				$res = $paraTransaccion->CommitTrans();
				if ( $res ) {
					$respuesta = re\Helpers::responder( true, $mensaje,
						array( 'data' => $resultado ));
					die( $respuesta );
				}
			}
			$paraTransaccion->RollbackTrans();
			$respuesta = re\Helpers::responder( false,
				'Error almacenando los datos'
			);
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
