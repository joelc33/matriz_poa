<?php     
session_start(); 
if( $_SESSION['estatus'] !== 'OK' ) {
    http_response_code(403);
	die();
} 
?>
<script type="text/javascript">
Ext.ns("accionEspLista");
function colorIndicador(v, meta, rec){
	if(rec.get('total') == rec.get('mo_cargado')){
	    return '<span style="color:green;">'+formatoNumero(rec.get('mo_cargado'))+'</span>';
	}else{
	    return '<span style="color:red;">'+formatoNumero(rec.get('mo_cargado'))+'</span>';
	}
return val;
};
accionEspLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();
this.store_lista_2 = this.getLista2();

this.accionFisica1 = new Ext.Button({
	text:'Ver Actividades',
	id:'verFisico',
	iconCls: 'icon-accion_fisica',
	tooltip: 'Ver Actividades de la Accion Especifica',
	handler: function(boton){
		addTab(accionEspLista.main.gridPanel_1.getSelectionModel().getSelected().get('id_proyecto')+'ae','Proyecto:'+accionEspLista.main.gridPanel_1.getSelectionModel().getSelected().get('id_proyecto')+' >  Accion Especifica:'+accionEspLista.main.gridPanel_1.getSelectionModel().getSelected().get('tx_codigo'),'formulacion/modulos/metas/metaLista.php','load','icon-accion_especifica','codigo='+accionEspLista.main.gridPanel_1.getSelectionModel().getSelected().get('co_proyecto_acc_espec')+'&id_proyecto='+accionEspLista.main.gridPanel_1.getSelectionModel().getSelected().get('id_proyecto'));
	}
});
this.cargarPartida = new Ext.Button({
	text: 'Cargar Partidas',
	id: 'cargarPartida',
	iconCls: 'icon-excel',
	handler: function( boton ) {
		var sel = accionEspLista.main.gridPanel_1.getSelectionModel().getSelected();
		accionEspLista.main.mascara.show();
		this.msg = Ext.get('cargar_partidas');
		this.msg.load({
			params:{
				codigo: sel.get('co_proyecto_acc_espec'),
			},
			url:"formulacion/modulos/accionFisica/cargarPartida.php",
			scripts: true,
			text: "Cargando.."
		});
	}
});

this.cargarPartida.disable();
this.accionFisica1.disable();

this.buscador_1 = new Ext.form.TwinTriggerField({
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
	emptyText: 'Campo de Filtro',
	width:350,
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
		accionEspLista.main.store_lista.baseParams={};
		accionEspLista.main.store_lista.baseParams.paginar = 'si';
		accionEspLista.main.store_lista.load();
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
			accionEspLista.main.store_lista.baseParams={}
			accionEspLista.main.store_lista.baseParams.BuscarBy = true;
			accionEspLista.main.store_lista.baseParams[this.paramName] = v;
			accionEspLista.main.store_lista.baseParams.paginar = 'si';
			accionEspLista.main.store_lista.load();
		}
	}
});

//reordenar un registro
this.reordenar_pr_a= new Ext.Button({
    text:'Reordenar Actividades',
    iconCls: 'icon-cambiar',
    handler:function(){
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Reordenar Actividades?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/accionEspecifica/funcion.php?op=13',
            params:{
                co_proyecto_acc_espec:accionEspLista.main.gridPanel_1.getSelectionModel().getSelected().get('co_proyecto_acc_espec')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    accionEspLista.main.store_lista_2.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                accionEspLista.main.mascara.hide();
            }});
	}});
    }
});

this.reordenar_pr_a.disable();

