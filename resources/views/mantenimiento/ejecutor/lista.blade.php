<script type="text/javascript">
Ext.ns("ejecutorLista");
function change(val){
	if(val==true){
	    return '<span style="color:green;">Activo</span>';
	}else if(val==false){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
function verificar(val){
	if(val==true){
	    return '<span style="color:green;">Si</span>';
	}else if(val==false){
	    return '<span style="color:red;">No</span>';
	}
return val;
};
ejecutorLista.main = {
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
        ejecutorLista.main.mascara.show();
        this.msg = Ext.get('formularioejecutor');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/ejecutor/nuevo') }}",
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
	this.codigo  = ejecutorLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	ejecutorLista.main.mascara.show();
        this.msg = Ext.get('formularioejecutor');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/ejecutor/editar') }}/"+this.codigo,
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
	this.codigo  = ejecutorLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Deshabilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/ejecutor/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: ejecutorLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    ejecutorLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                ejecutorLista.main.mascara.hide();
            }});
	}});
    }
});

this.habilitar= new Ext.Button({
    text:'Habilitar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = ejecutorLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Habilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/ejecutor/habilitar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: ejecutorLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    ejecutorLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                ejecutorLista.main.mascara.hide();
            }});
	}});
    }
});

this.editar.disable();
this.eliminar.disable();
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
		ejecutorLista.main.store_lista.baseParams={};
		ejecutorLista.main.store_lista.baseParams.paginar = 'si';
		ejecutorLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		ejecutorLista.main.store_lista.load();
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
			ejecutorLista.main.store_lista.baseParams={}
			ejecutorLista.main.store_lista.baseParams.BuscarBy = true;
			ejecutorLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			ejecutorLista.main.store_lista.baseParams[this.paramName] = v;
			ejecutorLista.main.store_lista.baseParams.paginar = 'si';
			ejecutorLista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'ejecutor.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'ejecutor.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'ejecutor.habilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.habilitar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'ejecutor.deshabilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'Ejecutor', width:400,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nombre'},
    {header: 'Correo Institucional', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_correo'},
    {header: 'telefono', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_telefono'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    {header: 'Verificado', width:80,  menuDisabled:true, sortable: true, renderer: verificar, dataIndex: 'in_verificado'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			ejecutorLista.main.editar.enable();
			ejecutorLista.main.habilitar.enable();
			ejecutorLista.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorejecutorLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
ejecutorLista.main.editar.disable();
ejecutorLista.main.habilitar.disable();
ejecutorLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/ejecutor/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'id_ejecutor'},
    {name: 'tx_ejecutor'},
    {name: 'de_correo'},
    {name: 'de_telefono'},
    {name: 'in_activo'},
    {name: 'in_verificado'},
    {name: 'nombre',
        convert: function(v, r) {
            return r.id_ejecutor + ' - ' + r.tx_ejecutor;
        }
    }
           ]
    });
    return this.store;
}
};
Ext.onReady(ejecutorLista.main.init, ejecutorLista.main);
</script>
<div id="contenedorejecutorLista"></div>
<div id="formularioejecutor"></div>
