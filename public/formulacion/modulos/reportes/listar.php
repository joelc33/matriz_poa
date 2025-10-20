<?php
require '../../comun.php';
$ruta = 'formulacion/modulos/reportes/';
$con = file_get_contents( 'jasper/mapa_'.$_SESSION['ejercicio_fiscal'].'.json' );
$mapa = json_decode( $con );
$reportes = array();
foreach ( $mapa->reportes as $rpt ) {
	if ( isset( $rpt->d ) ) {
		$reportes[] = $rpt;
	}
}
?>
<div class="reporte" id="reportes"></div>
<style>
	.reporte a {
		text-decoration: none;
		display: block;
		font: 14pt;
	}
</style>
<script type="text/javascript">
( function( Ext ) {
	Ext.ns( 'Reportes' );
	Reportes.init = function(){
		var store = Ext.create({
			xtype: 'jsonstore',
			storeId: 'reportes',
			data: <?php echo json_encode( $reportes ); ?>,
			idProperty: 'n',
			fields: [ 'n', 't', 'd' ]
		});
		var lista = Ext.create({
			xtype: 'grid',
			autoHeight: true,
			autoWidth: true,
			store: store,
			emptyText: 'No hay reportes para mostrar',
			viewConfig: {
				headersDisabled: true,
				forceFit: true
			},
			columns: [{
				dataIndex: 't',
				menuDisabled: false,
				sortable: true
			}],
			listeners: {
				rowclick: function(grid, idx){
					var r, p;
					r = grid.getStore().getAt(idx).data;
					console.log(r);
					p = Ext.apply({
						"r": r.n
					}, r.d)
					window.bajar.load({
						url: 'formulacion/modulos/reportes/ver.php',
						params: p
					});
				}
			},
			bbar: [{
				text: 'Recargar Mapa ACs',
				handler: function() {
					Ext.Ajax.request({
						method: 'POST',
						url: 'formulacion/modulos/reportes/regenerar.php',
						params: {
							op: 1
						},
						success: function(result) {
							var obj = Ext.util.JSON.decode(result.responseText);
							if (obj.success) {
								//self.store.reload();
							}
							Ext.Msg.alert("Notificación", obj.msg);
						},
						failure: function() {
							Ext.Msg.alert("Ocurrió un error contactando al servidor");
						}
					});
				}
			} , {
				text: 'Recargar Mapa Proyectos',
				handler: function() {
					Ext.Ajax.request({
						method: 'POST',
						url: 'formulacion/modulos/reportes/regenerar.php',
						params: {
							op: 2
						},
						success: function(result) {
							var obj = Ext.util.JSON.decode(result.responseText);
							if (obj.success) {
								//self.store.reload();
							}
							Ext.Msg.alert("Notificación", obj.msg);
						},
						failure: function() {
							Ext.Msg.alert("Ocurrió un error contactando al servidor");
						}
					});
				}
			}]
		});
		lista.render( 'reportes' );
	}
	Ext.onReady( Reportes.init );
}( Ext ) );
</script>

