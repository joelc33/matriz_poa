<script type="text/javascript">
Ext.ns("usuarioLista");
function change(val){
	if(val==true){
	    return '<span style="color:green;">Activo</span>';
	}else if(val==false){
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
         url:"{{ URL::to('usuario/nuevo') }}",
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
	this.codigo  = usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	usuarioLista.main.mascara.show();
        this.msg = Ext.get('formulariousuario');
        this.msg.load({
         url:"{{ URL::to('usuario/editar') }}/"+this.codigo,
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Desabilitar un registro
this.eliminar= new Ext.Button({
    text:'Deshabilitar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Deshabilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('usuario/deshabilitar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
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
/*this.resetear= new Ext.Button({
  text:'Resetear Clave',
  iconCls: 'icon-cambio',
    handler:function(){
	this.codigo  = usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Resetear clave?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('usuario/resetear') }}',
            params:{
								_token: '{{ csrf_token() }}',
                id: usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
							obj = Ext.util.JSON.decode(result.responseText);
							if(obj.success==true){
								usuarioLista.main.store_lista.load();
								Ext.Msg.alert("Notificación",obj.msg);
							}else{
								var errores = '';
								for(datos in obj.msg){
									errores += obj.msg[datos] + '<br>';
								}
								Ext.Msg.alert("Notificación", errores);
							}
              usuarioLista.main.mascara.hide();
            }});
	}});
    }
});*/

//Editar un registro
this.resetear= new Ext.Button({
    text:'Resetear Clave',
    iconCls: 'icon-cambio',
    handler:function(){
	this.codigo  = usuarioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	usuarioLista.main.mascara.show();
        this.msg = Ext.get('formulariousuario');
        this.msg.load({
         url:"{{ URL::to('usuario/cambiar/clave') }}/"+this.codigo,
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.editar.disable();
this.eliminar.disable();
this.resetear.disable();

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
		usuarioLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
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
			usuarioLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			usuarioLista.main.store_lista.baseParams[this.paramName] = v;
			usuarioLista.main.store_lista.baseParams.paginar = 'si';
			usuarioLista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    iconCls: 'icon-libro',
    store: this.store_lista,
    border:false,
    loadMask:true,
    autoWidth: true,
    autoHeight:true,
    tbar:[
			@if( in_array( array( 'de_privilegio' => 'usuarios.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'usuarios.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'usuarios.deshabilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
      @if( in_array( array( 'de_privilegio' => 'usuarios.resetear', 'in_habilitado' => true), Session::get('credencial') ))
        this.resetear,'-',
      @endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'co_usuario',hidden:true, menuDisabled:true,dataIndex: 'co_usuario'},
    {header: 'Cedula', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'nu_cedula'},
    {header: 'Nombre', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'nb_funcionario'},
    {header: 'Apellido', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'ap_funcionario'},
    {header: 'Unidad Ejecutora', width:300,  menuDisabled:true, sortable: true, /*renderer: textoLargo,*/ dataIndex: 'tx_ejecutor'},
    {header: 'Login', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'da_login'},
    {header: 'Perfil', width:150,  menuDisabled:true, sortable: true,  dataIndex: 'de_rol'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
      usuarioLista.main.editar.enable();
      usuarioLista.main.eliminar.enable();
      usuarioLista.main.resetear.enable();
    }},
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
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
usuarioLista.main.editar.disable();
usuarioLista.main.eliminar.disable();
usuarioLista.main.resetear.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('usuario/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'nb_funcionario'},
    {name: 'ap_funcionario'},
    {name: 'nu_cedula'},
    {name: 'da_login'},
    {name: 'de_rol'},
    {name: 'tx_ejecutor'},
    {name: 'in_estatus'},
    ],
    listeners : {
        exception : function(proxy, response, operation) {
            Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
        }
    }
    });
    return this.store;
}
};
Ext.onReady(usuarioLista.main.init, usuarioLista.main);
</script>
<div id="contenedorusuarioLista"></div>
<div id="formulariousuario"></div>
