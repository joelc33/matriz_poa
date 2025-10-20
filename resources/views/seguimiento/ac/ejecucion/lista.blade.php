<script type="text/javascript">
Ext.ns("acejecucionLista");
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
acejecucionLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
    
this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});    
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.storeCO_EJECUTOR = this.getStoreID_EJECUTOR();

<?php $rol_planificador = array( 3, 8); ?>
//<Stores de fk>
this.id_ejecutor = new Ext.form.Hidden({
	name:'id_ejecutor',
	@if (in_array(Session::get('rol'), $rol_planificador))
        value:this.OBJ.id_ejecutor,
	@endif
});

this.id_tab_ejecutores = new Ext.form.ComboBox({
	fieldLabel:'UNIDAD EJECUTORA',
	store: this.storeCO_EJECUTOR,
	typeAhead: true,
	valueField: 'id_ejecutor',
	displayField:'de_ejecutor',
	hiddenName:'id_ejecutor',
	@if (in_array(Session::get('rol'), $rol_planificador))
		readOnly:true,
		style:'background:#c9c9c9;',
	@endif
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Unidad Ejecutora',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_ejecutor}</div></div></tpl>'),
	//listWidth:'600',
	resizable:true,
	allowBlank:false,
	listeners:{                        select: function(){
			acejecucionLista.main.store_lista.baseParams={}
			acejecucionLista.main.store_lista.baseParams.BuscarBy = true;
			acejecucionLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
                        acejecucionLista.main.store_lista.baseParams.id_tab_lapso = '{{ $lapso->id }}';
			acejecucionLista.main.store_lista.baseParams.id_ejecutor = this.getValue();
			acejecucionLista.main.store_lista.baseParams.paginar = 'si';
			acejecucionLista.main.store_lista.load();
                        }                   
	}
});
this.storeCO_EJECUTOR.load();
	@if (in_array(Session::get('rol'), $rol_planificador))
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_ejecutores,
	value:  this.OBJ.id_ejecutor,
	objStore: this.storeCO_EJECUTOR
});
	@endif

//Editar un registro
this.ficha= new Ext.Button({
    text:'Ver Ficha',
    iconCls: 'icon-pdf',
    handler:function(){
                        if(acejecucionLista.main.id_tab_ejecutores.getValue()!='' || acejecucionLista.main.id_tab_ejecutores.getValue()!=null){
			this.codigo  = acejecucionLista.main.id_tab_ejecutores.getValue();
			bajar.load({
				url: '{{ URL::to('reporte/ac/seguimiento/ficha/ejecucion') }}/{!! $lapso->id !!}/'+this.codigo
			});
                        }else{
			bajar.load({
				url: '{{ URL::to('reporte/ac/seguimiento/ficha/ejecucion') }}/{!! $lapso->id !!}'
			});                            
                        }
    }
});

this.ficha_acumulada= new Ext.Button({
    text:'Ver Ficha Acumulada',
    iconCls: 'icon-pdf',
    handler:function(){
                        if(acejecucionLista.main.id_tab_ejecutores.getValue()!='' || acejecucionLista.main.id_tab_ejecutores.getValue()!=null){
			this.codigo  = acejecucionLista.main.id_tab_ejecutores.getValue();
			bajar.load({
				url: '{{ URL::to('reporte/ac/seguimiento/ficha/acumulada/ejecucion') }}/{!! $lapso->id !!}/'+this.codigo
			});
                        }else{
			bajar.load({
				url: '{{ URL::to('reporte/ac/seguimiento/ficha/acumulada/ejecucion') }}/{!! $lapso->id !!}'
			});                            
                        }
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
		acejecucionLista.main.store_lista.baseParams={};
		acejecucionLista.main.store_lista.baseParams.paginar = 'si';
		acejecucionLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		acejecucionLista.main.store_lista.load();
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
			acejecucionLista.main.store_lista.baseParams={}
			acejecucionLista.main.store_lista.baseParams.BuscarBy = true;
			acejecucionLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			acejecucionLista.main.store_lista.baseParams[this.paramName] = v;
			acejecucionLista.main.store_lista.baseParams.paginar = 'si';
			acejecucionLista.main.store_lista.load();
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
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
			  this.ficha,'-',this.ficha_acumulada,'-',this.id_tab_ejecutores
			@endif
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'Código', width:80,  menuDisabled:true, sortable: true, dataIndex: 'co_partida'},
    {header: 'Denominación', width:200,  menuDisabled:true, sortable: true, dataIndex: 'tx_nombre'},
		{header: 'P. Inicial', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_presupuesto'},
    {header: 'P. Modificado', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_modificado_anual'},
    {header: 'P. Actualizado (Total)', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_actualizado_anual'},
		{header: 'Comprometido', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_comprometido'},
		{header: 'Causado', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_causado'},
		{header: 'Pagado', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_pagado'},
                {header: 'Disp. Financiera', width:150,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_financiera'},
                {header: 'Disp. Presupuestaria', width:150,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_presupuestaria'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
//			acejecucionLista.main.editar.enable();
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
//									url: '{{ URL::to('ac/seguimiento/ejecucion/detalle') }}',
//									scripts: true,
//									params: {_token:'{{ csrf_token() }}', codigo:rec.json.co_partida},
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
//	this.record = acejecucionLista.main.store_lista.getAt(row);
//	this.codigo = this.record.data["co_partida"];
//	this.msg = Ext.get('detalle');
//	this.msg.load({
//	    url: '{{ URL::to('ac/seguimiento/ejecucion/detalle') }}',
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

this.panel.render("contenedoracejecucionLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
acejecucionLista.main.store_lista.baseParams.id_tab_lapso = '{{ $lapso->id }}';
this.store_lista.load();
this.store_lista.on('load',function(){
//acejecucionLista.main.editar.disable();
//acejecucionLista.main.habilitar.disable();
//acejecucionLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
	    url:'{{ URL::to('ac/seguimiento/ejecucion/storeLista') }}',
	    root:'data',
	    fields:[
		    {name: 'id'},
				{name: 'co_partida'},
				{name: 'mo_presupuesto'},
		    {name: 'tx_nombre'},
				{name: 'mo_modificado_anual'},
		    {name: 'mo_actualizado_anual'},
				{name: 'mo_comprometido'},
				{name: 'mo_causado'},
				{name: 'mo_pagado'},
                                {name: 'mo_financiera'},
                                {name: 'mo_presupuestaria'}
	    ]
    });
    return this.store;
},
getStoreID_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/ejecutor/activo') }}',
        root:'data',
        fields:[
						{name: 'id'},
						{name: 'id_ejecutor'},
						{name: 'tx_ejecutor'},
						{name: 'de_ejecutor',
								convert: function(v, r) {
										return r.id_ejecutor + ' - ' + r.tx_ejecutor;
								}
						}
            ],
            filter: function(filters, value) {
                Ext.data.Store.prototype.filter.apply(this, [
                    filters,
                    value ? new RegExp(String.escape(value), 'i') : value
                ]);
            },
            listeners : {
                exception : function(proxy, response, operation) {
                    Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                }
            }
    });
    return this.store;
}
};
Ext.onReady(acejecucionLista.main.init, acejecucionLista.main);
</script>
<div id="contenedoracejecucionLista"></div>
<div id="formularioacseguimiento"></div>
