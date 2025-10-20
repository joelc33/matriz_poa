<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}
?>
<script type="text/javascript">
Ext.ns("opcionPlanificador");
function formatoNumero(val){
	    return paqueteComunJS.funcion.getNumeroFormateado(val);
return val;
};
function textoLargo(value, metadata) {
    metadata.attr = 'style="white-space: normal;"';
    return value;
};
opcionPlanificador.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.ver = new Ext.Button({
	text:'Editar Proyecto',
	id:'verProyecto',
	iconCls: 'icon-editar',
	handler: function(boton){
		addTab(opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto'),'Proyecto '+opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto'),'formulacion/modulos/gestionarProyecto/editarProyecto.php','load','icon-buscar','codigo='+opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('co_proyectos'));
		/*var direccionar = Ext.get('contenedoropcionPlanificador');
		direccionar.load({ url: 'formulacion/modulos/proyecto/editarProyecto.php', scripts: true, text: 'Cargando...',              params:'codigo='+opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('co_proyectos')});*/
	}
});

this.ver.disable();

this.buscador = new Ext.form.TwinTriggerField({
	initComponent : function(){
		Ext.ux.form.SearchField.superclass.initComponent.call(this);
		this.on('specialkey', function(f, e){
			if(e.getKey() == e.ENTER){
				this.onTrigger2Click();
			}
		}, this);
	},
	xtype: 'twintriggerfield',
	trigger1Class: 'x-form-clear-trigger',
	trigger2Class: 'x-form-search-trigger',
	enableKeyEvents : true,
	validationEvent:false,
	validateOnBlur:false,
	width:400,
	hasSearch : false,
	paramName : 'variable',
	onTrigger1Click : function() {
		if (this.hiddenField) {
			this.hiddenField.value = '';
		}
		this.setRawValue('');
		this.lastSelectionText = '';
		this.applyEmptyText();
		this.value = '';
		this.fireEvent('clear', this);
		opcionPlanificador.main.store_lista.baseParams={};
		opcionPlanificador.main.store_lista.baseParams.paginar = 'si';
		opcionPlanificador.main.store_lista.load();
	},
	onTrigger2Click : function(){
		var v = this.getRawValue();
		if(v.length < 1){
			    Ext.MessageBox.show({
				       title: 'Notificación',
				       msg: 'Debe ingresar un parametro de busqueda',
				       buttons: Ext.MessageBox.OK,
				       icon: Ext.MessageBox.WARNING
			    });
		}else{
			opcionPlanificador.main.store_lista.baseParams={}
			opcionPlanificador.main.store_lista.baseParams.BuscarBy = true;
			opcionPlanificador.main.store_lista.baseParams[this.paramName] = v;
			opcionPlanificador.main.store_lista.baseParams.paginar = 'si';
			opcionPlanificador.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    title:'Proyectos Activos',
    store: this.store_lista,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    tbar:[
        this.ver,'-',this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyectos',hidden:true, menuDisabled:true,dataIndex: 'co_proyectos'},
    {header: 'Ejecutor', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_ejecutor'},
    {header: 'Codigo', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'id_proyecto'},
    {header: 'Nombre', width:300,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nombre'},
    {header: 'Monto', width:130,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto'},
    {header: 'Monto Registrado', width:130,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_registrado'},
    {header: 'Estatus', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'tx_estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){opcionPlanificador.main.ver.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 10,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.salir = new Ext.Button({
	text:'Atras',
	iconCls:'icon-atras',
	handler:function(){
		var direccionar = Ext.get('contenedoropcionEjecutor');
		direccionar.load({ url: 'formulacion/modulos/actividades/opTranscriptor.php', scripts: true, text: 'Cargando...'});
	}
});

this.panel = new Ext.Panel({
	layout: "fit",
	border: false,
	padding	: 10,
	items: [
		this.gridPanel_
	],
	buttonAlign:'left',
	buttons:[this.salir]
});

this.panel.render("contenedoropcionPlanificador");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
opcionPlanificador.main.ver.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/actividades/funcion.php?op=2',
    root:'data',
    fields:[
    {name: 'co_proyectos'},
    {name: 'id_proyecto'},
    {name: 'nombre'},
    {name: 'tx_ejecutor'},
    {name: 'monto'},
    {name: 'mo_registrado'},
    {name: 'tx_estatus'},
           ]
    });
    return this.store;
}
};
Ext.onReady(opcionPlanificador.main.init, opcionPlanificador.main);
</script>
<div id="contenedoropcionPlanificador"></div>
