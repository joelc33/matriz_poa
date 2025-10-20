<script type="text/javascript">
Ext.ns("objetivoLista");
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
objetivoLista.main = {
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
        objetivoLista.main.mascara.show();
        this.msg = Ext.get('formularioobjetivo');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/objetivo/nuevo') }}",
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
	this.codigo  = objetivoLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	objetivoLista.main.mascara.show();
        this.msg = Ext.get('formularioobjetivo');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/objetivo/editar') }}/"+this.codigo,
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
	this.codigo  = objetivoLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Deshabilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/objetivo/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: objetivoLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    objetivoLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                objetivoLista.main.mascara.hide();
            }});
	}});
    }
});

this.habilitar= new Ext.Button({
    text:'Habilitar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = objetivoLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Habilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/objetivo/habilitar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: objetivoLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    objetivoLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                objetivoLista.main.mascara.hide();
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
		objetivoLista.main.store_lista.baseParams={};
		objetivoLista.main.store_lista.baseParams.paginar = 'si';
		objetivoLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		objetivoLista.main.store_lista.load();
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
			objetivoLista.main.store_lista.baseParams={}
			objetivoLista.main.store_lista.baseParams.BuscarBy = true;
			objetivoLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			objetivoLista.main.store_lista.baseParams[this.paramName] = v;
			objetivoLista.main.store_lista.baseParams.paginar = 'si';
			objetivoLista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'objetivos.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'objetivos.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'objetivos.habilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.habilitar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'objetivos.deshabilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'Descripcion', width:600,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_descripcion'},
    {header: 'Ejercicio', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'ejercicio'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			objetivoLista.main.editar.enable();
			objetivoLista.main.habilitar.enable();
			objetivoLista.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorobjetivoLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
objetivoLista.main.editar.disable();
objetivoLista.main.habilitar.disable();
objetivoLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/objetivo/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'nu_codigo'},
		{name: 'tx_descripcion'},
    {name: 'in_activo'},
    {name: 'id_tab_ejercicio_fiscal'},
    {name: 'nombre',
        convert: function(v, r) {
            return r.nu_codigo + ' - ' + r.tx_descripcion;
        }
    },
    {name: 'ejercicio',
        convert: function(v, r) {
            //return r.id_tab_ejercicio_fiscal.replace('{','').replace('}','');
            return r.id_tab_ejercicio_fiscal;
        }
    }
           ]
    });
    return this.store;
}
};
Ext.onReady(objetivoLista.main.init, objetivoLista.main);
</script>
<div id="contenedorobjetivoLista"></div>
<div id="formularioobjetivo"></div>
