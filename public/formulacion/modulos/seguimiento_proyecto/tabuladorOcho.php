<?php
	$data8 = json_encode(array(
		"id_proyecto"     => $id_proyecto,
	));
?>
<script type="text/javascript">
Ext.ns("tabuladorOcho");
tabuladorOcho.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data8 ?>'});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista_especifica = this.getListaEspecifica();
this.store_lista_partidas = this.getListaPartidas();

//<ClavePrimaria>
this.id_proyecto = new Ext.form.Hidden({
	name:'id_proyecto',
	value:this.OBJ.id_proyecto
});
//</ClavePrimaria>

this.verEspecifico = new Ext.Button({
	text:'Ver Partidas',
	id:'verEspecificoS',
	iconCls: 'icon-reporteest',
	handler: function(boton){
		addTab(tabuladorOcho.main.gridPanelEspecifica_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'),'Proyecto '+tabuladorOcho.main.gridPanelEspecifica_.getSelectionModel().getSelected().get('id_proyecto')+', Accion Especifica '+tabuladorOcho.main.gridPanelEspecifica_.getSelectionModel().getSelected().get('tx_codigo'),'formulacion/modulos/seguimiento_proyecto/partida.php','load','icon-reporteest','codigo='+tabuladorOcho.main.gridPanelEspecifica_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'));
	}
});

this.verEspecifico.disable();

//Grid principal
this.gridPanelEspecifica_ = new Ext.grid.GridPanel({
    store: this.store_lista_especifica,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    tbar:[
        this.verEspecifico
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_acc_espec_dist',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_acc_espec_dist'},
    {header: 'id_proyecto',hidden:true, menuDisabled:true,dataIndex: 'id_proyecto'},
    {header: 'co_proyecto_acc_espec',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_acc_espec'},
    {header: 'CÓD.', width:50,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo'},
    {header: 'NOMBRE DE LA ACCIÓN ESPECÍFICA', width:300,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'descripcion'},
    {header: '401', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_401'},
    {header: '402', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_402'},
    {header: '403', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_403'},
    {header: '404', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_404'},
    {header: '405', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_405'},
    {header: '406', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_406'},
    {header: '407', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_407'},
    {header: '408', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_408'},
    {header: '409', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_409'},
    {header: '410', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_410'},
    {header: '411', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_411'},
    {header: '498', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_498'},
    {header: 'Total', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'total'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){tabuladorOcho.main.verEspecifico.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista_especifica,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

Ext.ux.grid.GroupSummary.Calculations['nuMonto'] = function(v, record, field){
	v+=parseFloat(record.data.nu_monto);
	return v;
};
this.summary = new Ext.ux.grid.GroupSummary();

//Grid principal
this.gridPanelDetalle_ = new Ext.grid.GridPanel({
    store: this.store_lista_partidas,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'IMPUTACIÓN PRESUPUESTARIA', width:300,  menuDisabled:true, sortable: true, summaryRenderer: function(v, params, data){return '<b>TOTALES</b>';}, dataIndex: 'tx_partida'},
    {header: 'AÑO <?php echo $id_ejercicio;?>', width:300,  menuDisabled:true, sortable: true, summaryType: 'nuMonto', renderer: formatoNumero, dataIndex: 'nu_monto'},
    {header: 'PROYECTO', summaryType: 'sum',summaryRenderer: function(v, params, data){return 'Total';},autoWidth: true, sortable: true,groupable: false,  dataIndex: 'id_proyecto'},
    ],
    view: new Ext.grid.GroupingView({
        groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Partidas" : "Partida"]})',
        forceFit: true,
        showGroupName: true,
        enableNoGroups: false,
	enableGroupingMenu: false,
        hideGroupedColumn: true
    }),
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    plugins: this.summary,
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista_partidas,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
	this.gridPanelEspecifica_
		]
});

this.fieldset2 = new Ext.form.FieldSet({
	autoWidth:true,
    	title: 'Imputación Presupuestaria de los Egresos del Proyecto por Concepto del Gasto (Bs.)',
        items:[
	this.gridPanelDetalle_
		]
});

this.panelDatos81 = new Ext.Panel({
    title: '8.1. DISTRIBUCIÓN PRESUPUESTARÍA POR ACCIONES ESPECÍFICAS DEL AÑO A FORMULAR (Bs.)',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    autoWidth: true,
    autoScroll:true,
    items:[	
	this.fieldset1,
	this.fieldset2
	]
});

this.panelDatos = new Ext.TabPanel({
    activeTab:0,
    enableTabScroll:true,
    deferredRender: false,
    title: '8. DISTRIBUCIÓN Y PROGRAMACIÓN PRESUPUESTARÍA',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[this.panelDatos81]
});

//Cargar el grid
this.store_lista_especifica.baseParams.paginar = 'si';
this.store_lista_especifica.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista_especifica.load();
this.store_lista_especifica.on('beforeload',function(){
panel_detalle.collapse();
});
this.store_lista_especifica.on('load',function(){
tabuladorOcho.main.verEspecifico.disable();
});
//Cargar el grid
this.store_lista_partidas.baseParams.paginar = 'si';
this.store_lista_partidas.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista_partidas.baseParams.op = 34;
this.store_lista_partidas.load();
this.store_lista_partidas.on('beforeload',function(){
panel_detalle.collapse();
});
this.store_lista_especifica.on('load',function(){
tabuladorOcho.main.verEspecifico.disable();
});
},
getListaEspecifica: function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 33
	},
	fields:[
	{name: 'co_proyecto_acc_espec'},
	{name: 'id_proyecto'},
	{name: 'tx_codigo'},
	{name: 'descripcion'},
	{name: 'monto_401'},
	{name: 'monto_402'},
	{name: 'monto_403'},
	{name: 'monto_404'},
	{name: 'monto_405'},
	{name: 'monto_406'},
	{name: 'monto_407'},
	{name: 'monto_408'},
	{name: 'monto_409'},
	{name: 'monto_410'},
	{name: 'monto_411'},
	{name: 'monto_498'},
	{name: 'total'},
	   ]
	});
    return this.store;
}/*,
getListaPartidas: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/accionDistribucion/funcion.php?op=2',
    root:'data',
    fields:[
    {name: 'id_proyecto'},
    {name: 'tx_partida'},
    {name: 'nu_monto'},
           ]
    });
    return this.store;
}*/,
getListaPartidas: function(){
this.Store = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
            method: 'POST'
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'total'
        },
        [
	    {name: 'id_proyecto'},
	    {name: 'tx_partida'},
	    {name: 'nu_monto'},
        ]),
        sortInfo:{
            field: 'tx_partida',
            direction: "ASC"
        },
        groupField:'id_proyecto'

});
return this.Store;
}
};
Ext.onReady(tabuladorOcho.main.init, tabuladorOcho.main);
</script>
