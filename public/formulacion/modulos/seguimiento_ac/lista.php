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

Ext.ns("listaAcS");
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
listaAcS.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.verS = new Ext.Button({
	text:'Ver',
	id:'verAcS',
	iconCls: 'icon-buscar',
	handler: function(boton){
		addTab(listaAcS.main.gridPanel_.getSelectionModel().getSelected().get('id'),'Seguimiento -> Accion Centralizada '+listaAcS.main.gridPanel_.getSelectionModel().getSelected().get('id'),'formulacion/modulos/seguimiento_ac/editar.php','load','icon-buscar','codigo='+listaAcS.main.gridPanel_.getSelectionModel().getSelected().get('id'));
	}
});

//Eliminar un registro
this.eliminarProyecto= new Ext.Button({
    text:'Eliminar',
    iconCls: 'icon-eliminar',
    handler:function(){
	var pr = listaAcS.main.gridPanel_.getSelectionModel().getSelected();
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea eliminar el Proyecto: <b>' + pr.get('id_proyecto') + '</b>?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/seguimiento_ac/funcion.php?op=9999',
            params:{
                co_proyectos:pr.get('co_proyectos')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    listaAcS.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                listaAcS.main.mascara.hide();
            }});
	}});
    }
});

this.fichaAc = new Ext.Button({
    text: 'Ver Ficha',
    iconCls: 'icon-reporteest',
    handler: function(){
        var pr = listaAcS.main.gridPanel_.getSelectionModel().getSelected();
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
this.fichaAc.disable();

this.buscador = Ext.create({
	xtype: 'buscador_grid',
	store: listaAcS.main.store_lista
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    title:'Seguimiento - Acciones Centralizadas',
    store: this.store_lista,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    tbar:[
        this.verS, this.fichaAc, '-',this.buscador,'->',this.eliminarProyecto
    ],
    columns: [
    new Ext.grid.RowNumberer(),
{header: 'id', hidden:true, menuDisabled:true, dataIndex: 'id'},
{header: 'Ejecutor', width: 200,  menuDisabled:true, sortable: true, renderer: colorTexto, dataIndex: 'tx_ejecutor'},
{header: 'Codigo', width: 130,  menuDisabled:true, sortable: true, renderer: colorTexto, dataIndex: 'codigo'},
{header: 'Nombre', width: 300,  menuDisabled:true, sortable: true, renderer: colorTexto, dataIndex: 'nombre'},
{header: 'Monto', width: 130,  menuDisabled:true, sortable: true, renderer: colorMonto, dataIndex: 'monto'},
{header: 'Monto Registrado', width: 130,  menuDisabled: true, sortable: true, renderer: colorMonto, dataIndex: 'monto_calc'},
{header: 'Estatus', width: 100,  menuDisabled: true, sortable: true, renderer: colorTexto, dataIndex: 'tx_estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
        listaAcS.main.verS.enable();
        listaAcS.main.fichaAc.enable();
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

this.panel.render("listaAcSSeguimiento");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
	listaAcS.main.verS.disable();
});
this.store_lista.on('beforeload',function(){
	panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/seguimiento_ac/funcion.php',
    root:'data',
    baseParams: {
	op: 1
    },
    fields:[
	{name: 'id'},
	{name: 'codigo'},
	{name: 'nombre'},
	{name: 'tx_ejecutor'},
	{name: 'monto'},
	{name: 'monto_calc'},
	{name: 'id_estatus'},
	{name: 'tx_estatus'},
	{name: 'reabrir'},
	{name: 'eliminar'},
    	{name: 'id_tab_tipo_registro'},
           ]
    });
    return this.store;
}
};
Ext.onReady(listaAcS.main.init, listaAcS.main);
</script>
<div id="listaAcSSeguimiento"></div>
