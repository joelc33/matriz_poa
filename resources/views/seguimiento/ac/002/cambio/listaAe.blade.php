<script type="text/javascript">
Ext.ns("forma002ActividadListaCambio");
function actividadEstado(val){
	if(val==true){
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/check.png') }}" style="cursor:pointer;">'+' <span style="color:green;"> Cargado</span>'+'</div></tpl>';
	}else{
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> Pendiente</span>'+'</div></tpl>';
	}
return val;
};
function changeEstatus( in_002, de_estatus){
	if(in_002==true){
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/check.png') }}" style="cursor:pointer;">'+' <span style="color:green;"> '+de_estatus+'</span>'+'</div></tpl>';
	}else{
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> '+de_estatus+'</span>'+'</div></tpl>';
	}
return val;
};
forma002ActividadListaCambio.main = {
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
	this.codigo  = forma002ActividadListaCambio.main.gridPanel_.getSelectionModel().getSelected().get('id');
			this.msg = Ext.get('formularioeditar');
			this.msg.load({
			 url:"{{ URL::to('seguimiento/ac/002/cambio/editar') }}/"+this.codigo,
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
		{header: 'Codigo', width:50,  menuDisabled:true, sortable: true, dataIndex: 'codigo'},
		{header: 'Actividad', width:300,  menuDisabled:true, sortable: true, dataIndex: 'nb_meta'},
    {header: 'Programado', width:120,  menuDisabled:true, sortable: true,  dataIndex: 'programado'},
    {header: 'Inicio', width:80,  menuDisabled:true, sortable: true,  dataIndex: 'fecha_inicio'},
    {header: 'Final', width:80,  menuDisabled:true, sortable: true,  dataIndex: 'fecha_fin'},
    {header: 'Estatus', width:130,  menuDisabled:true, sortable: true, dataIndex: 'estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			forma002ActividadListaCambio.main.editar.enable();
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
forma002ListaCambio.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.baseParams.ac_ae = '{{ $data['id'] }}';
this.store_lista.load();
this.store_lista.on('load',function(){
forma002ActividadListaCambio.main.editar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('seguimiento/ac/002/cambio/storeListaAe') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'codigo'},
    {name: 'nb_meta'},
    {name: 'de_unidad_medida'},
    {name: 'fecha_inicio'},
    {name: 'fecha_fin'},
    {
    name: 'estatus',
    convert: function(v, r) {
                    return changeEstatus( r.in_002, r.de_estatus);
    }
    },
    {
        name: 'programado',
        convert: function(v, r) {
            return r.tx_prog_anual + ' ' + r.de_unidad_medida;
        }
    }
           ]
    });
    return this.store;
}
};
Ext.onReady(forma002ActividadListaCambio.main.init, forma002ActividadListaCambio.main);
</script>
<div id="formularioeditar"></div>