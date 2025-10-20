<script type="text/javascript">
Ext.ns("clasificadortipoLista");
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
clasificadortipoLista.main = {
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
        clasificadortipoLista.main.mascara.show();
        this.msg = Ext.get('formularioclasificadortipo');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/clasificadortipo/nuevo') }}",
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
	this.codigo  = clasificadortipoLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	clasificadortipoLista.main.mascara.show();
        this.msg = Ext.get('formularioclasificadortipo');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/clasificadortipo/editar') }}/"+this.codigo,
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
	this.codigo  = clasificadortipoLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Deshabilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/clasificadortipo/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: clasificadortipoLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    clasificadortipoLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                clasificadortipoLista.main.mascara.hide();
            }});
	}});
    }
});

this.habilitar= new Ext.Button({
    text:'Habilitar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = clasificadortipoLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Habilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/clasificadortipo/habilitar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: clasificadortipoLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    clasificadortipoLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                clasificadortipoLista.main.mascara.hide();
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
		clasificadortipoLista.main.store_lista.baseParams={};
		clasificadortipoLista.main.store_lista.baseParams.paginar = 'si';
		clasificadortipoLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		clasificadortipoLista.main.store_lista.load();
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
			clasificadortipoLista.main.store_lista.baseParams={}
			clasificadortipoLista.main.store_lista.baseParams.BuscarBy = true;
			clasificadortipoLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			clasificadortipoLista.main.store_lista.baseParams[this.paramName] = v;
			clasificadortipoLista.main.store_lista.baseParams.paginar = 'si';
			clasificadortipoLista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'libro.clasificadortipo.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.clasificadortipo.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.clasificadortipo.habilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.habilitar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.clasificadortipo.deshabilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'Ejercicio', width:80,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'id_tab_ejercicio_fiscal'},
		{header: 'Descripcion', width:200,  menuDisabled:true, sortable: true, dataIndex: 'tipo_personal'},
		{header: 'Cargos', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'total_cargo'},
		{header: 'Sueldo', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_sueldo'},
		{header: 'Compensacion', width:100,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_compensacion'},
		{header: 'Primas', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_primas'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			clasificadortipoLista.main.editar.enable();
			clasificadortipoLista.main.habilitar.enable();
			clasificadortipoLista.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorclasificadortipoLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
clasificadortipoLista.main.editar.disable();
clasificadortipoLista.main.habilitar.disable();
clasificadortipoLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/clasificadortipo/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
		{name: 'id_tab_ejercicio_fiscal'},
    {name: 'de_tipo_personal'},
		{name: 'mo_sueldo'},
		{name: 'mo_compensacion'},
		{name: 'mo_primas'},
    {name: 'in_activo'},
		{name: 'total_cargo',
				convert: function(v, r) {
						return eval(r.nu_masculino)+eval(r.nu_femenino);
				}
		},
		{name: 'tipo_personal',
				convert: function(v, r) {
						return r.nu_codigo+ ' - ' + r.de_tipo_personal;
				}
		}
           ]
    });
    return this.store;
}
};
Ext.onReady(clasificadortipoLista.main.init, clasificadortipoLista.main);
</script>
<div id="contenedorclasificadortipoLista"></div>
<div id="formularioclasificadortipo"></div>
