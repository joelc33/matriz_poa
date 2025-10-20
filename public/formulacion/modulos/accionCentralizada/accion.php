<?php
require_once '../../comun.php';

$id_accion = null;
if ( array_key_exists( 'id', $_POST ) ) {
    $id_accion = intval( $_POST['id'] );
}
$accion = null;
$local = $usuario->co_rol > 2; //es planificador local?

if( in_array( array( 'de_privilegio' => 'ac.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){
  $ac_guardar = true;
}else{
  $ac_guardar = false;
}


if ( $id_accion > 0 ) {
	$params = array();
	$sql = <<<EOT
SELECT t46.id, id_ejercicio, t46.id_ejecutor, t24.tx_ejecutor as ejecutor,
'AC' || t24.id_ejecutor || id_ejercicio || lpad(id_accion::text, 5, '0') as codigo,
codigo_new_etapa as co_sistema, t46.id_accion, descripcion,
id_estatus, id_subsector, sit_presupuesto as co_situacion_presupuestaria,
monto, t18.co_sector, fecha_inicio, fecha_fin, t52.nombre,
inst_mision, inst_vision, inst_objetivos, nu_po_beneficiar, nu_em_previsto, tx_re_esperado, tx_pr_objetivo,
(t46.id_estatus = 3) as bloqueado
FROM t46_acciones_centralizadas as t46
JOIN mantenimiento.tab_sectores as t18 on t46.id_subsector = t18.id
JOIN mantenimiento.tab_ejecutores as t24 on t46.id_ejecutor = t24.id_ejecutor
JOIN t52_ac_predefinidas as t52 on t52.id = t46.id_accion
EOT;
	$where = ' WHERE t46.id = ?';
	$params[] = $id_accion;

	/*if ( $local ) { //planificador local sólo ve los de su ejecutor
		$params[] = $usuario->id_ejecutor;
		$where .= ' AND t46.id_ejecutor = ?';
	}*/

	$res = $comunes->ObtenerFilasBySqlSelect( $sql.$where, $params );
	if ( count( $res ) > 0 ) {
		$accion = $res[0];
		$id_ejercicio = $res['id_ejercicio'];
		$contenedor = "contenedorAccionCentralizada_{$id_accion}";
		$accion['es_local'] = $local;

    $rol_planificador = array(1, 2, 3, 8);
    if (in_array($usuario->co_rol, $rol_planificador)) {
		  $accion['bloqueado'] = $accion['bloqueado'] === 't';
    }else{
      //$accion['bloqueado'] = 'f';
      if($accion['id_ejecutor']==$usuario->id_ejecutor){
        $accion['bloqueado'] = false;
      }else{
        $accion['bloqueado'] = true;
      }
    }

		$credencial = array('ac_guardar' => $ac_guardar);
		$accion = array_merge($accion, $credencial);
		//var_dump($accion); exit();
	}
}

if ( is_null( $accion ) ) {
	$ejercicio = $comunes->ObtenerFilasBySqlSelect( <<<EOT
SELECT id as co_ejercicio_fiscal FROM mantenimiento.tab_ejercicio_fiscal WHERE id = $_SESSION[ejercicio_fiscal] LIMIT 1;
EOT
	)[0];
	$id_ejercicio = $ejercicio['co_ejercicio_fiscal'];
	$contenedor = "contenedorAccionCentralizada_nueva";

	$accion = array(
		'id' => null,
		'codigo' => null,
		'id_ejercicio' => $id_ejercicio,
		'id_ejecutor' => $usuario->id_ejecutor,
		'es_local' => $local,
		'bloqueado' => false,
		'id_accion' => null,
		'co_sistema' => null,
		'descripcion' => null,
		'sit_presupuesto' => null,
		'monto' => null,
		'co_sector' => null,
		'id_subsector' => null,
		'fecha_inicio' => "01-01-{$id_ejercicio}",
		'fecha_fin' => "31-12-{$id_ejercicio}",
		'ac_guardar' => $ac_guardar
	);
}

//anio de ejercicio fiscal activo
/*$sql_verificar = "SELECT EXISTS (SELECT id FROM mantenimiento.tab_ejercicio_fiscal where id = ".$_SESSION['ejercicio_fiscal']." AND in_activo is true)::int as activo;";*/
$sql_verificar = "SELECT EXISTS (SELECT id_tab_ejercicio_fiscal
  FROM mantenimiento.tab_apertura_ef WHERE in_activo is true AND id_tab_ejercicio_fiscal = ".$_SESSION['ejercicio_fiscal']." AND NOW() between fe_desde and fe_hasta)::int as activo;";
$res_verificar = $comunes->ObtenerFilasBySqlSelect($sql_verificar);
if($res_verificar[0]['activo']==0 && is_null( $id_accion )){
?>
<script type="text/javascript">
Ext.ns('nuevoAC');
nuevoAC.main = {
	init: function(){
	this.tabuladores = new Ext.Panel({
		title: 'Aviso: Periodo Fiscal <?php echo $_SESSION['ejercicio_fiscal'];?> Cerrado.',
		iconCls: 'icon-info',
		layout: "fit",
		border: false,
		padding	: 10,
		html: '<center><p><i><b>Ejercicio Fiscal Año:</b> <?php echo $_SESSION['ejercicio_fiscal'];?></i></p><p><i><b>Estado:</b> Cerrado.</i></p><p><i>No se pueden crear nuevas Acciones Centralizadas para este Periodo Fiscal.</i></p></center>',
	});
	this.tabuladores.render('<?php echo $contenedor; ?>');
	}
}
Ext.onReady(nuevoAC.main.init, nuevoAC.main);
</script>
<div id="<?php echo $contenedor; ?>"></div>
<?php
}elseif($res_verificar[0]['activo']==0 && $id_accion !== ''){
?>
<div id="<?php echo $contenedor; ?>"></div>
<script type="text/javascript">
<?php
	echo file_get_contents('../../js/async.js');
	echo file_get_contents('../../js/overrides.js');
	echo file_get_contents('../../js/Reingsys.js');
	echo file_get_contents('accion_centralizada.js');
	echo file_get_contents('accion_especifica.js');
?>
Ext.onReady( function() {
	var ac = <?php echo json_encode( $accion ); ?>;
	var panel = Ext.create({
		xtype: 'accion_centralizada',
		frm: {
			ac: ac,
			extra: {
				fecha_ini: '01-01-' + ac.id_ejercicio,
				fecha_fin: '31-12-' + ac.id_ejercicio,
			}
		}
	});
	panel.render( '<?php echo $contenedor; ?>' );
});
</script>
<?php
}elseif($res_verificar[0]['activo']==1 && $id_accion !== ''){
?>
<div id="<?php echo $contenedor; ?>"></div>
<script type="text/javascript">
<?php
	echo file_get_contents('../../js/async.js');
	echo file_get_contents('../../js/overrides.js');
	echo file_get_contents('../../js/Reingsys.js');
	echo file_get_contents('accion_centralizada.js');
	echo file_get_contents('accion_especifica.js');
?>
Ext.onReady( function() {
	var ac = <?php echo json_encode( $accion ); ?>;
	var panel = Ext.create({
		xtype: 'accion_centralizada',
		frm: {
			ac: ac,
			extra: {
				fecha_ini: '01-01-' + ac.id_ejercicio,
				fecha_fin: '31-12-' + ac.id_ejercicio,
			}
		}
	});
	panel.render( '<?php echo $contenedor; ?>' );
});
</script>
<?php
}
?>
