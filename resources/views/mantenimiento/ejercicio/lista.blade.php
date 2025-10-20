<script type="text/javascript">
Ext.ns("ejercicioLista");
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
ejercicioLista.main = {
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
        /*ejercicioLista.main.mascara.show();
        this.msg = Ext.get('formularioejercicio');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/ejercicio/nuevo') }}",
         scripts: true,
         text: "Cargando.."
			 });*/
			 addTab('nuevoEjercicio','Crear Ejercicio','{{ URL::to('mantenimiento/ejercicio/nuevo') }}','load','icon-nuevo','');
    }
});

//Editar un registro
this.ver= new Ext.Button({
    text:'Cronograma',
    iconCls: 'icon-calendario',
    handler:function(){
	this.codigo  = ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	ejercicioLista.main.mascara.show();
        this.msg = Ext.get('formularioejercicio');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/ejercicio/cronograma') }}/"+this.codigo,
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Desabilitar un registro
this.deshabilitar= new Ext.Button({
    text:'Cerrar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea cerrar Periodo?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/ejercicio/cerrar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                periodo: ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    ejercicioLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                ejercicioLista.main.mascara.hide();
            }});
	}});
    }
});

this.habilitar= new Ext.Button({
    text:'Habilitar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Habilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/ejercicio/habilitar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                periodo: ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    ejercicioLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                ejercicioLista.main.mascara.hide();
            }});
	}});
    }
});

this.ver.disable();
this.deshabilitar.disable();
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
		ejercicioLista.main.store_lista.baseParams={};
		ejercicioLista.main.store_lista.baseParams.paginar = 'si';
		ejercicioLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		ejercicioLista.main.store_lista.load();
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
			ejercicioLista.main.store_lista.baseParams={}
			ejercicioLista.main.store_lista.baseParams.BuscarBy = true;
			ejercicioLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			ejercicioLista.main.store_lista.baseParams[this.paramName] = v;
			ejercicioLista.main.store_lista.baseParams.paginar = 'si';
			ejercicioLista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.aperturar', 'in_habilitado' => true), Session::get('credencial') ))
				this.habilitar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.cerrar', 'in_habilitado' => true), Session::get('credencial') ))
				this.deshabilitar,'-',
			@endif
				this.buscador,'-',
      @if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.cronograma', 'in_habilitado' => true), Session::get('credencial') ))
        this.ver,'-',
      @endif
    ],
    columns: [
    new Ext.grid.RowNumberer(),
		{header: 'Descripcion', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'id'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			ejercicioLista.main.ver.enable();
			ejercicioLista.main.habilitar.enable();
			ejercicioLista.main.deshabilitar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorejercicioLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
ejercicioLista.main.ver.disable();
ejercicioLista.main.habilitar.disable();
ejercicioLista.main.deshabilitar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/ejercicio/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'in_activo'},
           ]
    });
    return this.store;
}
};
Ext.onReady(ejercicioLista.main.init, ejercicioLista.main);
</script>
<div id="contenedorejercicioLista"></div>
<div id="formularioejercicio"></div>
