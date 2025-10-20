<script type="text/javascript">
Ext.ns("proyectoseguimientoLista");
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
proyectoseguimientoLista.main = {
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
        proyectoseguimientoLista.main.mascara.show();
        this.msg = Ext.get('formularioproyectoseguimiento');
        this.msg.load({
         url:"{{ URL::to('proyecto/seguimiento/nuevo') }}",
         scripts: true,
         text: "Cargando.."
        });
    }
});

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
		proyectoseguimientoLista.main.store_lista.baseParams={};
		proyectoseguimientoLista.main.store_lista.baseParams.paginar = 'si';
		proyectoseguimientoLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		proyectoseguimientoLista.main.store_lista.load();
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
			proyectoseguimientoLista.main.store_lista.baseParams={}
			proyectoseguimientoLista.main.store_lista.baseParams.BuscarBy = true;
			proyectoseguimientoLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			proyectoseguimientoLista.main.store_lista.baseParams[this.paramName] = v;
			proyectoseguimientoLista.main.store_lista.baseParams.paginar = 'si';
			proyectoseguimientoLista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    iconCls: 'icon-libro',
    store: this.store_lista,
    border:true,
    loadMask:true,
    autoWidth: true,
    autoHeight:true,
    tbar:[
			@if( in_array( array( 'de_privilegio' => 'proyectoseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.nuevo,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'Periodo', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'periodo'},
    {header: 'Ejecutor', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'ejecutor'},
		{header: 'Codigo', width:120,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nu_codigo'},
    {header: 'Descripcion', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_nombre'},
    {header: '% Ejecutado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_activo'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){

		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    }),
		sm: new Ext.grid.RowSelectionModel({
			singleSelect: true,
			/*AQUI ES DONDE ESTA EL LISTENER*/
				listeners: {
				rowselect: function(sm, row, rec) {
					var msg = Ext.get('detalle');
					msg.load({
									url: '{{ URL::to('proyecto/seguimiento/detalle') }}',
									scripts: true,
									params: {_token:'{{ csrf_token() }}', codigo:rec.json.id},
									text: 'Cargando...'
					});
					if(panel_detalle.collapsed == true)
					{
						panel_detalle.toggleCollapse();
					}
				}
			}
		})
});

/*Evento Doble Click*/
this.gridPanel_.on('rowdblclick', function( grid, row, evt){
	panel_detalle.toggleCollapse(true);
	this.record = proyectoseguimientoLista.main.store_lista.getAt(row);
	this.codigo = this.record.data["id"];
	this.msg = Ext.get('detalle');
	this.msg.load({
	    url: '{{ URL::to('proyecto/seguimiento/detalle') }}',
	    scripts: true,
	    params: {_token:'{{ csrf_token() }}', codigo:this.codigo},
	    text: "Cargando..."
	});
});

this.panel = new Ext.Panel({
	layout: "fit",
	border: false,
	padding: 5,
	items: [
		this.gridPanel_
	]
});

this.panel.render("contenedorproyectoseguimientoLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){

});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
	    url:'{{ URL::to('proyecto/seguimiento/storeLista') }}',
	    root:'data',
	    fields:[
		    {name: 'id'},
				{name: 'id_ejecutor'},
				{name: 'id_tab_ejecutores'},
		    {name: 'tx_ejecutor'},
				{name: 'nu_codigo'},
		    {name: 'de_nombre'},
				{
						name: 'ejecutor',
						convert: function(v, r) {
								return r.id_ejecutor + ' - ' + r.tx_ejecutor;
						}
				},
				{
						name: 'periodo',
						convert: function(v, r) {
								return r.fe_inicio + ' - ' + r.fe_fin;
						}
				}
	    ]
    });
    return this.store;
}
};
Ext.onReady(proyectoseguimientoLista.main.init, proyectoseguimientoLista.main);
</script>
<div id="contenedorproyectoseguimientoLista"></div>
<div id="formularioproyectoseguimiento"></div>
