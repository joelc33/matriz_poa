<script type="text/javascript">
Ext.ns("desagregadoLista");
desagregadoLista.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

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
		desagregadoLista.main.store_lista.baseParams={};
		desagregadoLista.main.store_lista.baseParams.paginar = 'si';
		desagregadoLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
    desagregadoLista.main.store_lista.baseParams.ae = {{ $data->co_proyecto_acc_espec }};
		desagregadoLista.main.store_lista.load();
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
			desagregadoLista.main.store_lista.baseParams={}
			desagregadoLista.main.store_lista.baseParams.BuscarBy = true;
			desagregadoLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
      desagregadoLista.main.store_lista.baseParams.ae = {{ $data->co_proyecto_acc_espec }};
			desagregadoLista.main.store_lista.baseParams[this.paramName] = v;
			desagregadoLista.main.store_lista.baseParams.paginar = 'si';
			desagregadoLista.main.store_lista.load();
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
    height:400,
    tbar:[
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'PARTIDA', width:100,  menuDisabled:true, sortable: true, dataIndex: 'co_partida'},
		{header: 'APLICACION', width:100,  menuDisabled:true, sortable: true, dataIndex: 'nu_aplicacion'},
    {header: 'DENOMINACIÓN', width:330,  menuDisabled:true, sortable: true, dataIndex: 'de_denominacion'},
    {header: 'MONTO (BS)', width:130,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_partida'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.winformPanel_ = new Ext.Window({
    title:'Partidas Desagregadas de la Acción Específica: '+this.OBJ.tx_codigo+' - '+this.OBJ.descripcion,
    modal:true,
    constrain:true,
    width:714,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
      this.gridPanel_
    ]
});
this.winformPanel_.show();
partidaLista.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.baseParams.ae = {{ $data->co_proyecto_acc_espec }};
this.store_lista.load();

},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('proyecto/ae/partida/desagregado/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
		{name: 'co_partida'},
    {name: 'de_denominacion'},
    {name: 'nu_aplicacion'},
    {name: 'mo_partida'},
           ]
    });
    return this.store;
}
};
Ext.onReady(desagregadoLista.main.init, desagregadoLista.main);
</script>
