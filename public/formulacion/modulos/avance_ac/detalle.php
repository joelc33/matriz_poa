<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}  
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT * FROM ac_seguimiento.tab_meta_seguimiento
	where id=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"id"     => trim($row["id"]),
			"id_tab_meta_fisica"     => trim($row["id_tab_meta_fisica"]),
			"nu_partida"     => trim($row["nu_partida"]),
			"mo_presupuesto"     => trim($row["mo_presupuesto"]),
			"nu_ponderacion"     => trim($row["nu_ponderacion"]),
			"fe_inicio"     => trim(date_format(date_create($row["fe_inicio"]),'d/m/Y')),
			"fe_fin"     => trim(date_format(date_create($row["fe_fin"]),'d/m/Y')),
			"tx_observacion"     => trim($row["tx_observacion"]),
			"nu_lapso"     => trim($row["nu_lapso"]),
		));
	}
}else{
	$data = json_encode(array(
		"id"     => decode($_POST['codigo']),
	));
}
?>
<script type="text/javascript">
Ext.ns("detalleAvance");
detalleAvance.paquete = {
imagen: function(codigo){
	return  '<img width="220" height="150" src="modulos/imagen/funcion.php?op=6&codigo='+codigo+'&t='+new Date().getTime()+'">';
},
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

this.datosDetalle  = '';
this.datosDetalle += '<p class="contrib_detalle"><b>Fecha Inicio: </b>'+this.OBJ.fe_inicio+'</p>';
this.datosDetalle += '<p class="contrib_detalle"><b>Fecha Corte: </b>'+this.OBJ.fe_fin+'</p>';
this.datosDetalle += '<p class="contrib_detalle"><b>Partida: </b>'+this.OBJ.nu_partida+'</p>';
this.datosDetalle += '<p class="contrib_detalle"><b>Presupuesto: </b>'+this.OBJ.mo_presupuesto+'</p>';
this.datosDetalle += '<p class="contrib_detalle"><b>Ponderacion: </b>'+this.OBJ.nu_ponderacion+' %</p>';
this.datosDetalle += '<p class="contrib_detalle"><b>Observacion: </b>'+this.OBJ.tx_observacion+'</p>';

this.fielset_detalle= new Ext.form.FieldSet({
        title: 'Información',
        html: this.datosDetalle
});

this.storeDatosImagenes=detalleAvance.paquete.getStoreImagenes();

if(this.OBJ.id){
	detalleAvance.paquete.storeDatosImagenes.baseParams.id_tab_meta_seguimiento = this.OBJ.id;
	detalleAvance.paquete.storeDatosImagenes.load();
}

this.gridImagen = new Ext.grid.GridPanel({
	height:200,
	width:300,border:false,
	autoScroll:true,
	store:detalleAvance.paquete.storeDatosImagenes,
	columns: [
		{width: 290,height:200,sortable: true, renderer: detalleAvance.paquete.imagen, dataIndex: 'co_img_avance'}
	],
	bbar: new Ext.PagingToolbar({
		pageSize: 1,
		store: detalleAvance.paquete.storeDatosImagenes
	})
});

this.PanelImagen = new Ext.form.FieldSet({
	title: 'Imagenes',
	border:true,
	items: [
		this.gridImagen
	]
});

this.p = new Ext.Panel({
	applyTo: 'detalleAvance',
	border:false,
	autoWidth: true,
	autoHeight:true,
	bodyStyle: 'padding: 10px',
	items: [this.fielset_detalle,this.PanelImagen]
});
},
getStoreImagenes: function(){
	this.store = new Ext.data.JsonStore({
		url:'formulacion/modulos/avance_ac/funcion.php',
		root:'data',
		baseParams: {
			op: 6
		},
		fields:[{name: 'co_img_avance'}]
	});
	return this.store;
}
}
Ext.onReady(detalleAvance.paquete.init,detalleAvance.paquete);
</script>
<div id="detalleAvance"></div>