//Grid principal
this.gridPanel_1 = new Ext.grid.GridPanel({
    title: 'PROYECTOS: Acciones Especificas',
    iconCls: 'icon-accion_especifica',
    store: this.store_lista,
    loadMask:true,
border:false,
    autoWidth: true,
    autoHeight:true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.actividad.ver', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.accionFisica1,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.actividad.reodenar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.reordenar_pr_a,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.actividad.cargar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.cargarPartida,'-',
<?php } ?>
	this.buscador_1
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_acc_espec',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_acc_espec'},
    {header: 'id_accion_centralizada',hidden:true, menuDisabled:true,dataIndex: 'id_accion_centralizada'},
    //{header: 'id_proyecto',hidden:true, menuDisabled:true,dataIndex: 'id_proyecto'},
    {header: 'EJECUTOR RESPONSABLE', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'ejecutor_resp'},
    //{header: 'PROYECTO', width:130,  menuDisabled:true, sortable: true, dataIndex: 'id_proyecto'},
    {header: 'PROYECTO', width:300,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nb_proyecto'},
    //{header: 'UNIDAD EJECUTORA RESP.', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'co_ejecutores'},
    {header: 'CÓD.', width:80,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo'},
    {header: 'Nombre de la Acción', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'descripcion'},
    {header: 'UNIDAD DE MEDIDA', width:120,  menuDisabled:true, sortable: false,  dataIndex: 'co_unidades_medida'},
    {header: 'TOTAL GENERAL Bs.', width:120,  menuDisabled:true, sortable: false, renderer: formatoNumero, dataIndex: 'total'},
    {header: 'TOTAL CARGADO', width:120,  menuDisabled:true, sortable: false, renderer: colorIndicador, dataIndex: 'mo_cargado'},
    {header: 'FECHA INICIO', width:100,  menuDisabled:true, sortable: false,  dataIndex: 'fec_inicio'},
    {header: 'FECHA CULMINACIÓN', width:130,  menuDisabled:true, sortable: false,  dataIndex: 'fec_termino'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    viewConfig: {
	forceFit:true,
	enableRowBody:true,
	showPreview:true,
    },
    //listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){accionEspLista.main.accionFisica1.enable();}},
    listeners: {
		cellclick: function(grid, rowIndex, columnIndex, e ) {
			accionEspLista.main.accionFisica1.enable();
			accionEspLista.main.reordenar_pr_a.enable();
			var sel = grid.getSelectionModel().getSelected();
			sel.get('');
			if ( sel.get('cargar') ) {
				accionEspLista.main.cargarPartida.enable();
			} else {
				accionEspLista.main.cargarPartida.disable();
			}
		}
    },
    bbar: new Ext.PagingToolbar({
        pageSize: 15,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.accionFisica2 = new Ext.Button({
	text:'Ver Actividades',
	id:'verFisico2',
	iconCls: 'icon-accion_fisica',
	tooltip: 'Ver Actividades del Proyecto',
	handler: function(boton){
		addTab(accionEspLista.main.gridPanel_2.getSelectionModel().getSelected().get('id_proyecto')+'ae','Programa:'+accionEspLista.main.gridPanel_2.getSelectionModel().getSelected().get('id_proyecto')+' >  Proyecto:'+accionEspLista.main.gridPanel_2.getSelectionModel().getSelected().get('tx_codigo'),'formulacion/modulos/metas/acLista.php','load','icon-accion_especifica','codigo='+accionEspLista.main.gridPanel_2.getSelectionModel().getSelected().get('co_proyecto_acc_espec')+'&id_accion_centralizada='+accionEspLista.main.gridPanel_2.getSelectionModel().getSelected().get('id_accion_centralizada'));
	}
});

this.accionFisica2.disable();

this.buscador_2 = new Ext.form.TwinTriggerField({
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
	emptyText: 'Campo de Filtro',
	width:350,
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
		accionEspLista.main.store_lista_2.baseParams={};
		accionEspLista.main.store_lista_2.baseParams.paginar = 'si';
		accionEspLista.main.store_lista_2.load();
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
			accionEspLista.main.store_lista_2.baseParams={}
			accionEspLista.main.store_lista_2.baseParams.BuscarBy = true;
			accionEspLista.main.store_lista_2.baseParams[this.paramName] = v;
			accionEspLista.main.store_lista_2.baseParams.paginar = 'si';
			accionEspLista.main.store_lista_2.load();
		}
	}
});

//reordenar un registro
this.reordenar_ac_a= new Ext.Button({
    text:'Reordenar Actividades',
    iconCls: 'icon-cambiar',
    handler:function(){
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Reordenar Actividades?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/accionEspecifica/funcion.php?op=12',
            params:{
                id_accion_centralizada:accionEspLista.main.gridPanel_2.getSelectionModel().getSelected().get('id_accion_centralizada'),
		codigo:accionEspLista.main.gridPanel_2.getSelectionModel().getSelected().get('co_proyecto_acc_espec')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    accionEspLista.main.store_lista_2.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                accionEspLista.main.mascara.hide();
            }});
	}});
    }
});

