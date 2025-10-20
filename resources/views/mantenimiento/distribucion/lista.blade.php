<script type="text/javascript">
Ext.ns("distribucionmunicipioLista");
function change(val){
	if(val==true){
	    return '<span style="color:green;">Activo</span>';
	}else if(val==false){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
function movimiento(val){
	if(val==true){
	    return '<span style="color:green;">Si</span>';
	}else if(val==false){
	    return '<span style="color:red;">No</span>';
	}
return val;
};
distribucionmunicipioLista.main = {
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
        distribucionmunicipioLista.main.mascara.show();
        this.msg = Ext.get('formulariodistribucionmunicipio');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/distribucionmunicipio/nuevo') }}",
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
	this.codigo  = distribucionmunicipioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	distribucionmunicipioLista.main.mascara.show();
        this.msg = Ext.get('formulariodistribucionmunicipio');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/distribucionmunicipio/editar') }}/"+this.codigo,
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
	this.codigo  = distribucionmunicipioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Deshabilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/distribucionmunicipio/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: distribucionmunicipioLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    distribucionmunicipioLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                distribucionmunicipioLista.main.mascara.hide();
            }});
	}});
    }
});

this.habilitar= new Ext.Button({
    text:'Habilitar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = distribucionmunicipioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Habilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/distribucionmunicipio/habilitar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: distribucionmunicipioLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    distribucionmunicipioLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                distribucionmunicipioLista.main.mascara.hide();
            }});
	}});
    }
});

//Agregar un registro
this.parametros = new Ext.Button({
    text:'Parametros',
    iconCls: 'icon-paracont',
    handler:function(){
        distribucionmunicipioLista.main.mascara.show();
        this.msg = Ext.get('formulariodistribucionmunicipio');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/distribucionmunicipio/parametro') }}",
         scripts: true,
         text: "Cargando.."
        });
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
		distribucionmunicipioLista.main.store_lista.baseParams={};
		distribucionmunicipioLista.main.store_lista.baseParams.paginar = 'si';
		distribucionmunicipioLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		distribucionmunicipioLista.main.store_lista.load();
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
			distribucionmunicipioLista.main.store_lista.baseParams={}
			distribucionmunicipioLista.main.store_lista.baseParams.BuscarBy = true;
			distribucionmunicipioLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			distribucionmunicipioLista.main.store_lista.baseParams[this.paramName] = v;
			distribucionmunicipioLista.main.store_lista.baseParams.paginar = 'si';
			distribucionmunicipioLista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'libro.distribucionmunicipio.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.distribucionmunicipio.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.distribucionmunicipio.habilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.habilitar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.distribucionmunicipio.deshabilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.distribucionmunicipio.parametro', 'in_habilitado' => true), Session::get('credencial') ))
				this.parametros,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'Municipio', width:100,  menuDisabled:true, sortable: true, dataIndex: 'de_municipio'},
		{header: 'Partida', width:100,  menuDisabled:true, sortable: true, dataIndex: 'co_partida'},
		{header: 'Proyeccion Poblacion', width:100,  menuDisabled:true, sortable: true, dataIndex: 'nu_base_censo'},
    {header: 'Factor Poblacion', width:100,  menuDisabled:true, sortable: true, dataIndex: 'nu_factor_poblacion'},
    {header: '45% P.I', width:100,  menuDisabled:true, sortable: true, dataIndex: 'cuatrocinco_ppi'},
    {header: '50% F.P', width:100,  menuDisabled:true, sortable: true, dataIndex: 'cincocero_fpp'},
    {header: 'Sup. KM2', width:100,  menuDisabled:true, sortable: true, dataIndex: 'superficie_km'},
    {header: 'F. Sup.', width:100,  menuDisabled:true, sortable: true, dataIndex: 'superficie_factor'},
    {header: 'Monto Situado', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_total'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			distribucionmunicipioLista.main.editar.enable();
			distribucionmunicipioLista.main.habilitar.enable();
			distribucionmunicipioLista.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedordistribucionmunicipioLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
distribucionmunicipioLista.main.editar.disable();
distribucionmunicipioLista.main.habilitar.disable();
distribucionmunicipioLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/distribucionmunicipio/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
		{name: 'de_municipio'},
		{name: 'co_partida'},
		{name: 'nu_base_censo'},
    {name: 'nu_factor_poblacion'},
    {name: 'cuatrocinco_ppi'},
		{name: 'cincocero_fpp'},
		{name: 'superficie_km'},
		{name: 'superficie_factor'},
		{name: 'extension_territorio'},
		{name: 'mo_total'},
           ]
    });
    return this.store;
}
};
Ext.onReady(distribucionmunicipioLista.main.init, distribucionmunicipioLista.main);
</script>
<div id="contenedordistribucionmunicipioLista"></div>
<div id="formulariodistribucionmunicipio"></div>
