<?php     
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ./');
}   
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

	$sql = "SELECT * from t16_solicitud where co_solicitud=".$_POST['codigo'];

	$result = $comunes->ObtenerFilasBySqlSelect($sql);

	foreach($result as $key => $row){
		$data = json_encode(array(
			    "co_solicitud"     => trim($row["co_solicitud"]),
		));
	}
?>
<script src="js/md5.js"></script>
<script type="text/javascript">
Ext.ns('datos');
datos.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

this.datos = '<p class="registro_detalle"><b>Fecha de Registro: </b>'+this.OBJ.fecha_creacion+'</p>';
this.datos += '<p class="registro_detalle"><b>R.I.F: </b>'+this.OBJ.nu_cedula+'</p>';
this.datos +='<p class="registro_detalle"><b>Nombre / Razon Social: </b>'+this.OBJ.nb_persona+' '+this.OBJ.ap_persona+'</p>';
this.datos +='<p class="registro_detalle"><b>Municipio: </b>'+this.OBJ.tx_municipio+'</p>';
this.datos +='<p class="registro_detalle"><b>Direccion: </b>'+this.OBJ.tx_direccion+'</p>';
this.datos +='<p class="registro_detalle"><b>Telefono Movil: </b>'+this.OBJ.nu_movil+'<b> Telefono Habitacion: </b>'+this.OBJ.nu_fijo+'</p>';
this.datos +='<p class="registro_detalle"><b>Ramo: </b>'+this.OBJ.tx_tipo_vivienda+'</p>';

this.fieldDatos = new Ext.form.FieldSet({
	title: 'Datos del Contribuyente',
	html: this.datos
});

this.datos2 ='<p class="registro_detalle"><b>Fecha de Solicitud: </b>'+this.OBJ.p_uno+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Codigo de Solictud: </b>'+this.OBJ.p_dos+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Tipo de Solictud: </b>'+this.OBJ.p_dos+'</p>';

this.fieldDatosElectorales = new Ext.form.FieldSet({
	title: 'Datos de la Solicitud',
	html: this.datos2
});

this.imprimir = new Ext.Button({
	text:'Imprimir Planilla',
	id:'imprimirPlanilla',
	iconCls: 'icon-pdf',
	handler: function(boton){
		  var vco_solicitud  = bandejaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_solicitud');
		  window.open("modulos/reportes/comprobante.php?c="+CryptoJS.MD5(vco_solicitud));
	}
});

this.formpanel = new Ext.form.FormPanel({
	bodyStyle: 'padding:10px',
	autoWidth:true,
	autoHeight:true,
        border:false,
        tbar:[
		this.imprimir
        ],
	items:[
		this.fieldDatos,this.fieldDatosElectorales		
		]
});

	this.tabuladores = new Ext.TabPanel({
		resizeTabs:true, // turn on tab resizing
		minTabWidth: 135,
		tabWidth:150,border:false,
		enableTabScroll:true,
		width:348,
		autoHeight:true,
		activeTab: 0,
		defaults: {autoScroll:true},
		items:[
			{
				title: 'Datos de la Solicitud',
				items:[this.formpanel]
			},
			{
				title: 'Detalles de la Solicitud',
				autoLoad:{
				url:'detalleFamiliares.php',
				scripts: true,
				params:{co_solicitud:this.OBJ.co_solicitud}
				}
			}
		]
	});

        this.tabuladores.render('detalle');

 	}
}
Ext.onReady(datos.main.init, datos.main);
</script>
