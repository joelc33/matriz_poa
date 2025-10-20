<script type="text/javascript">
Ext.ns("sectorLista");
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
sectorLista.main = {
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
        sectorLista.main.mascara.show();
        this.msg = Ext.get('formulariosector');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/sector/nuevo') }}",
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
	this.codigo  = sectorLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	sectorLista.main.mascara.show();
        this.msg = Ext.get('formulariosector');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/sector/editar') }}/"+this.codigo,
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
	this.codigo  = sectorLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Deshabilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/sector/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: sectorLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    sectorLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                sectorLista.main.mascara.hide();
            }});
	}});
    }
});

this.habilitar= new Ext.Button({
    text:'Habilitar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = sectorLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Habilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/sector/habilitar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: sectorLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    sectorLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                sectorLista.main.mascara.hide();
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
		sectorLista.main.store_lista.baseParams={};
		sectorLista.main.store_lista.baseParams.paginar = 'si';
		sectorLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		sectorLista.main.store_lista.load();
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
			sectorLista.main.store_lista.baseParams={}
			sectorLista.main.store_lista.baseParams.BuscarBy = true;
			sectorLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			sectorLista.main.store_lista.baseParams[this.paramName] = v;
			sectorLista.main.store_lista.baseParams.paginar = 'si';
			sectorLista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'sectores.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'sectores.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'sectores.habilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.habilitar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'sectores.deshabilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'Descripcion', width:400,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nombre'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			sectorLista.main.editar.enable();
			sectorLista.main.habilitar.enable();
			sectorLista.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorsectorLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
sectorLista.main.editar.disable();
sectorLista.main.habilitar.disable();
sectorLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/sector/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'tx_codigo'},
		{name: 'tx_descripcion'},
    {name: 'in_activo'},
    {name: 'nombre',
        convert: function(v, r) {
            return r.tx_codigo + ' - ' + r.tx_descripcion;
        }
    }
           ]
    });
    return this.store;
}
};
Ext.onReady(sectorLista.main.init, sectorLista.main);
</script>
<div id="contenedorsectorLista"></div>
<div id="formulariosector"></div>
