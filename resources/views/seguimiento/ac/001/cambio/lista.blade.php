<script type="text/javascript">
Ext.ns("forma001ListaCambio");
function changeEstatus( in_001, de_estatus){
	if(in_001==true){
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/check.png') }}" style="cursor:pointer;">'+' <span style="color:green;"> '+de_estatus+'</span>'+'</div></tpl>';
	}else{
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> '+de_estatus+'</span>'+'</div></tpl>';
	}
return val;
};
forma001ListaCambio.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.storeCO_TIPO_PERIODO = this.getStoreCO_TIPO_PERIODO();

//Editar un registro
this.ficha= new Ext.Button({
    text:'Ver Ficha',
    iconCls: 'icon-pdf',
    handler:function(){
			this.codigo  = forma001ListaCambio.main.gridPanel_.getSelectionModel().getSelected().get('id_tab_ac');
			bajar.load({
				url: '{{ URL::to('reporte/ac/seguimiento/ficha/001') }}/'+this.codigo
			});
    }
});

this.ficha.disable();


this.editar = new Ext.Button({
	text:'Editar AE',
	iconCls: 'icon-editar',
	handler:function(){
	this.codigo  = forma001ListaCambio.main.gridPanel_.getSelectionModel().getSelected().get('id');
	forma001ListaCambio.main.mascara.show();
			this.msg = Ext.get('formularioacseguimiento');
			this.msg.load({
			 url:"{{ URL::to('seguimiento/ac/001/cambio/editar') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
			});
	}
});
this.editar.disable();

this.id_tab_tipo_periodo = new Ext.form.ComboBox({
	fieldLabel:'Periodo',
	store: this.storeCO_TIPO_PERIODO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_tipo_periodo',
	hiddenName:'tipo_periodo',       
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Periodo...',
	selectOnFocus: true,
	mode: 'local',
	width:150,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_tipo_periodo}</div></div></tpl>'),
	resizable:true,
	allowBlank:false,
        listeners:{                        select: function(){
        forma001ListaCambio.main.store_lista.baseParams={}
        forma001ListaCambio.main.store_lista.baseParams.BuscarBy = true;
        forma001ListaCambio.main.store_lista.baseParams._token = '{{ csrf_token() }}';
        forma001ListaCambio.main.store_lista.baseParams.variable = forma001ListaCambio.main.buscador.getValue();
        forma001ListaCambio.main.store_lista.baseParams.id_tab_tipo_periodo = this.getValue();
        forma001ListaCambio.main.store_lista.baseParams.paginar = 'si';
        forma001ListaCambio.main.store_lista.load();
                }                   
}
});


this.storeCO_TIPO_PERIODO.load({
    params: {periodo:3, _token:'{{ csrf_token() }}'}
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
                forma001ListaCambio.main.id_tab_tipo_periodo.clearValue();
		forma001ListaCambio.main.store_lista.baseParams={};
		forma001ListaCambio.main.store_lista.baseParams.paginar = 'si';
		forma001ListaCambio.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		forma001ListaCambio.main.store_lista.load();
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
			forma001ListaCambio.main.store_lista.baseParams={}
			forma001ListaCambio.main.store_lista.baseParams.BuscarBy = true;
			forma001ListaCambio.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			forma001ListaCambio.main.store_lista.baseParams[this.paramName] = v;
                        forma001ListaCambio.main.store_lista.baseParams.id_tab_tipo_periodo = forma001ListaCambio.main.id_tab_tipo_periodo.getValue();
			forma001ListaCambio.main.store_lista.baseParams.paginar = 'si';
			forma001ListaCambio.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.001.ficha', 'in_habilitado' => true), Session::get('credencial') ))
			  this.editar,'-',this.id_tab_tipo_periodo,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'id_tab_ac',hidden:true, menuDisabled:true,dataIndex: 'id_tab_ac'},
		{header: 'Periodo', width:150,  menuDisabled:true, sortable: true, dataIndex: 'de_lapso'},
    {header: 'Ejecutor', width:200,  menuDisabled:true, sortable: true, dataIndex: 'ejecutor'},
		{header: 'Codigo', width:120,  menuDisabled:true, sortable: true, dataIndex: 'nu_codigo'},
    {header: 'Fecha', width:140,  menuDisabled:true, sortable: true, dataIndex: 'fe_solicitud'},
    {header: 'Estatus', width:80,  menuDisabled:true, sortable: true, dataIndex: 'estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			forma001ListaCambio.main.ficha.enable();
                        forma001ListaCambio.main.editar.enable();
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
//					var msg = Ext.get('detalle');
//					msg.load({
//									url: '{{ URL::to('seguimiento/ac/001/cambio/detalle') }}',
//									scripts: true,
//									params: {_token:'{{ csrf_token() }}', codigo:rec.json.id},
//									text: 'Cargando...'
//					});
//					if(panel_detalle.collapsed == true)
//					{
//						panel_detalle.toggleCollapse();
//					}
				}
			}
		})
});

/*Evento Doble Click*/
//this.gridPanel_.on('rowdblclick', function( grid, row, evt){
//	panel_detalle.toggleCollapse(true);
//	this.record = forma001ListaCambio.main.store_lista.getAt(row);
//	this.codigo = this.record.data["id"];
//	this.msg = Ext.get('detalle');
//	this.msg.load({
//	    url: '{{ URL::to('seguimiento/ac/001/cambio/detalle') }}',
//	    scripts: true,
//	    params: {_token:'{{ csrf_token() }}', codigo:this.codigo},
//	    text: "Cargando..."
//	});
//});

this.panel = new Ext.Panel({
	layout: "fit",
	border: false,
	padding: 5,
	items: [
		this.gridPanel_
	]
});

this.panel.render("contenedorforma001ListaCambio");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
forma001ListaCambio.main.ficha.disable();
forma001ListaCambio.main.editar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
	    url:'{{ URL::to('seguimiento/ac/001/cambio/storeLista') }}',
	    root:'data',
	    fields:[
		    {name: 'id'},
                    {name: 'id_tab_ac'},
				{name: 'id_ejecutor'},
				{name: 'id_tab_ejecutores'},
		    {name: 'tx_ejecutor_ac'},
				{name: 'nu_codigo'},
		    {name: 'de_ac'},
                    {name: 'de_lapso'},
				{name: 'in_001'},
				{name: 'fe_solicitud'},
				{
						name: 'ejecutor',
						convert: function(v, r) {
								return r.id_ejecutor + ' - ' + r.tx_ejecutor_ac;
						}
				},
				{
						name: 'periodo',
						convert: function(v, r) {
								return r.fe_inicio + ' - ' + r.fe_fin;
						}
				},
				{
						name: 'estatus',
						convert: function(v, r) {
								return changeEstatus( r.in_001, r.de_estatus);
						}
				}
	    ]
    });
    return this.store;
},
getStoreCO_TIPO_PERIODO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/periodo/tipo') }}',
        root:'data',
        fields:[
            {name: 'id'},
            {name: 'de_tipo_periodo'}
            ]
    });
    return this.store;
}
};
Ext.onReady(forma001ListaCambio.main.init, forma001ListaCambio.main);
</script>
<div id="contenedorforma001ListaCambio"></div>
<div id="formularioacseguimiento"></div>