this.reordenar_ac_a.disable();

//Grid principal
this.gridPanel_2 = new Ext.grid.GridPanel({
	title: 'PROGRAMAS: Proyectos',
    iconCls: 'icon-accion_especifica',
    store: this.store_lista_2,
    loadMask:true,
    autoWidth: true,
    autoHeight:true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'ac.ae.actividad.ver', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.accionFisica2,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ac.ae.actividad.reodenar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.reordenar_ac_a,'-',
<?php } ?>
	this.buscador_2
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_acc_espec',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_acc_espec'},
    {header: 'id_accion_centralizada',hidden:true, menuDisabled:true,dataIndex: 'id_accion_centralizada'},
    //{header: 'id_proyecto',hidden:true, menuDisabled:true,dataIndex: 'id_proyecto'},
    {header: 'EJECUTOR RESPONSABLE', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'ejecutor_resp'},
    //{header: 'ACCION C.', width:130,  menuDisabled:true, sortable: true, dataIndex: 'id_proyecto'},
    {header: 'PROGRAMA', width:300,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nb_ac'},
    {header: 'CÓD.', width:80,  menuDisabled:true, sortable: true, textoLargo, dataIndex: 'tx_codigo'},
    {header: 'Nombre del Proyecto', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'descripcion'},
    {header: 'UNIDAD DE MEDIDA', width:120,  menuDisabled:true, sortable: true,  dataIndex: 'co_unidades_medida'},
    {header: 'TOTAL GENERAL Bs.', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'total'},
    {header: 'TOTAL CARGADO', width:120,  menuDisabled:true, sortable: true, renderer: colorIndicador, dataIndex: 'mo_cargado'},
    {header: 'FECHA INICIO', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'fec_inicio'},
    {header: 'FECHA CULMINACIÓN', width:130,  menuDisabled:true, sortable: true,  dataIndex: 'fec_termino'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    viewConfig: {
	forceFit:true,
	enableRowBody:true,
	showPreview:true,
    },
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){accionEspLista.main.accionFisica2.enable();accionEspLista.main.reordenar_ac_a.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 15,
        store: this.store_lista_2,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.panel = new Ext.TabPanel({
	activeTab:0,
	border:false,
	enableTabScroll:true,
	deferredRender: false,
	autoHeight:true,
	autoWidth: true,
	autoScroll:true,
    items:[
//	this.gridPanel_1,
	this.gridPanel_2
	]
});

this.panel.render("consultarAccion");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.BuscarBy = true;
this.store_lista.load();
//Cargar el grid2
this.store_lista_2.baseParams.paginar = 'si';
this.store_lista_2.baseParams.BuscarBy = true;
this.store_lista_2.load();
this.store_lista.on('load',function(){
accionEspLista.main.accionFisica1.disable();
accionEspLista.main.cargarPartida.disable();
accionEspLista.main.reordenar_pr_a.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
this.store_lista_2.on('load',function(){
accionEspLista.main.accionFisica2.disable();
accionEspLista.main.reordenar_ac_a.disable();
});
this.store_lista_2.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/accionEspecifica/funcion.php?op=9',
    root:'data',
    fields:[
    {name: 'co_proyecto_acc_espec'},
    {name: 'id_accion_centralizada'},
    {name: 'id_proyecto'},
    {name: 'nb_proyecto'},
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
    {name: 'ejecutor_resp'},
    {name: 'cargar'},
           ]
    });
    return this.store;
},
getLista2: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/accionEspecifica/funcion.php?op=10',
    root:'data',
    fields:[
    {name: 'co_proyecto_acc_espec'},
    {name: 'id_accion_centralizada'},
    {name: 'id_proyecto'},
    {name: 'nb_ac'},
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
    {name: 'ejecutor_resp'},
           ]
    });
    return this.store;
}
};
Ext.onReady(accionEspLista.main.init, accionEspLista.main);
</script>
<div id="consultarAccion" ></div>
<div id="cargar_partidas" ></div>
