<script type="text/javascript">
Ext.ns("forma003ActividadLista");
function actividadEstado(val){
	if(val==true){
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/check.png') }}" style="cursor:pointer;">'+' <span style="color:green;"> Cargado</span>'+'</div></tpl>';
	}else{
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> Pendiente</span>'+'</div></tpl>';
	}
return val;
};
forma003ActividadLista.main = {
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});


this.nuevo= new Ext.Button({
    text:'Nueva Actividad',
    iconCls: 'icon-nuevo',
    handler:function(){
	forma003ActividadLista.main.mascara.show();
        this.msg = Ext.get('forma003Actividad');
        this.msg.load({
         url:"{{ URL::to('ac/seguimiento/003/actividad/nuevo') }}/{{ $data['id'] }}",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.editar_financiera= new Ext.Button({
    text:'Editar Metas Financieras',
    iconCls: 'icon-editar',
    handler:function(){
        this.codigo  = forma003ActividadLista.main.gridPanel_.getSelectionModel().getSelected().get('id_tab_meta_fisica');
	forma003ActividadLista.main.mascara.show();
        this.msg = Ext.get('forma003Actividad');
        this.msg.load({
          url:"{{ URL::to('ac/seguimiento/003/actividad/editarFinanciera') }}/"+this.codigo,
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.editar_financiera.disable();
//Editar un registro
this.editar= new Ext.Button({
    text:'Editar Actividades',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = forma003ActividadLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	forma003ActividadLista.main.mascara.show();
        this.msg = Ext.get('forma003Actividad');
        this.msg.load({
         url:"{{ URL::to('ac/seguimiento/003/actividad/editar') }}/"+this.codigo,
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.editar.disable();

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
		forma003ActividadLista.main.store_lista.baseParams={};
		forma003ActividadLista.main.store_lista.baseParams.paginar = 'si';
		forma003ActividadLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
    forma003ActividadLista.main.store_lista.baseParams.ac_ae = '{{ $data['id'] }}';
		forma003ActividadLista.main.store_lista.load();
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
			forma003ActividadLista.main.store_lista.baseParams={}
			forma003ActividadLista.main.store_lista.baseParams.BuscarBy = true;
			forma003ActividadLista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
      forma003ActividadLista.main.store_lista.baseParams.ac_ae = '{{ $data['id'] }}';
			forma003ActividadLista.main.store_lista.baseParams[this.paramName] = v;
			forma003ActividadLista.main.store_lista.baseParams.paginar = 'si';
			forma003ActividadLista.main.store_lista.load();
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
    height:510,
    tbar:[
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar_financiera,'-',
			@endif
                        @if( in_array( array( 'de_privilegio' => 'acseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
				this.editar,'-',
			@endif
				this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'id_tab_meta_fisica',hidden:true, menuDisabled:true,dataIndex: 'id_tab_meta_fisica'},
		/*{header: 'Codigo', width:50,  menuDisabled:true, sortable: true, dataIndex: 'codigo'},*/
		{header: 'Actividad', width:250,  menuDisabled:true, sortable: true, dataIndex: 'actividad'},
    {header: 'Fuente Financimiento', width:220,  menuDisabled:true, sortable: true,  dataIndex: 'de_fuente_financiamiento'},
    {header: 'Presupuesto Anual', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_presupuesto'},
		{header: 'Categoria', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'categoria'},
    {header: 'Estatus', width:80,  menuDisabled:true, sortable: true, renderer: actividadEstado, dataIndex: 'in_cargado'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			forma003ActividadLista.main.editar.enable();
                        if(forma003ActividadLista.main.gridPanel_.getSelectionModel().getSelected().get('in_enviado')==false){
                        forma003ActividadLista.main.editar_financiera.enable();
                    }else{
                       forma003ActividadLista.main.editar_financiera.disable(); 
                        }
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
    title:'Formulario: METAS FINANCIERAS',
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
forma003DetalleLista{!! $data['id_tab_ac'] !!}.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.baseParams.ac_ae = '{{ $data['id'] }}';
this.store_lista.load();
this.store_lista.on('load',function(){
forma003ActividadLista.main.editar.disable();
forma003ActividadLista.main.editar_financiera.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('ac/seguimiento/003/actividad/storeLista') }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'id_tab_meta_fisica'},
		{name: 'codigo'},
    {name: 'nb_meta'},
    {name: 'de_fuente_financiamiento'},
    {name: 'mo_presupuesto'},
    {name: 'in_cargado'},
    {name: 'in_enviado'},
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
Ext.onReady(forma003ActividadLista.main.init, forma003ActividadLista.main);
</script>
