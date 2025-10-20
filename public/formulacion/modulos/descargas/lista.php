<?php
$con = file_get_contents( 'archivos.json' );
$mapa = json_decode( $con );
$archivos = array();
foreach ( $mapa->archivos as $rpt ) {
	if ( isset( $rpt->d ) ) {
		$archivos[] = $rpt;
	}
}
?>
<div class="archivo" id="archivos"></div>
<style>
	.archivo a {
		text-decoration: none;
		display: block;
		font: 14pt;
	}
</style>
<script type="text/javascript">
function objToString (obj) {
    var str = '';
    for (var p in obj) {
        if (obj.hasOwnProperty(p)) {
            //str += p + '::' + obj[p] + '\n';
            str += obj[p] + '\n';
        }
    }
    return str;
}
( function( Ext ) {
	Ext.ns( 'Archivos' );
	Archivos.init = function(){
		var store = Ext.create({
			xtype: 'jsonstore',
			storeId: 'archivos',
			data: <?php echo json_encode( $archivos ); ?>,
			idProperty: 'n',
			fields: [ 'n', 't', 'd' ]
		});
		var lista = Ext.create({
			xtype: 'grid',
			autoHeight: true,
			autoWidth: true,
			store: store,border:false,
			emptyText: 'No hay archivos para mostrar',
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
						url: 'formulacion/modulos/descargas/'+objToString(p)
					});
				}
			},
			bbar: ['->',{xtype: 'tbtext', text: '<span style="color:red;"><b>Precione Click sobre el archivo para descargar.</b></span>'}]
		});
		lista.render( 'archivos' );
	}
	Ext.onReady( Archivos.init );
}( Ext ) );
</script>
