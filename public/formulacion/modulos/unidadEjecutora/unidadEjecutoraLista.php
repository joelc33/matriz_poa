<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}
?>
<script type="text/javascript">
Ext.ns("unidadEjecutoraLista");
function change(val){
	if(val=="t"){
	    return '<span style="color:green;">Activo</span>';
	}else if(val=="f"){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
unidadEjecutoraLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

//Agregar un registro
this.nuevo = new Ext.Button({
    text:'Nuevo',
    iconCls: 'icon-nuevo',
    handler:function(){
        unidadEjecutoraLista.main.mascara.show();
        this.msg = Ext.get('formulariounidadEjecutora');
        this.msg.load({
         url:"formulacion/modulos/unidadEjecutora/editarUnidadEjecutora.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Editar un registro
this.editar= new Ext.Button({
    text:'Editar',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = unidadEjecutoraLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejecutores');
	unidadEjecutoraLista.main.mascara.show();
        this.msg = Ext.get('formulariounidadEjecutora');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/unidadEjecutora/editarUnidadEjecutora.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Habilitar un registro
this.habilitar= new Ext.Button({
    text:'Habilitar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = unidadEjecutoraLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejecutores');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Habilitar Unidad Ejecutora?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/unidadEjecutora/funcion.php?op=6',
            params:{
                co_ejecutores:unidadEjecutoraLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejecutores')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    unidadEjecutoraLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                unidadEjecutoraLista.main.mascara.hide();
            }});
	}});
    }
});

//Desabilitar un registro
this.deshabilitar= new Ext.Button({
    text:'Desabilitar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = unidadEjecutoraLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejecutores');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea desabilitar Unidad Ejecutora?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/unidadEjecutora/funcion.php?op=5',
            params:{
                co_ejecutores:unidadEjecutoraLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejecutores')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    unidadEjecutoraLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                unidadEjecutoraLista.main.mascara.hide();
            }});
	}});
    }
});

this.editar.disable();
this.deshabilitar.disable();
this.habilitar.disable();

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
		unidadEjecutoraLista.main.store_lista.baseParams={};
		unidadEjecutoraLista.main.store_lista.baseParams.paginar = 'si';
		unidadEjecutoraLista.main.store_lista.load();
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
			unidadEjecutoraLista.main.store_lista.baseParams={}
			unidadEjecutoraLista.main.store_lista.baseParams.BuscarBy = true;
			unidadEjecutoraLista.main.store_lista.baseParams[this.paramName] = v;
			unidadEjecutoraLista.main.store_lista.baseParams.paginar = 'si';
			unidadEjecutoraLista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    //title:'Lista de unidadEjecutora',
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
//    frame:true,
//    height:550,
    autoWidth: true,
    autoHeight:true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'ejecutor.nuevo', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.nuevo,'-',
<?php } ?>
	this.buscador,'-',
<?php if( in_array( array( 'de_privilegio' => 'ejecutor.editar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.editar,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ejecutor.habilitar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.habilitar,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ejecutor.deshabilitar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.deshabilitar
<?php } ?>
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_ejecutores',hidden:true, menuDisabled:true,dataIndex: 'co_ejecutores'},
    {header: 'Codigo', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'id_ejecutor'},
    {header: 'Descripcion', width:400,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_ejecutor'},
    {header: 'Correo Institucional', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_correo'},
    {header: 'telefono', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_telefono'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'edo_reg'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){unidadEjecutoraLista.main.editar.enable();unidadEjecutoraLista.main.habilitar.enable();unidadEjecutoraLista.main.deshabilitar.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorunidadEjecutoraLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
unidadEjecutoraLista.main.editar.disable();
unidadEjecutoraLista.main.habilitar.disable();
unidadEjecutoraLista.main.deshabilitar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/unidadEjecutora/funcion.php?op=1',
    root:'data',
    fields:[
    {name: 'co_ejecutores'},
    {name: 'id_ejecutor'},
    {name: 'tx_ejecutor'},
    {name: 'car_01'},
    {name: 'car_02'},
    {name: 'car_03'},
    {name: 'car_04'},
    {name: 'co_tipo_ejecutor'},
    {name: 'id_ambito_ejecutor'},
    {name: 'codigo_01'},
    {name: 'codigo_eje'},
    {name: 'edo_reg'},
    {name: 'de_correo'},
    {name: 'de_telefono'},
           ]
    });
    return this.store;
}
};
Ext.onReady(unidadEjecutoraLista.main.init, unidadEjecutoraLista.main);
</script>
<div id="contenedorunidadEjecutoraLista"></div>
<div id="formulariounidadEjecutora"></div>
