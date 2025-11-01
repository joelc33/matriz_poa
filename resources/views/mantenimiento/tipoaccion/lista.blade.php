<script type="text/javascript">
Ext.ns("tipoaccionLista");
function change(val){
	if(val==true){
	    return '<span style="color:green;">Activo</span>';
	}else if(val==false){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
tipoaccionLista.main = {
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
        tipoaccionLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccion');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/nuevo') }}",
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
	this.codigo  = tipoaccionLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	tipoaccionLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccion');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/editar') }}/"+this.codigo,
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Desabilitar un registro
this.eliminar= new Ext.Button({
    text:'Eliminar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = tipoaccionLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Eliminar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/tipoaccion/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: tipoaccionLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    tipoaccionLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                tipoaccionLista.main.mascara.hide();
            }});
	}});
    }
});

this.lista_ae= new Ext.Button({
    text:'Proyectos',
    iconCls: 'icon-accion_especifica',
    handler:function(){
	this.codigo  = tipoaccionLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	tipoaccionLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccion');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/ae/lista') }}/"+this.codigo,
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.lista_partida= new Ext.Button({
    text:'Partidas Admitidas',
    iconCls: 'icon-accion_fisica',
    handler:function(){
	this.codigo  = tipoaccionLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	tipoaccionLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccion');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/partida/lista') }}/"+this.codigo,
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.editar.disable();
this.eliminar.disable();
this.lista_ae.disable();
this.lista_partida.disable();

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
		tipoaccionLista.main.store_lista.baseParams={};
		tipoaccionLista.main.store_lista.baseParams.paginar = 'si';
		tipoaccionLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		tipoaccionLista.main.store_lista.load();
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
			tipoaccionLista.main.store_lista.baseParams={}
			tipoaccionLista.main.store_lista.baseParams.BuscarBy = true;
			tipoaccionLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			tipoaccionLista.main.store_lista.baseParams[this.paramName] = v;
			tipoaccionLista.main.store_lista.baseParams.paginar = 'si';
			tipoaccionLista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'tipoac.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'tipoac.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'tipoac.eliminar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'tipoac.ae.lista', 'in_habilitado' => true), Session::get('credencial') ))
				this.lista_ae,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'Sector', width:300, sortable: true, menuDisabled:true,dataIndex: 'nu_descripcion'},
    {header: 'Codigo', width:80, sortable: true, menuDisabled:true,dataIndex: 'nu_original'},
		{header: 'Nombre', width:500,  menuDisabled:true, sortable: true, /*renderer: textoLargo,*/ dataIndex: 'de_nombre'},
    {header: 'Descripcion', width:300,  menuDisabled:true, sortable: true, /*renderer: textoLargo,*/ dataIndex: 'de_accion'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			tipoaccionLista.main.editar.enable();
			tipoaccionLista.main.eliminar.enable();
			tipoaccionLista.main.lista_ae.enable();
			tipoaccionLista.main.lista_partida.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedortipoaccionLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
tipoaccionLista.main.editar.disable();
tipoaccionLista.main.eliminar.disable();
tipoaccionLista.main.lista_ae.disable();
tipoaccionLista.main.lista_partida.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/tipoaccion/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'nu_descripcion'},
    {name: 'de_nombre'},
    {name: 'de_accion'},
    {name: 'nu_original'},
    {name: 'in_activo'},
           ]
    });
    return this.store;
}
};
Ext.onReady(tipoaccionLista.main.init, tipoaccionLista.main);
</script>
<div id="contenedortipoaccionLista"></div>
<div id="formulariotipoaccion"></div>
