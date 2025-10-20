<script type="text/javascript">
Ext.ns("tipoaccionpartidaLista");
function change(val){
	if(val==true){
	    return '<span style="color:green;">Activo</span>';
	}else if(val==false){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
tipoaccionpartidaLista.main = {
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
        tipoaccionpartidaLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccionpartida');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/partida/nuevo') }}/{{ $data['id'] }}",
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
	this.codigo  = tipoaccionpartidaLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	tipoaccionpartidaLista.main.mascara.show();
        this.msg = Ext.get('formulariotipoaccionpartida');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/tipoaccion/partida/editar') }}/"+this.codigo,
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
	this.codigo  = tipoaccionpartidaLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Eliminar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/tipoaccion/partida/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: tipoaccionpartidaLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    tipoaccionpartidaLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                tipoaccionpartidaLista.main.mascara.hide();
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
		tipoaccionpartidaLista.main.store_lista.baseParams={};
		tipoaccionpartidaLista.main.store_lista.baseParams.paginar = 'si';
		tipoaccionpartidaLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		tipoaccionpartidaLista.main.store_lista.baseParams.ac = {{ $data['id'] }};
		tipoaccionpartidaLista.main.store_lista.load();
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
			tipoaccionpartidaLista.main.store_lista.baseParams={}
			tipoaccionpartidaLista.main.store_lista.baseParams.BuscarBy = true;
			tipoaccionpartidaLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			tipoaccionpartidaLista.main.store_lista.baseParams.ac = {{ $data['id'] }};
			tipoaccionpartidaLista.main.store_lista.baseParams[this.paramName] = v;
			tipoaccionpartidaLista.main.store_lista.baseParams.paginar = 'si';
			tipoaccionpartidaLista.main.store_lista.load();
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
		height:400,
    tbar:[
			@if( in_array( array( 'de_privilegio' => 'tipoac.partida.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'tipoac.partida.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'tipoac.partida.eliminar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
		{header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'Accion Especifica', width:300,  menuDisabled:true, sortable: true, /*renderer: textoLargo,*/ dataIndex: 'ae_nombre'},
		{header: 'Codigo', width:80, sortable: true, menuDisabled:true,dataIndex: 'nu_partida'},
		{header: 'Denominacion', width:200,  menuDisabled:true, sortable: true, /*renderer: textoLargo,*/ dataIndex: 'de_partida'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			tipoaccionpartidaLista.main.editar.enable();
			tipoaccionpartidaLista.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 15,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

//this.gridPanel_.render("contenedortipoaccionpartidaLista");

this.winformPanel_ = new Ext.Window({
	title:'Formulario: Partidas Admitidas',
	modal:true,
	constrain:true,
	width:714,
	height:432,
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
tipoaccionpartidaLista.main.editar.disable();
tipoaccionpartidaLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/tipoaccion/partida/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
		{name: 'nu_numero'},
		{name: 'de_nombre'},
    {name: 'nu_partida'},
		{name: 'de_partida'},
    {name: 'in_activo'},
		{name: 'ae_nombre',
				convert: function(v, r) {
						return r.nu_numero + ' - ' + r.de_nombre;
				}
		}
           ]
    });
    return this.store;
}
};
Ext.onReady(tipoaccionpartidaLista.main.init, tipoaccionpartidaLista.main);
</script>
<div id="contenedortipoaccionpartidaLista"></div>
<div id="formulariotipoaccionpartida"></div>
