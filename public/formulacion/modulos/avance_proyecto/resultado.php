<?php     
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

	/*$sql = "SELECT *, monto_cargado(id_proyecto) as mo_registrado FROM t26_proyectos as t26
	inner join t24_ejecutores as t24 on t26.id_ejecutor=t24.id_ejecutor";*/

	if($_POST['BuscarBy']=="true"){
		$sql.="SELECT nu_codigo as id_proy_ac, de_nombre, tx_ejecutor, fe_inicio, fe_fin, mo_proyecto, 
			proyecto_seguimiento.sp_proyecto_mo_cargado(nu_codigo) as mo_registrado, '1' as co_tipo 
			FROM proyecto_seguimiento.tab_proyecto as t26
			inner join mantenimiento.tab_ejecutores as t24 on t26.id_tab_ejecutores=t24.id_ejecutor 
			where t26.nu_codigo = '".decode($_POST['id_proyecto'])."' AND t26.in_activo is true";
		//if($_POST['id_proyecto']!=""){$sql.=" and id_proyecto ='".decode($_POST['id_proyecto'])."'";}
	}
		$sql.=" LIMIT 2";

	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	$cantidadTotal = $comunes->getFilas($sql);

	if($cantidadTotal==1){

	foreach($result as $key => $row){
		$data = json_encode(array(
			    "id_proyecto"     => trim($row["id_proy_ac"]),
			    "nombre"     => trim($row["de_nombre"]),
			    "id_ejecutor"     => trim($row["tx_ejecutor"]),
			    "fecha_inicio"     => trim(date_format(date_create($row["fe_inicio"]),'d/m/Y')),
			    "fecha_fin"     => trim(date_format(date_create($row["fe_fin"]),'d/m/Y')),
			    "monto"     => trim($row["mo_proyecto"]),
			    "mo_registrado"     => trim($row["mo_registrado"]),
		));
	$co_tipo = $row["co_tipo"];
	}
	if($co_tipo==1){
		$datosEnunciado='Proyecto';
		$fieldDatos='Datos del Proyecto';
		$co_proy_ac='Codigo de Proyecto';
		$url_proy_ac='formulacion/modulos/avance_proyecto/meta_proyecto.php';
		$op='1';
		$variable="+'&id_proyecto='+resultadoPRSAC.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto')";
	}else{
		$datosEnunciado='Accion Centralizada';
		$fieldDatos='Datos de la Accion Centralizada';
		$co_proy_ac='Codigo de la Accion Centralizada';
		$url_proy_ac='formulacion/modulos/metas/acLista.php';
		$op='2';
		$variable="+'&id_accion_centralizada='+resultadoPRSAC.main.gridPanel_.getSelectionModel().getSelected().get('id_accion_centralizada')";
	}
?>
<script type="text/javascript">
Ext.ns("resultadoPRSAC");
function colorIndicador(v, meta, rec){
	if(rec.get('total') == rec.get('mo_cargado')){
	    return '<span style="color:green;">'+formatoNumero(rec.get('mo_cargado'))+'</span>';
	}else{
	    return '<span style="color:red;">'+formatoNumero(rec.get('mo_cargado'))+'</span>';
	}
return val;
};
resultadoPRSAC.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

this.datos = '<p class="registro_detalle"><b><?php echo $co_proy_ac;?>: </b>'+this.OBJ.id_proyecto+'  <b>Nombre: </b>'+this.OBJ.nombre+'</p>';
this.datos += '<p class="registro_detalle"><b>Ejecutor: </b>'+this.OBJ.id_ejecutor+' <b>Fecha de Inicio: </b>'+this.OBJ.fecha_inicio+'  <b>Fecha de Cierre: </b>'+this.OBJ.fecha_fin+'</p>';
this.datos += '<p class="registro_detalle"><b>Monto: </b>'+formatoNumero(this.OBJ.monto)+'  <b>Monto Registrado: </b>'+formatoNumero(this.OBJ.mo_registrado)+'</p>';

this.fieldDatos = new Ext.form.FieldSet({
	title: ' <?php echo $fieldDatos;?>',
	autoWidth: true,
	autoHeight:true,
	html: this.datos,
});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.accionFisica = new Ext.Button({
	text:'Ver Actividades',
	id:'verFisico',
	iconCls: 'icon-accion_fisica',
	tooltip: 'Ver Actividades de la Accion Especifica',
	handler: function(boton){
		addTab(resultadoPRSAC.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto')+'ae','<?php echo $datosEnunciado;?>:'+resultadoPRSAC.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto')+' >  Accion Especifica:'+resultadoPRSAC.main.gridPanel_.getSelectionModel().getSelected().get('tx_codigo'),'<?php echo $url_proy_ac;?>','load','icon-accion_especifica','codigo='+resultadoPRSAC.main.gridPanel_.getSelectionModel().getSelected().get('co_proyecto_acc_espec')<?php echo $variable;?>);
	}
});

