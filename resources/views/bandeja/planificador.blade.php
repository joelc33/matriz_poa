<script type="text/javascript">
Ext.ns("Comun");
Ext.define('Comun.BuscadorGrid', {
	extend: 'Ext.form.TwinTriggerField',
	xtype: 'buscador_grid',
	constructor: function(cfg) {
		cfg = Ext.apply({
			trigger1Class: 'x-form-clear-trigger',
			trigger2Class: 'x-form-search-trigger',
			enableKeyEvents: true,
			validationEvent: false,
			validateOnBlur: false,
			hasSearch: false,
			paramName: 'variable',
			width: 400
		}, cfg);
		this.superclass().constructor.call(this, cfg);
	},
	initComponent: function(){
		this.superclass().initComponent.call(this);
		this.on('specialkey', function(f, e){
			if(e.getKey() == e.ENTER){
				this.onTrigger2Click();
			}
		}, this);
	},
	onTrigger1Click: function() {
		if (this.hiddenField) {
			this.hiddenField.value = '';
		}
		this.setRawValue('');
		this.lastSelectionText = '';
		this.applyEmptyText();
		this.value = '';
		this.fireEvent('clear', this);
		this.store.baseParams = this.baseParams || this.store.baseParams;
		this.store.load();
	},
	onTrigger2Click: function(){
		var v = this.getRawValue();
		if (v.length < 1){
			Ext.MessageBox.show({
				title: 'Notificación',
				msg: 'Debe ingresar un parámetro de búsqueda',
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.WARNING
			});
		} else {
			this.baseParams = this.baseParams || this.store.baseParams;
			var nuevosParams = Ext.apply({}, {
				BuscarBy: true,
				paginar: 'si'
			}, this.baseParams);
			nuevosParams[this.paramName] = v;
			this.store.baseParams = nuevosParams;
			this.store.load();
		}
	}
});

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

this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

this.store_lista = this.getLista();

this.ver = new Ext.Button({
	text:'Ver',
	id:'verProyecto',
	iconCls: 'icon-buscar',
	handler: function(boton){
		addTab(opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto'),'Proyecto '+opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto'),'formulacion/modulos/proyecto/editarProyecto.php','load','icon-buscar','codigo='+opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('co_proyectos'));
	}
});

this.eliminarProyecto= new Ext.Button({
    text:'Eliminar',
    iconCls: 'icon-eliminar',
    handler:function(){
	var pr = opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected();
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea eliminar el Proyecto: <b>' + pr.get('id_proyecto') + '</b>?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/proyecto/funcion.php?op=9999',
            params:{
                co_proyectos:pr.get('co_proyectos')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    opcionPlanificador.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                opcionPlanificador.main.mascara.hide();
            }});
	}});
    }
});

this.fichaProyecto = new Ext.Button({
    text: 'Ver Ficha',
    iconCls: 'icon-reporteest',
    handler: function(){
        var pr = opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected();
        bajar.load({
            url: 'formulacion/modulos/reportes/ver.php',
            params: {
                r: 'ficha',
                id_proyecto: pr.get('id_proyecto'),
                id_ejercicio: pr.get('id_ejercicio')
            }
        });
    }
});

this.reabrirProyecto = new Ext.Button({
	text: 'Reabrir',
	id: 'reabrirProyecto',
	iconCls: 'icon-buscar',
	handler: function( boton ) {
		var sel = opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected();
		Ext.Ajax.request({
			method: 'POST',
			/*url: 'formulacion/modulos/proyecto/funcion.php?op=8888',*/
			url:'{{ URL::to('proyecto/abrir') }}',
			params: {
				/*op: 8888,*/
				_token: '{{ csrf_token() }}',
				proyecto: sel.get('co_proyectos'),
			},
			success: function(result) {
				var obj = Ext.util.JSON.decode(result.responseText);
				if (obj.success) {
					opcionPlanificador.main.store_lista.reload();
				} else {
					Ext.Msg.alert('Error', obj.msg);
				}
			},
			failure: function() {
				Ext.Msg.alert("Ocurrió un error contactando al servidor");
			}
		});
	}
});
this.reabrirProyecto.disable();

this.accionFisica = new Ext.Button({
	text:'Acciones Especificas',
	id:'veraccionFisica',
	iconCls: 'icon-accion_especifica',
	handler: function(boton){
		addTab(opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto')+'ae','Acciones Especificas: Proyecto '+opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto'),'formulacion/modulos/accionEspecifica/accionLista.php','load','icon-accion_especifica','codigo='+opcionPlanificador.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto'));
	}
});

this.ver.disable();
this.accionFisica.disable();
this.eliminarProyecto.disable();
this.fichaProyecto.disable();

