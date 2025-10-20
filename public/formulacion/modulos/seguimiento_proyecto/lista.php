<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}
?>
<script type="text/javascript">
Ext.ns("ComunS");
Ext.define('ComunS.BuscadorGrid', {
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

Ext.ns("listaProyectoS");
function formatoNumero(val){
	return paqueteComunJS.funcion.getNumeroFormateado(val);
	return val;
};
function textoLargo(value, metadata) {
	metadata.attr = 'style="white-space: normal;"';
	return value;
};
function colorMonto(v, meta, rec){
	if(rec.get('id_tab_tipo_registro') == 1){
	    return '<span style="color:green;">'+formatoNumero(v)+'</span>';
	}else{
	    return '<span style="color:red;">'+formatoNumero(v)+'</span>';
	}
return val;
};
function colorTexto(v, meta, rec){
	if(rec.get('id_tab_tipo_registro') == 1){
	    return '<span style="color:green; white-space: normal;">'+v+'</span>';
	}else{
	    return '<span style="color:red; white-space: normal;">'+v+'</span>';
	}
return val;
};
listaProyectoS.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.verS = new Ext.Button({
	text:'Ver Proyecto',
	id:'verProyectoS',
	iconCls: 'icon-buscar',
	handler: function(boton){
		addTab(listaProyectoS.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto'),'Seguimiento -> Proyecto '+listaProyectoS.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto'),'formulacion/modulos/seguimiento_proyecto/editar.php','load','icon-buscar','codigo='+listaProyectoS.main.gridPanel_.getSelectionModel().getSelected().get('co_proyectos'));
	}
});

//Eliminar un registro
this.eliminarProyecto= new Ext.Button({
    text:'Eliminar',
    iconCls: 'icon-eliminar',
    handler:function(){
	var pr = listaProyectoS.main.gridPanel_.getSelectionModel().getSelected();
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea eliminar el Proyecto: <b>' + pr.get('id_proyecto') + '</b>?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/seguimiento_proyecto/funcion.php?op=9999',
            params:{
                co_proyectos:pr.get('co_proyectos')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    listaProyectoS.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                listaProyectoS.main.mascara.hide();
            }});
	}});
    }
});

this.fichaProyecto = new Ext.Button({
    text: 'Ver Ficha',
    iconCls: 'icon-reporteest',
    handler: function(){
        var pr = listaProyectoS.main.gridPanel_.getSelectionModel().getSelected();
        bajar.load({
            url: 'formulacion/modulos/reportes/ver.php',
            params: {
                r: 'ficha',
                id_proyecto: pr.get('id_proyecto')
            }
        });
    }
});

this.verS.disable();
this.eliminarProyecto.disable();
this.fichaProyecto.disable();

this.buscador = Ext.create({
	xtype: 'buscador_grid',
	store: listaProyectoS.main.store_lista
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    title:'Seguimiento - Proyectos',
    store: this.store_lista,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    tbar:[
        this.verS, this.fichaProyecto, '-',this.buscador,'->',this.eliminarProyecto
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyectos',hidden:true, menuDisabled:true, dataIndex: 'co_proyectos'},
    {header: 'id_tab_tipo_registro',hidden:true, menuDisabled:true, dataIndex: 'id_tab_tipo_registro'},
    {header: 'Ejecutor', width:200,  menuDisabled:true, sortable: true, renderer: colorTexto, dataIndex: 'tx_ejecutor'},
    {header: 'Codigo', width:100,  menuDisabled:true, sortable: true, renderer: colorTexto, dataIndex: 'id_proyecto'},
    {header: 'Nombre', width:300,  menuDisabled:true, sortable: true, renderer: colorTexto, dataIndex: 'nombre'},
    {header: 'Monto', width:130,  menuDisabled:true, sortable: true, renderer: colorMonto, dataIndex: 'monto'},
    {header: 'Monto Registrado', width:130,  menuDisabled:true, sortable: true, renderer: colorMonto, dataIndex: 'mo_registrado'},
    {header: 'Estatus', width:100,  menuDisabled:true, sortable: true, renderer: colorTexto, dataIndex: 'tx_estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
        listaProyectoS.main.verS.enable();
        //listaProyectoS.main.eliminarProyecto.enable();
        listaProyectoS.main.fichaProyecto.enable();
    }},
    bbar: new Ext.PagingToolbar({
        pageSize: 10,
        store: this.store_lista,
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
		this.gridPanel_
	]
});

this.panel.render("listaProyectoSSeguimiento");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
	listaProyectoS.main.verS.disable();
});
this.store_lista.on('beforeload',function(){
	panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
    root:'data',
    baseParams: {
	op: 1
    },
    fields:[
    {name: 'co_proyectos'},
    {name: 'id_proyecto'},
    {name: 'nombre'},
    {name: 'tx_ejecutor'},
    {name: 'monto'},
    {name: 'mo_registrado'},
    {name: 'tx_estatus'},
    {name: 'id_tab_tipo_registro'},
           ]
    });
    return this.store;
}
};
Ext.onReady(listaProyectoS.main.init, listaProyectoS.main);
</script>
<div id="listaProyectoSSeguimiento"></div>
