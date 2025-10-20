<script type="text/javascript">
Ext.ns("proyectoLista");
proyectoLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

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
		proyectoLista.main.store_lista.baseParams={};
		proyectoLista.main.store_lista.baseParams.paginar = 'si';
		proyectoLista.main.store_lista.load();
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
			proyectoLista.main.store_lista.baseParams={}
			proyectoLista.main.store_lista.baseParams.BuscarBy = true;
			proyectoLista.main.store_lista.baseParams[this.paramName] = v;
			proyectoLista.main.store_lista.load();
		}
	}
});

this.summary = new Ext.ux.grid.GroupSummary();

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    //title:'Lista de usuario',
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
//    frame:true,
//    height:550,
    autoWidth: true,
    autoHeight:true,
    tbar:[
        this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyectos',hidden:true, menuDisabled:true,dataIndex: 'co_proyectos'},
    {header: 'Codigo', width:130,  menuDisabled:true, sortable: true,  dataIndex: 'id_proyecto'},
    {header: 'Nombre', width:400,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nombre'},
    {header: 'Monto', width:130,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto'},
    {header: 'Monto Registrado', width:130,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_registrado'},
    {header: 'Estatus', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'tx_estatus'},
    {header: 'RECURSOS SOLICITADOS', summaryType: 'sum',summaryRenderer: function(v, params, data){return 'Total';},autoWidth: true, sortable: true,groupable: false,  dataIndex: 'tx_ejecutor'},
    ],
    view: new Ext.grid.GroupingView({
        groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Proyectos" : "Proyecto"]})',
        forceFit: true,
        showGroupName: false,
        enableNoGroups: false,
	enableGroupingMenu: false,
        hideGroupedColumn: true
    }),
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    bbar: new Ext.PagingToolbar({
        pageSize: 100,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorproyectoLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
/*getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/reportes/funcion.php?op=1',
    root:'data',
    fields:[
    {name: 'co_proyectos'},
    {name: 'id_proyecto'},
    {name: 'nombre'},
    {name: 'tx_ejecutor'},
    {name: 'monto'},
    {name: 'mo_registrado'},
    {name: 'tx_estatus'},
           ]
    });
    return this.store;
},*/
getLista: function(){
this.Store = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url:'formulacion/modulos/reportes/funcion.php?op=1',
            method: 'POST'
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'total'
        },
        [
	    {name: 'co_proyectos'},
	    {name: 'id_ejecutor'},
	    {name: 'id_proyecto'},
	    {name: 'nombre'},
	    {name: 'tx_ejecutor'},
	    {name: 'monto'},
	    {name: 'mo_registrado'},
	    {name: 'tx_estatus'},
        ]),
        sortInfo:{
            field: 'id_proyecto',
            direction: "ASC"
        },
        groupField:'tx_ejecutor'

});
return this.Store;
}
};
Ext.onReady(proyectoLista.main.init, proyectoLista.main);
</script>
<div id="contenedorproyectoLista"></div>
