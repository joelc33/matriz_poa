<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}
?>
<script type="text/javascript">
Ext.ns("partidaLista");
function change(val){
	if(val=="t"){
	    return '<span style="color:green;">Activo</span>';
	}else if(val=="f"){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
function movimiento(val){
	if(val=="t"){
	    return '<span style="color:green;">Si</span>';
	}else if(val=="f"){
	    return '<span style="color:red;">No</span>';
	}
return val;
};
partidaLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.habilitar= new Ext.Button({
    text:'Habilitar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = partidaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_partida');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea habilitar?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/partidas/funcion.php?op=3',
            params:{
                co_partida:partidaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_partida')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    partidaLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                partidaLista.main.mascara.hide();
            }});
	}});
    }
});

//Desabilitar un registro
this.deshabilitar= new Ext.Button({
    text:'Desabilitar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = partidaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_partida');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea desabilitar partida?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/partidas/funcion.php?op=4',
            params:{
                co_partida:partidaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_partida')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    partidaLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                partidaLista.main.mascara.hide();
            }});
	}});
    }
});

this.habilitar.disable();
this.deshabilitar.disable();

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
		partidaLista.main.store_lista.baseParams={};
		partidaLista.main.store_lista.baseParams.paginar = 'si';
		partidaLista.main.store_lista.load();
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
			partidaLista.main.store_lista.baseParams={}
			partidaLista.main.store_lista.baseParams.BuscarBy = true;
			partidaLista.main.store_lista.baseParams[this.paramName] = v;
			partidaLista.main.store_lista.baseParams.paginar = 'si';
			partidaLista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    //title:'Lista de plan',
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
//    frame:true,
//    height:550,
    autoWidth: true,
    autoHeight:true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'partidas.habilitar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.habilitar,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'partidas.deshabilitar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.deshabilitar,'-',
<?php } ?>
	this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_partida',hidden:true, menuDisabled:true,dataIndex: 'co_partida'},
    {header: 'Ejercicio Fiscal', width:100,  menuDisabled:true, sortable: true, dataIndex: 'co_ejercicio_fiscal'},
    {header: 'Codigo', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'co_partida'},
    {header: 'Descripcion', width:600,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_nombre'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'edo_reg'},
    {header: 'Movimiento', width:80,  menuDisabled:true, sortable: true, renderer: movimiento, dataIndex: 'ace_mov'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){partidaLista.main.habilitar.enable();partidaLista.main.deshabilitar.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorpartidaLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
partidaLista.main.habilitar.disable();
partidaLista.main.deshabilitar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/partidas/funcion.php?op=1',
    root:'data',
    fields:[
    {name: 'co_ejercicio_fiscal'},
    {name: 'co_partida'},
    {name: 'tx_nombre'},
    {name: 'edo_reg'},
    {name: 'ace_mov'},
           ]
    });
    return this.store;
}
};
Ext.onReady(partidaLista.main.init, partidaLista.main);
</script>
<div id="contenedorpartidaLista"></div>
<div id="formularioPartida"></div>
