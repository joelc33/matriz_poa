<script type="text/javascript">
Ext.ns("tipoaccionaeLista");
function change(val){
	if(val==true){
	    return '<span style="color:green;">Activo</span>';
	}else if(val==false){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
tipoaccionaeLista.main = {
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
        tipoaccionaeLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccionae');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/ae/nuevo') }}/{{ $data['id'] }}",
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
	this.codigo  = tipoaccionaeLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	tipoaccionaeLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccionae');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/ae/editar') }}/"+this.codigo,
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
	this.codigo  = tipoaccionaeLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Eliminar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/tipoaccion/ae/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: tipoaccionaeLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    tipoaccionaeLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                tipoaccionaeLista.main.mascara.hide();
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
		tipoaccionaeLista.main.store_lista.baseParams={};
		tipoaccionaeLista.main.store_lista.baseParams.paginar = 'si';
		tipoaccionaeLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		tipoaccionaeLista.main.store_lista.baseParams.ac = {{ $data['id'] }};
		tipoaccionaeLista.main.store_lista.load();
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
			tipoaccionaeLista.main.store_lista.baseParams={}
			tipoaccionaeLista.main.store_lista.baseParams.BuscarBy = true;
			tipoaccionaeLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			tipoaccionaeLista.main.store_lista.baseParams.ac = {{ $data['id'] }};
			tipoaccionaeLista.main.store_lista.baseParams[this.paramName] = v;
			tipoaccionaeLista.main.store_lista.baseParams.paginar = 'si';
			tipoaccionaeLista.main.store_lista.load();
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
    {header: 'Codigo', width:80, sortable: true, menuDisabled:true,dataIndex: 'nu_numero'},
		{header: 'Nombre', width:400,  menuDisabled:true, sortable: true, /*renderer: textoLargo,*/ dataIndex: 'de_nombre'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			tipoaccionaeLista.main.editar.enable();
			tipoaccionaeLista.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

//this.gridPanel_.render("contenedortipoaccionaeLista");

this.winformPanel_ = new Ext.Window({
	title:'Formulario: Accion Especifica',
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
tipoaccionLista.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.baseParams.ac = {{ $data['id'] }};
this.store_lista.load();
this.store_lista.on('load',function(){
tipoaccionaeLista.main.editar.disable();
tipoaccionaeLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/tipoaccion/ae/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'de_nombre'},
		{name: 'nu_numero'},
    {name: 'in_activo'},
           ]
    });
    return this.store;
}
};
Ext.onReady(tipoaccionaeLista.main.init, tipoaccionaeLista.main);
</script>
<div id="contenedortipoaccionaeLista"></div>
<div id="formulariotipoaccionae"></div>
