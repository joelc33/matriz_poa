<script type="text/javascript">
Ext.ns("forma003DetalleLista{!! $data['id'] !!}");
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
forma003DetalleLista{!! $data['id'] !!}.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

//Editar un registro
this.editar= new Ext.Button({
    text:'Ver Actividades',
    iconCls: 'icon-accion_fisica',
    handler:function(){
	this.codigo  = forma003DetalleLista{!! $data['id'] !!}.main.gridPanel_.getSelectionModel().getSelected().get('id');
	forma003DetalleLista{!! $data['id'] !!}.main.mascara.show();
        this.msg = Ext.get('forma003Detalle');
        this.msg.load({
         url:"{{ URL::to('ac/seguimiento/003/actividad/lista') }}/"+this.codigo,
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.editar.disable();

this.enviar = new Ext.Button({
	text:'Validar',
	iconCls: 'icon-report',
	handler:function(){
	this.codigo  = forma003DetalleLista{!! $data['id'] !!}.main.gridPanel_.getSelectionModel().getSelected().get('id');

        Ext.MessageBox.confirm('Confirmación', '¿Realmente desea enviar los cambios? <br><b>Nota:</b> No podra realizar mas modificaciones! <br><b>Nota:</b> Debe esperar por aprobacion de parte de Planificacion.', function(boton){
        if(boton=="yes"){

        Ext.Ajax.request({
            method:'POST',
           url:"{{ URL::to('ac/seguimiento/003/actividad/enviarAprobar') }}",
            params:{
		_token: '{{ csrf_token() }}',
                id: forma003DetalleLista{!! $data['id'] !!}.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }

            }});

			}
			});
	}
});
this.enviar.disable();

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
		forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams={};
		forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams.paginar = 'si';
		forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams.ac = '{{ $data['id'] }}';
		forma003DetalleLista{!! $data['id'] !!}.main.store_lista.load();
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
			forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams={}
			forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams.BuscarBy = true;
			forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams.ac = '{{ $data['id'] }}';
			forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams[this.paramName] = v;
			forma003DetalleLista{!! $data['id'] !!}.main.store_lista.baseParams.paginar = 'si';
			forma003DetalleLista{!! $data['id'] !!}.main.store_lista.load();
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
    autoHeight:true,
    tbar:[
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',this.enviar,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
		{header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'Numero', width:60,  menuDisabled:true, sortable: true,  dataIndex: 'nu_numero'},
    {header: 'Nombre', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_nombre'},
    {header: 'Ejecutor', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'ejecutor'},
    {header: 'Programado Anual', width:120,  menuDisabled:true, sortable: true, dataIndex: 'programado'},
    {header: 'Inicio', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'fecha_inicio'},
    {header: 'Final', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'fecha_fin'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			forma003DetalleLista{!! $data['id'] !!}.main.editar.enable();
                        forma003DetalleLista{!! $data['id'] !!}.main.enviar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorforma003DetalleLista{!! $data['id'] !!}");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.baseParams.ac = '{{ $data['id'] }}';
this.store_lista.load();
this.store_lista.on('load',function(){
forma003DetalleLista{!! $data['id'] !!}.main.editar.disable();
forma003DetalleLista{!! $data['id'] !!}.main.enviar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('ac/seguimiento/003/datos/storeLista') }}',
    root:'data',
    fields:[
			{name: 'id'},
	    {name: 'nu_numero'},
	    {name: 'de_nombre'},
	    {name: 'nb_meta'},
	    {name: 'tx_ejecutor_ac'},
	    {name: 'tx_prog_anual'},
	    {name: 'fecha_inicio'},
	    {name: 'fecha_fin'},
	    {name: 'nb_responsable'},
	    {name: 'mo_cargado'},
			{
					name: 'ejecutor',
					convert: function(v, r) {
							return r.id_ejecutor + ' - ' + r.tx_ejecutor_ac;
					}
			},
			{
					name: 'programado',
					convert: function(v, r) {
							return r.meta + ' ' + r.de_unidad_medida;
					}
			}
           ]
    });
    return this.store;
}
};
Ext.onReady(forma003DetalleLista{!! $data['id'] !!}.main.init, forma003DetalleLista{!! $data['id'] !!}.main);
</script>
<div id="contenedorforma003DetalleLista{!! $data['id'] !!}"></div>
<div id="forma003Detalle"></div>
<div id="forma003Actividad"></div>