this.buscador = Ext.create({
	xtype: 'buscador_grid',
	store: opcionPlanificador.main.store_lista
});

this.gridPanel_ = new Ext.grid.GridPanel({
    title:'Proyectos Activos',
    store: this.store_lista,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    tbar:[
      @if( in_array( array( 'de_privilegio' => 'proyecto.ver', 'in_habilitado' => true), Session::get('credencial') ))
        this.ver,'-',
      @endif
      @if( in_array( array( 'de_privilegio' => 'proyecto.ficha', 'in_habilitado' => true), Session::get('credencial') ))
        this.fichaProyecto,'-',
      @endif
      @if( in_array( array( 'de_privilegio' => 'proyecto.reabrir', 'in_habilitado' => true), Session::get('credencial') ))
        this.reabrirProyecto,'-',
      @endif
      @if( in_array( array( 'de_privilegio' => 'proyecto.eliminar', 'in_habilitado' => true), Session::get('credencial') ))
        this.eliminarProyecto,'-',
      @endif
        this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyectos',hidden:true, menuDisabled:true,dataIndex: 'co_proyectos'},
    {header: 'id_ejercicio',hidden:true, menuDisabled:true,dataIndex: 'id_ejercicio'},
    {header: 'Ejecutor', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_ejecutor'},
    {header: 'Codigo', width:130,  menuDisabled:true, sortable: true,  dataIndex: 'id_proyecto'},
    {header: 'Nombre', width:300,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nombre'},
    {header: 'Monto', width:130,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto'},
    {header: 'Monto Registrado', width:130,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_registrado'},
    {header: 'Estatus', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'tx_estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    viewConfig: {
	forceFit:true,
	enableRowBody:true,
	showPreview:true,
    },
    listeners: {
		cellclick: function(grid, rowIndex, columnIndex, e ) {
			opcionPlanificador.main.ver.enable();
			opcionPlanificador.main.fichaProyecto.enable();
			var sel = grid.getSelectionModel().getSelected();
			sel.get('');
			if ( sel.get('eliminar') ) {
				opcionPlanificador.main.eliminarProyecto.enable();
			} else {
				opcionPlanificador.main.eliminarProyecto.disable();
			}
			if ( sel.get('reabrir') ) {
				opcionPlanificador.main.reabrirProyecto.enable();
			} else {
				opcionPlanificador.main.reabrirProyecto.disable();
			}
		}
    },
    bbar: new Ext.PagingToolbar({
        pageSize: 10,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.store_acciones = this.getAcciones();

var eliminarCentralizadas = new Ext.Button({
	text: 'Eliminar',
	id: 'eliminarAccion',
	iconCls: 'icon-eliminar',
	handler: function( boton ) {
		var r = opcionPlanificador.main.gridCentralizadas_.getSelectionModel().getSelected();
		Ext.MessageBox.confirm('Confirmación',
			'¿Realmente desea eliminar la AC "' + r.get('codigo') + '?',
			function(boton) {
				if (boton == 'yes') {
					Ext.Ajax.request({
						method: 'POST',
						url: 'formulacion/modulos/accionCentralizada/funcion.php',
						params: {
							op: 98,
							id_accion_centralizada: r.get('id')
						},
						success: function(result, request) {
							obj = Ext.util.JSON.decode(result.responseText);
							if (obj.success) {
								opcionPlanificador.main.store_acciones.reload();
							}
							Ext.Msg.alert("Notificación", obj.msg);
						},
						failure: function() {
							Ext.Msg.alert("Ocurrió un error contactando al servidor");
						}
					});
				}
			}
		);
	}
});

var verCentralizadas = new Ext.Button({
	text: 'Ver',
	id: 'verAccion',
	iconCls: 'icon-buscar',
	handler: function( boton ) {
		var self = this;
		var sel = opcionPlanificador.main.gridCentralizadas_.getSelectionModel().getSelected();
		addTab(
			sel.get('codigo'),
			'Acción Centralizada '+ sel.get('codigo'),
			'formulacion/modulos/accionCentralizada/accion.php',
			'load',
			'icon-buscar',
			'id=' + sel.get('id')
		);
	}
});


var reabrirCentralizadas = new Ext.Button({
	text: 'Reabrir',
	id: 'reabrirAccion',
	iconCls: 'icon-buscar',
	handler: function( boton ) {
		var sel = opcionPlanificador.main.gridCentralizadas_.getSelectionModel().getSelected();
		Ext.Ajax.request({
			method: 'POST',
			url: 'formulacion/modulos/accionCentralizada/funcion.php',
			params: {
				op: 97,
				id_accion_centralizada: sel.get('id'),
			},
			success: function(result) {
				var obj = Ext.util.JSON.decode(result.responseText);
				if (obj.success) {
					opcionPlanificador.main.store_acciones.reload();
				} else {
					Ext.Msg.alert('Error', obj.msg);
				}
			},
			failure: function() {
				Ext.Msg.alert("Ocurrió un error contactando al servidor");
			}
		});
	}
});
reabrirCentralizadas.disable();

verCentralizadas.disable();
eliminarCentralizadas.disable();

this.paginaCentralizadas = 10;

this.store_acciones.baseParams = {
	paginar: 'si',
	start: 0,
	limit: this.paginaCentralizadas
};

this.gridCentralizadas_ = new Ext.grid.GridPanel({
	title:'Acciones Centralizadas Activas',
	store: this.store_acciones,
	loadMask: true,
	border: true,
	autoHeight: true,
	autoWidth: true,
	tbar:[
    @if( in_array( array( 'de_privilegio' => 'ac.ver', 'in_habilitado' => true), Session::get('credencial') ))
      verCentralizadas,'-',
    @endif
    @if( in_array( array( 'de_privilegio' => 'ac.reabrir', 'in_habilitado' => true), Session::get('credencial') ))
      reabrirCentralizadas,'-',
    @endif
    @if( in_array( array( 'de_privilegio' => 'ac.eliminar', 'in_habilitado' => true), Session::get('credencial') ))
      eliminarCentralizadas,'-',
    @endif
    { xtype: 'buscador_grid', store: this.store_acciones	}
	],
	columns: [
		new Ext.grid.RowNumberer(),
		{header: 'id', hidden:true, menuDisabled:true, dataIndex: 'id'},
		{header: 'Ejecutor', width: 200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_ejecutor'},
		{header: 'Codigo', width: 130,  menuDisabled:true, sortable: true, dataIndex: 'codigo'},
		{header: 'Nombre', width: 300,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nombre'},
		{header: 'Monto', width: 130,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto'},
		{header: 'Monto Registrado', width: 130,  menuDisabled: true, sortable: true, renderer: formatoNumero, dataIndex: 'monto_calc'},
		{header: 'Estatus', width: 100,  menuDisabled: true, sortable: true, dataIndex: 'tx_estatus'},
	],
	stripeRows: true,
	autoScroll:true,
	stateful: true,
	listeners: {
		cellclick: function(grid, rowIndex, columnIndex, e ) {
			verCentralizadas.enable();
			var sel = grid.getSelectionModel().getSelected();
			sel.get('');
			if ( sel.get('eliminar') ) {
				eliminarCentralizadas.enable();
			} else {
				eliminarCentralizadas.disable();
			}
			if ( sel.get('reabrir') ) {
				reabrirCentralizadas.enable();
			} else {
				reabrirCentralizadas.disable();
			}
		}
	},
	bbar: new Ext.PagingToolbar({
		pageSize: this.paginaCentralizadas,
		store: this.store_acciones,
		displayInfo: true,
		displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
		emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
	})
});

this.panel = new Ext.Panel({
	layout: "fit",
	border: false,
	padding: 5,
	items: [
		this.gridPanel_,
		{ height: 20 },
		this.gridCentralizadas_
	]
});

this.panel.render("contenedoropcionPlanificador");

this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
opcionPlanificador.main.ver.disable();
opcionPlanificador.main.accionFisica.disable();
opcionPlanificador.main.reabrirProyecto.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});

this.store_acciones.load();
this.store_acciones.on('load',function(){
	verCentralizadas.disable();
	eliminarCentralizadas.disable();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
		url:'{{ URL::to('proyecto/storeLista') }}',
    root:'data',
    fields:[
	{name: 'co_proyectos'},
	{name: 'id_ejercicio'},
	{name: 'id_proyecto'},
	{name: 'nombre'},
	{name: 'tx_ejecutor'},
	{name: 'monto'},
	{name: 'mo_registrado'},
	{name: 'tx_estatus'},
	{name: 'reabrir'},
	{name: 'eliminar'}
           ]
    });
    return this.store;
},
getAcciones: function(){
	var store = new Ext.data.JsonStore({
		url:'{{ URL::to('ac/storeLista') }}',
		root:'data',
		fields: [
			{name: 'id'},
			{name: 'codigo'},
			{name: 'nombre'},
			{name: 'tx_ejecutor'},
			{name: 'monto'},
			{name: 'monto_calc'},
			{name: 'id_estatus'},
			{name: 'tx_estatus'},
			{name: 'reabrir'},
			{name: 'eliminar'}
		]
	});
	return store;
}
};
Ext.onReady(opcionPlanificador.main.init, opcionPlanificador.main);
</script>
<div id="contenedoropcionPlanificador"></div>
