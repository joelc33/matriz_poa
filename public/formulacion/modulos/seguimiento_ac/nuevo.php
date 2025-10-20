<?php
require_once '../../comun.php';

$accion = null;
$local = $usuario->co_rol > 2; //es planificador local?

if ( is_null( $accion ) ) {
	$ejercicio = $comunes->ObtenerFilasBySqlSelect( <<<EOT
SELECT id FROM mantenimiento.tab_ejercicio_fiscal WHERE id = $_SESSION[ejercicio_fiscal] LIMIT 1;
EOT
	)[0];
	$id_ejercicio = $ejercicio['id'];
	$contenedor = "ac_nueva";

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
		'fecha_fin' => "31-12-{$id_ejercicio}"
	);
}

?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<div id="<?php echo $contenedor; ?>"></div>
<script type="text/javascript">
<?php
	echo file_get_contents('../../js/async.js');
	echo file_get_contents('../../js/overrides.js');
	echo file_get_contents('../../js/Reingsys.js');
	echo file_get_contents('ac_nueva.js');
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
</body>
</html>
