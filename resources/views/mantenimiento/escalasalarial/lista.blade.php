<script type="text/javascript">
Ext.ns("escalasalarialLista");
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
escalasalarialLista.main = {
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
        escalasalarialLista.main.mascara.show();
        this.msg = Ext.get('formularioescalasalarial');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/escalasalarial/nuevo') }}",
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
	this.codigo  = escalasalarialLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	escalasalarialLista.main.mascara.show();
        this.msg = Ext.get('formularioescalasalarial');
        this.msg.load({
         url:"{{ URL::to('mantenimiento/escalasalarial/editar') }}/"+this.codigo,
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
	this.codigo  = escalasalarialLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Deshabilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/escalasalarial/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: escalasalarialLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    escalasalarialLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                escalasalarialLista.main.mascara.hide();
            }});
	}});
    }
});

this.habilitar= new Ext.Button({
    text:'Habilitar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = escalasalarialLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Habilitar Registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('mantenimiento/escalasalarial/habilitar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: escalasalarialLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    escalasalarialLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                escalasalarialLista.main.mascara.hide();
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
		escalasalarialLista.main.store_lista.baseParams={};
		escalasalarialLista.main.store_lista.baseParams.paginar = 'si';
		escalasalarialLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		escalasalarialLista.main.store_lista.load();
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
			escalasalarialLista.main.store_lista.baseParams={}
			escalasalarialLista.main.store_lista.baseParams.BuscarBy = true;
			escalasalarialLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			escalasalarialLista.main.store_lista.baseParams[this.paramName] = v;
			escalasalarialLista.main.store_lista.baseParams.paginar = 'si';
			escalasalarialLista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'libro.escalasalarial.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.escalasalarial.editar', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.escalasalarial.habilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.habilitar,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'libro.escalasalarial.deshabilitar', 'in_habilitado' => true), Session::get('credencial') ))
				this.eliminar,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'Ejercicio', width:80,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'id_tab_ejercicio_fiscal'},
		{header: 'Tipo', width:100,  menuDisabled:true, sortable: true, dataIndex: 'de_tipo_empleado'},
		{header: 'Grupo', width:100,  menuDisabled:true, sortable: true, dataIndex: 'de_grupo'},
		{header: 'Escala', width:100,  menuDisabled:true, sortable: true, dataIndex: 'de_escala_salarial'},
		{header: 'Cargos', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'total_cargo'},
		{header: 'Monto', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_escala_salarial'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			escalasalarialLista.main.editar.enable();
			escalasalarialLista.main.habilitar.enable();
			escalasalarialLista.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorescalasalarialLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
escalasalarialLista.main.editar.disable();
escalasalarialLista.main.habilitar.disable();
escalasalarialLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('mantenimiento/escalasalarial/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
		{name: 'id_tab_ejercicio_fiscal'},
		{name: 'de_grupo'},
		{name: 'de_escala_salarial'},
    {name: 'de_tipo_empleado'},
		{name: 'mo_escala_salarial'},
    {name: 'in_activo'},
		{name: 'total_cargo',
				convert: function(v, r) {
						return eval(r.nu_masculino)+eval(r.nu_femenino);
				}
		}
           ]
    });
    return this.store;
}
};
Ext.onReady(escalasalarialLista.main.init, escalasalarialLista.main);
</script>
<div id="contenedorescalasalarialLista"></div>
<div id="formularioescalasalarial"></div>
