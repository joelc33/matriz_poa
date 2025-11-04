<script type="text/javascript">
Ext.ns("tipoaccionaeOficinaLista");
function change(val){
	if(val==true){
	    return '<span style="color:green;">Activo</span>';
	}else if(val==false){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
tipoaccionaeOficinaLista.main = {
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
        tipoaccionaeOficinaLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccionaeOficina');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/ae/oficina/nuevo') }}/{{ $data['id'] }}",
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
	this.codigo  = tipoaccionaeOficinaLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	tipoaccionaeOficinaLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccionaeOficina');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/ae/oficina/editar') }}/"+this.codigo,
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
	this.codigo  = tipoaccionaeOficinaLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Eliminar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/tipoaccion/ae/oficina/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: tipoaccionaeOficinaLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    tipoaccionaeOficinaLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                tipoaccionaeOficinaLista.main.mascara.hide();
            }});
	}});
    }
});

this.editar.disable();
this.eliminar.disable();

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
		tipoaccionaeOficinaLista.main.store_lista.baseParams={};
		tipoaccionaeOficinaLista.main.store_lista.baseParams.paginar = 'si';
		tipoaccionaeOficinaLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		tipoaccionaeOficinaLista.main.store_lista.baseParams.ac_ae = {{ $data['id'] }};
		tipoaccionaeOficinaLista.main.store_lista.load();
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
			tipoaccionaeOficinaLista.main.store_lista.baseParams={}
			tipoaccionaeOficinaLista.main.store_lista.baseParams.BuscarBy = true;
			tipoaccionaeOficinaLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			tipoaccionaeOficinaLista.main.store_lista.baseParams.ac_ae = {{ $data['id'] }};
			tipoaccionaeOficinaLista.main.store_lista.baseParams[this.paramName] = v;
			tipoaccionaeOficinaLista.main.store_lista.baseParams.paginar = 'si';
			tipoaccionaeOficinaLista.main.store_lista.load();
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
    //autoHeight:true,
		height:300,
    tbar:[
			@if( in_array( array( 'de_privilegio' => 'tipoac.ae.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'tipoac.ae.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'tipoac.ae.eliminar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
		{header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'Nombre', width:400,  menuDisabled:true, sortable: true, /*renderer: textoLargo,*/ dataIndex: 'de_nombre'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			tipoaccionaeOficinaLista.main.editar.enable();
			tipoaccionaeOficinaLista.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

//this.gridPanel_.render("contenedortipoaccionaeOficinaLista");

this.winformPanel_ = new Ext.Window({
	title:'Formulario: Oficina/Gerencia',
	modal:true,
	constrain:true,
	width:714,
	height:332,
	frame:true,
	closabled:true,
	resizable: false,
	//autoHeight:true,
	items:[
		this.gridPanel_
	]
});
this.winformPanel_.show();
tipoaccionaeLista.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.baseParams.ac_ae = {{ $data['id'] }};
this.store_lista.load();
this.store_lista.on('load',function(){
tipoaccionaeOficinaLista.main.editar.disable();
tipoaccionaeOficinaLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/tipoaccion/ae/oficina/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'de_nombre'},
    {name: 'in_activo'},
           ]
    });
    return this.store;
}
};
Ext.onReady(tipoaccionaeOficinaLista.main.init, tipoaccionaeOficinaLista.main);
</script>
<div id="contenedortipoaccionaeOficinaLista"></div>
<div id="formulariotipoaccionaeOficina"></div>