this.accionFisica.disable();

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
	title: 'Acciones Especificas',
    iconCls: 'icon-accion_especifica',
    store: this.store_lista,
    loadMask:true,
    autoWidth: true,
    autoHeight:true,
    tbar:[
        this.accionFisica
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_acc_espec',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_acc_espec'},
    {header: 'id_accion_centralizada',hidden:true, menuDisabled:true,dataIndex: 'id_accion_centralizada'},
    {header: 'id_proyecto',hidden:true, menuDisabled:true,dataIndex: 'id_proyecto'},
    {header: 'CÓD.', width:50,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo'},
    {header: 'Nombre de la Acción', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'descripcion'},
    {header: 'UNIDAD DE MEDIDA', width:120,  menuDisabled:true, sortable: true,  dataIndex: 'co_unidades_medida'},
    {header: 'TOTAL GENERAL Bs.', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'total'},
    {header: 'TOTAL CARGADO', width:120,  menuDisabled:true, sortable: true, renderer: colorIndicador, dataIndex: 'mo_cargado'},
    {header: 'FECHA INICIO', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'fec_inicio'},
    {header: 'FECHA CULMINACIÓN', width:130,  menuDisabled:true, sortable: true,  dataIndex: 'fec_termino'},
    {header: 'UNIDAD EJECUTORA RESPONSABLE', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'co_ejecutores'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){resultadoPRSAC.main.accionFisica.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 5,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.panel = new Ext.Panel({
	layout: "fit",
	border: false,
	padding	: 5,
	autoHeight:true,
	autoWidth: true,
	autoScroll:true,
	items: [this.fieldDatos,this.gridPanel_]
});

this.panel.render("consultarAccionSAP");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista.baseParams.BuscarBy = true;
this.store_lista.load();
this.store_lista.on('load',function(){
resultadoPRSAC.main.accionFisica.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/avance_proyecto/funcion.php',
    root:'data',
    baseParams: {
	op: <?php echo $op;?>
    },
    fields:[
    {name: 'co_proyecto_acc_espec'},
    {name: 'id_accion_centralizada'},
    {name: 'id_proyecto'},
    {name: 'tx_codigo'},
    {name: 'descripcion'},
    {name: 'co_unidades_medida'},
    {name: 'meta'},
    {name: 'ponderacion'},
    {name: 'bien_servicio'},
    {name: 'total'},
    {name: 'mo_cargado'},
    {name: 'fec_inicio'},
    {name: 'fec_termino'},
    {name: 'co_ejecutores'},
           ]
    });
    return this.store;
}
};
Ext.onReady(resultadoPRSAC.main.init, resultadoPRSAC.main);
</script>
<?php     
}else{
?>
<script type="text/javascript">
Ext.ns('resultadoPRSAC');
resultadoPRSAC.main = {
	init: function(){
	this.tabuladores = new Ext.Panel({
		title: 'Busqueda',
		layout: "fit",
		border: false,
		padding	: 10,
		html: '<p><i>Sin Resultados...</i></p>',
	});
	this.tabuladores.render('consultarAccionSAP');
	}
}
Ext.onReady(resultadoPRSAC.main.init, resultadoPRSAC.main);
</script>
<?php
}
?>
