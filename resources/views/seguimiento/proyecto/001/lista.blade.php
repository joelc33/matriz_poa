<script type="text/javascript">
Ext.ns("prforma001Lista");
function change(val){
	if(val==true){
	    return '<tpl><div class="x-grid-row">'+'<img src="{{ asset('images/16x16/check.png') }}" style="cursor:pointer;">'+' <span style="color:green;"> Cargado</span>'+'</div></tpl>';
	}else{
	    return '<tpl><div class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> Pendiente</span>'+'</div></tpl>';
	}
return val;
};
prforma001Lista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

//Editar un registro
this.ficha= new Ext.Button({
    text:'Ver Ficha',
    iconCls: 'icon-pdf',
    handler:function(){
			this.codigo  = prforma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
			bajar.load({
				url: '{{ URL::to('reporte/proyecto/seguimiento/ficha') }}/'+this.codigo
			});
    }
});

this.ficha.disable();

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
		prforma001Lista.main.store_lista.baseParams={};
		prforma001Lista.main.store_lista.baseParams.paginar = 'si';
		prforma001Lista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		prforma001Lista.main.store_lista.load();
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
			prforma001Lista.main.store_lista.baseParams={}
			prforma001Lista.main.store_lista.baseParams.BuscarBy = true;
			prforma001Lista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			prforma001Lista.main.store_lista.baseParams[this.paramName] = v;
			prforma001Lista.main.store_lista.baseParams.paginar = 'si';
			prforma001Lista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'proyectoseguimiento.001.ficha', 'in_habilitado' => true), Session::get('credencial') ))
			  this.ficha,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'Periodo', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'periodo'},
    {header: 'Ejecutor', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'ejecutor'},
		{header: 'Codigo', width:120,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nu_codigo'},
    {header: 'Descripcion', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_proyecto'},
    {header: 'Estatus', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_001'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			prforma001Lista.main.ficha.enable();
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
									url: '{{ URL::to('proyecto/seguimiento/001/detalle') }}',
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
	this.record = prforma001Lista.main.store_lista.getAt(row);
	this.codigo = this.record.data["id"];
	this.msg = Ext.get('detalle');
	this.msg.load({
	    url: '{{ URL::to('proyecto/seguimiento/001/detalle') }}',
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

this.panel.render("contenedorprforma001Lista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
prforma001Lista.main.ficha.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
	    url:'{{ URL::to('proyecto/seguimiento/001/storeLista') }}',
	    root:'data',
	    fields:[
		    {name: 'id'},
				{name: 'id_ejecutor'},
				{name: 'id_tab_ejecutores'},
		    {name: 'tx_ejecutor'},
				{name: 'nu_codigo'},
		    {name: 'de_proyecto'},
				{name: 'in_001'},
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
Ext.onReady(prforma001Lista.main.init, prforma001Lista.main);
</script>
<div id="contenedorprforma001Lista"></div>
<div id="formularioproyectoseguimiento"></div>
