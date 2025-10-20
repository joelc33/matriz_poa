<script type="text/javascript">
Ext.ns("fuenteLista");
fuenteLista.main = {
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
		fuenteLista.main.store_lista.baseParams={};
		fuenteLista.main.store_lista.baseParams.paginar = 'si';
		fuenteLista.main.store_lista.load();
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
			fuenteLista.main.store_lista.baseParams={}
			fuenteLista.main.store_lista.baseParams.BuscarBy = true;
			fuenteLista.main.store_lista.baseParams[this.paramName] = v;
			fuenteLista.main.store_lista.baseParams.paginar = 'si';
			fuenteLista.main.store_lista.load();
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
    {header: 'co_proyecto_distribucion',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_distribucion'},
    {header: 'RECURSOS SOLICITADOS', width:300,  menuDisabled:true, sortable: true, summaryRenderer: function(v, params, data){return '<b>SUB-TOTAL</b>';}, dataIndex: 'tx_tipo_fondo'},
    {header: 'MONTO PROGRAMADO DEL EJERCICIO A FORMULAR', width:300,  menuDisabled:true, sortable: true, summaryType: 'moFondo', renderer: formatoNumero, dataIndex: 'mo_fondo'},
    {header: 'RECURSOS SOLICITADOS', summaryType: 'sum',summaryRenderer: function(v, params, data){return 'Total';},autoWidth: true, sortable: true,groupable: false,  dataIndex: 'tx_tipo_recurso'},
    ],
    view: new Ext.grid.GroupingView({
        groupTextTpl: '{text}',
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

this.gridPanel_.render("contenedorfuenteLista");

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
            url:'formulacion/modulos/reportes/funcion.php?op=2',
            method: 'POST'
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'total'
        },
        [
	    {name: 'co_proyecto_distribucion'},
	    {name: 'tx_codigo_recurso'},
	    {name: 'id_proyecto'},
	    {name: 'tx_tipo_fondo'},
	    {name: 'mo_fondo'},
	    {name: 'edo_reg'},
	    {name: 'tx_tipo_recurso'},
        ]),
        sortInfo:{
            field: 'tx_codigo_recurso',
            direction: "ASC"
        },
        groupField:'tx_tipo_recurso'

});
return this.Store;
}
};
Ext.onReady(fuenteLista.main.init, fuenteLista.main);
</script>
<div id="contenedorfuenteLista"></div>
