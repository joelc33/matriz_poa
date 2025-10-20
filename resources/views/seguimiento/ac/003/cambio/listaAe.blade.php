<script type="text/javascript">
Ext.ns("forma003ActividadListaCambio");
function changeEstatus( id_tab_estatus, de_estatus){
	if(id_tab_estatus==6){
	return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/check.png') }}" style="cursor:pointer;">'+' <span style="color:green;"> '+de_estatus+'</span>'+'</div></tpl>';            
	}else{
            return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> '+de_estatus+'</span>'+'</div></tpl>';
	}
return val;
};
forma003ActividadListaCambio.main = {
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.editar = new Ext.Button({
	text:'Editar Actividad',
	iconCls: 'icon-editar',
	handler:function(){
	this.codigo  = forma003ActividadListaCambio.main.gridPanel_.getSelectionModel().getSelected().get('id');
			this.msg = Ext.get('formularioeditar');
			this.msg.load({
			 url:"{{ URL::to('seguimiento/ac/003/cambio/editar') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
			});
	}
});


//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    iconCls: 'icon-libro',
    store: this.store_lista,
    border:false,
    loadMask:true,
    autoWidth: true,
    height:510,
    tbar:[
        this.editar
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		/*{header: 'Codigo', width:50,  menuDisabled:true, sortable: true, dataIndex: 'codigo'},*/
		{header: 'Actividad', width:250,  menuDisabled:true, sortable: true, dataIndex: 'actividad'},
    {header: 'Fuente Financimiento', width:220,  menuDisabled:true, sortable: true,  dataIndex: 'de_fuente_financiamiento'},
    {header: 'Presupuesto Anual', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_presupuesto'},
		{header: 'Categoria', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'categoria'},
    {header: 'Estatus', width:80,  menuDisabled:true, sortable: true, dataIndex: 'estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			forma003ActividadListaCambio.main.editar.enable();
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
    title:'AC: {{ $data['nu_codigo'] }} | AE: {{ $data['de_nombre'] }}',
    modal:true,
    constrain:true,
    width:814,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
      this.gridPanel_
    ]
});
this.winformPanel_.show();
forma003ListaCambio.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.baseParams.ac_ae = '{{ $data['id'] }}';
this.store_lista.load();
this.store_lista.on('load',function(){
forma003ActividadListaCambio.main.editar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('seguimiento/ac/003/cambio/storeListaAe') }}',
    root:'data',
    fields:[
    {name: 'id'},
		{name: 'codigo'},
    {name: 'nb_meta'},
    {name: 'de_fuente_financiamiento'},
    {name: 'mo_presupuesto'},
    {name: 'in_cargado'},
    {
    name: 'estatus',
    convert: function(v, r) {
                    return changeEstatus( r.id_tab_estatus, r.de_estatus);
    }
    },    
    {
        name: 'categoria',
        convert: function(v, r) {
            return r.co_sector + '.' + r.nu_original + '.00.0' + r.nu_numero + '.' + r.co_partida;
        }
    },
    {
        name: 'actividad',
        convert: function(v, r) {
            return r.codigo + ' - ' + r.nb_meta;
        }
    }
           ]
    });
    return this.store;
}
};
Ext.onReady(forma003ActividadListaCambio.main.init, forma003ActividadListaCambio.main);
</script>
<div id="formularioeditar"></div>