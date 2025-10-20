<script type="text/javascript">
Ext.ns("forma005ListaDatos");
function actividadEstado(val){
	if(val==6){
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/check.png') }}" style="cursor:pointer;">'+' <span style="color:green;"> Cargado</span>'+'</div></tpl>';
	}else{
            if(val==5){
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> Pendiente</span>'+'</div></tpl>';
	}else{
        return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> Negado</span>'+'</div></tpl>';
        }
        }
return val;
};
forma005ListaDatos.main = {
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.nuevo = new Ext.Button({
    text:'Nuevo',
    iconCls: 'icon-nuevo',
	handler:function(){
	this.codigo  = '{{ $id_tab_ac }}';
	forma005ListaDatos.main.mascara.show();
			this.msg = Ext.get('formularioEditar');
			this.msg.load({
			 url:"{{ URL::to('ac/seguimiento/005/nuevo') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
			});
	}
});
//Editar un registro
this.editar= new Ext.Button({
    text:'Editar Indicadores',
    iconCls: 'icon-editar',
	handler:function(){
	this.codigo  = forma005ListaDatos.main.gridPanel_.getSelectionModel().getSelected().get('id');
	forma005ListaDatos.main.mascara.show();
			this.msg = Ext.get('formularioEditar');
			this.msg.load({
			 url:"{{ URL::to('ac/seguimiento/005/editar') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
			});
	}
});

this.editar.disable();

this.eliminar= new Ext.Button({
	text:'Eliminar',
	iconCls: 'icon-eliminar',
	handler: function(boton){
		this.codigo  = forma005ListaDatos.main.gridPanel_.getSelectionModel().getSelected().get('id');
		Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Eliminar?', function(boton){
		if(boton=="yes"){
	        Ext.Ajax.request({
	            method:'POST',
	            url:'{{ URL::to('ac/seguimiento/005/eliminar') }}',
	            params:{
			_token: '{{ csrf_token() }}',
	                id: forma005ListaDatos.main.gridPanel_.getSelectionModel().getSelected().get('id')
	            },
	            success:function(result, request ) {
	                obj = Ext.util.JSON.decode(result.responseText);
	                if(obj.success=="true"){
			    forma005ListaDatos.main.store_lista.load();
	                    Ext.Msg.alert("Notificación",obj.msg);
	                }else{
	                    Ext.Msg.alert("Notificación",obj.msg);
	                }
	                forma005ListaDatos.main.mascara.hide();
	            }});
		}});
	}
});

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
		forma005ListaDatos.main.store_lista.baseParams={};
		forma005ListaDatos.main.store_lista.baseParams.paginar = 'si';
		forma005ListaDatos.main.store_lista.baseParams._token = '{{ csrf_token() }}';
    forma005ListaDatos.main.store_lista.baseParams.id_tab_ac = '{{ $id_tab_ac }}';
		forma005ListaDatos.main.store_lista.load();
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
			forma005ListaDatos.main.store_lista.baseParams={}
			forma005ListaDatos.main.store_lista.baseParams.BuscarBy = true;
			forma005ListaDatos.main.store_lista.baseParams._token = '{{ csrf_token() }}';
      forma005ListaDatos.main.store_lista.baseParams.id_tab_ac = '{{ $id_tab_ac }}';
			forma005ListaDatos.main.store_lista.baseParams[this.paramName] = v;
			forma005ListaDatos.main.store_lista.baseParams.paginar = 'si';
			forma005ListaDatos.main.store_lista.load();
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
    height:610,
    tbar:[
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
				this.nuevo,'-',
			@endif
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',this.eliminar,'-',
			@endif                        
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'PRODUCTO PROGRAMADO ANUAL DEL OBJETIVO INSTITUCIONAL', width:180, renderer: textoLargo, menuDisabled:true, sortable: true, dataIndex: 'pp_anual'},
    {header: 'TIPO DE INDICADOR', width:160, renderer: textoLargo, menuDisabled:true, sortable: true, dataIndex: 'tp_indicador'},
    {header: 'NOMBRE DEL INDICADOR', width:220,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nb_indicador_gestion'},
    {header: 'DESCRIPCIÓN DEL INDICADOR', width:180,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_indicador_descripcion'},
    {header: 'Estatus', width:130,  menuDisabled:true, sortable: true, renderer: actividadEstado, dataIndex: 'id_tab_estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			forma005ListaDatos.main.editar.enable();
                        forma005ListaDatos.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.winformPanel_ = new Ext.Window({
    title:'F005: INDICADORES DE GESTIÓN',
    modal:true,
    constrain:true,
    width:914,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
      this.gridPanel_
    ]
});
this.winformPanel_.show();
forma005Lista.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.baseParams.id_tab_ac = '{{ $id_tab_ac }}';
this.store_lista.load();
this.store_lista.on('load',function(){
forma005ListaDatos.main.editar.disable();
forma005ListaDatos.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('ac/seguimiento/005/datos/storeListaDatos') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'pp_anual'},
    {name: 'tp_indicador'},
    {name: 'nb_indicador_gestion'},
    {name: 'de_indicador_descripcion'},
    {name: 'id_tab_estatus'},
    {name: 'in_005'}
           ]
    });
    return this.store;
}
};
Ext.onReady(forma005ListaDatos.main.init, forma005ListaDatos.main);
</script>
<div id="formularioEditar"></div>