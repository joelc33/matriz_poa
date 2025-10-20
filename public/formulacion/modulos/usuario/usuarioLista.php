<?php     
session_start(); 
if( $_SESSION['estatus'] !== 'OK' ) {
    http_response_code(403);
	die();
}
?>
<script type="text/javascript">
Ext.ns("usuarioLista");
function change(val){
	if(val=="t"){
	    return '<span style="color:green;">Activo</span>';
	}else if(val=="f"){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
usuarioLista.main = {
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
        usuarioLista.main.mascara.show();
        this.msg = Ext.get('formulariousuario');
        this.msg.load({
         url:"formulacion/modulos/usuario/editarUsuario.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Editar un registro
this.editar= new Ext.Button({
    text:'Editar Usuario',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_usuario');
	usuarioLista.main.mascara.show();
        this.msg = Ext.get('formulariousuario');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/usuario/editarUsuario.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Resetear un registro
this.resetear= new Ext.Button({
    text:'Resetear Clave',
    iconCls: 'icon-cambio',
    handler:function(){
	this.codigo  = usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_usuario');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea resetear clave?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/usuario/funcion.php?op=9',
            params:{
                co_usuario:usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_usuario')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    usuarioLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                usuarioLista.main.mascara.hide();
            }});
	}});
    }
});

//Desabilitar un registro
this.deshabilitar= new Ext.Button({
    text:'Desabilitar Usuario',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_usuario');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea desabilitar usuario?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/usuario/funcion.php?op=10',
            params:{
                co_usuario:usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_usuario')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    usuarioLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                usuarioLista.main.mascara.hide();
            }});
	}});
    }
});

this.editar.disable();
this.resetear.disable();
this.deshabilitar.disable();

//filtro
this.filtro = new Ext.Button({
    text:'Filtro',
    iconCls: 'icon-buscar',
    handler:function(){
        this.msg = Ext.get('filtrousuario');
        usuarioLista.main.mascara.show();
        usuarioLista.main.filtro.setDisabled(true);
        this.msg.load({
             url: 'usuario/filtroUsuario.php',
             scripts: true
        });
    }
});

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
		usuarioLista.main.store_lista.baseParams={};
		usuarioLista.main.store_lista.baseParams.paginar = 'si';
		usuarioLista.main.store_lista.load();
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
			usuarioLista.main.store_lista.baseParams={}
			usuarioLista.main.store_lista.baseParams.BuscarBy = true;
			usuarioLista.main.store_lista.baseParams[this.paramName] = v;
			usuarioLista.main.store_lista.baseParams.paginar = 'si';
			usuarioLista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    //title:'Lista de usuario',
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
//    frame:true,
//    height:550,
    autoWidth: true,
    autoHeight:true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'usuarios.nuevo', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.nuevo,'-',
<?php } ?>
	this.buscador,'-',
<?php if( in_array( array( 'de_privilegio' => 'usuarios.editar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.editar,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'usuarios.resetear', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.resetear,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'usuarios.deshabilitar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.deshabilitar
<?php } ?>
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_usuario',hidden:true, menuDisabled:true,dataIndex: 'co_usuario'},
    {header: 'Cedula', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'nu_cedula'},
    {header: 'Nombre', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'nb_funcionario'},
    {header: 'Apellido', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'ap_funcionario'},
    {header: 'Unidad Ejecutora', width:300,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_ejecutor'},
    {header: 'Login', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'tx_login'},
    {header: 'Perfil', width:150,  menuDisabled:true, sortable: true,  dataIndex: 'co_rol'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'edo_reg'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){usuarioLista.main.editar.enable();usuarioLista.main.resetear.enable();usuarioLista.main.deshabilitar.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorusuarioLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
usuarioLista.main.editar.disable();
usuarioLista.main.resetear.disable();
usuarioLista.main.deshabilitar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/usuario/funcion.php?op=2',
    root:'data',
    fields:[
    {name: 'co_usuario'},
    {name: 'nb_funcionario'},
    {name: 'ap_funcionario'},
    {name: 'nu_cedula'},
    {name: 'tx_login'},
    {name: 'co_rol'},
    {name: 'edo_reg'},
    {name: 'tx_ejecutor'},
           ]
    });
    return this.store;
}
};
Ext.onReady(usuarioLista.main.init, usuarioLista.main);
</script>
<div id="contenedorusuarioLista"></div>
<div id="formulariousuario"></div>
<div id="filtrousuario"></div>
