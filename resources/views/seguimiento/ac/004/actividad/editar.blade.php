<script type="text/javascript">
Ext.ns("forma004ActividadEditar");
forma004ActividadEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});
this.FECHA = paqueteComunJS.funcion.doJSON({stringData:'{!! $fecha !!}'});

@if(empty($data->id))

@else
//objeto store
this.store_lista = this.getLista();
//objeto store
@endif
//<Stores de fk>
this.storeCO_UNIDADES_MEDIDA = this.getStoreCO_UNIDADES_MEDIDA();
//<Stores de fk>

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.id_tab_ac_ae = new Ext.form.Hidden({
	name:'ac_ae',
	value:this.OBJ.id_tab_ac_ae
});

this.nb_meta = new Ext.form.TextField({
	fieldLabel:'NOMBRE DE LA ACTIVIDAD',
	name:'actividad',
	value:this.OBJ.nb_meta,
	width:550,
        readOnly:true,
	allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.id_tab_unidad_medida = new Ext.form.ComboBox({
	fieldLabel:'UNIDAD DE MEDIDA',
	store: this.storeCO_UNIDADES_MEDIDA,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_unidad_medida',
	hiddenName:'unidad_medida',
        readOnly:true,
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Unidades',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false
});

this.storeCO_UNIDADES_MEDIDA.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_unidad_medida,
	value:  this.OBJ.id_tab_unidad_medida,
	objStore: this.storeCO_UNIDADES_MEDIDA
});

this.tx_prog_anual = new Ext.form.NumberField({
	fieldLabel:'PROGRAMADO ANUAL',
	name:'programado_anual',
	value:this.OBJ.tx_prog_anual,
	allowBlank:false,
	width:200,
	maxLength: 8,
	emptyText: '00',
	decimalPrecision: 0,
 	minValue : 0,
 	maxValue : 99999999,
	msgTarget : 'Rango Entre 0 y 9',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
	allowDecimals: false,
        readOnly:true,
	allowNegative: false
});

this.fecha_inicio = new Ext.form.DateField({
	fieldLabel:'FECHA DE INICIO',
	name:'fecha_inicio',
	value:this.OBJ.fecha_inicio,
	minValue:this.FECHA.fe_ini,
	maxValue:this.FECHA.fe_fin,
	allowBlank:false,
        readOnly:true,
	width:100,
});

this.fecha_fin = new Ext.form.DateField({
	fieldLabel:'FECHA DE CULMINACIÓN',
	name:'fecha_culminacion',
	value:this.OBJ.fecha_fin,
	minValue:this.FECHA.fe_ini,
	maxValue:this.FECHA.fe_fin,
	allowBlank:false,
        readOnly:true,
	width:100
});

this.comFechaInCul = new Ext.form.CompositeField({
fieldLabel: 'FECHA DE INICIO',
items: [
	this.fecha_inicio,
             {
                   xtype: 'displayfield',
                   value: '&nbsp;&nbsp;&nbsp; FECHA DE CULMINACIÓN:',
                   width: 190
             },
	this.fecha_fin
	]
});

this.nb_responsable = new Ext.form.TextField({
	fieldLabel:'RESPONSABLE',
	name:'responsable',
	value:this.OBJ.nb_responsable,
	width:400,
        readOnly:true,
	allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.mo_modificado_anual = new Ext.form.NumberField({
	fieldLabel:'METAS MODIFICADAS',
	name:'modificado_anual',
	value:this.OBJ.nu_meta_modificada,
	//allowBlank:false,
	width:200,
	maxLength: 20,
	emptyText: '00',
	decimalPrecision: 2,
 	maxValue : 999999999999999999999,
	msgTarget : 'Rango Entre 0 y 9',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 20},
	allowDecimals: true,
	readOnly:true,
});

this.mo_actualizado_anual = new Ext.form.NumberField({
	fieldLabel:'METAS ACTUALIZADAS',
	name:'actualizado_anual',
	value:this.OBJ.nu_meta_actualizada,
	//allowBlank:false,
	width:200,
	maxLength: 20,
	emptyText: '0',
	decimalPrecision: 2,
 	minValue : 0,
 	maxValue : 999999999999999999999,
	msgTarget : 'Rango Entre 0 y 9',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 20},
	allowDecimals: true,
	allowNegative: false,
	readOnly:true,
});

this.de_desvio = new Ext.form.TextArea({
	fieldLabel:'CAUSAS DEL DESVIO',
	name:'desvio',
	value:this.OBJ.de_desvio,
	width:550,
	height:100,
	//maxLength: 250,
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});
 

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!forma004ActividadEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        forma004ActividadEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('ac/seguimiento/004/actividad/guardar') }}',
	@else
		url:'{{ URL::to('ac/seguimiento/004/actividad/guardar') }}/{!! $data->id !!}',
	@endif
		waitMsg: 'Enviando datos, por favor espere..',
		waitTitle:'Enviando',
            failure: function(form, action) {
		var errores = '';
		for(datos in action.result.msg){
			errores += action.result.msg[datos] + '<br>';
		}
                Ext.MessageBox.alert('Error en transacción', errores);
            },
            success: function(form, action) {
                 if(action.result.success){
                     Ext.MessageBox.show({
                         title: 'Mensaje',
                         msg: action.result.msg,
                         closable: false,
                         icon: Ext.MessageBox.INFO,
                         resizable: false,
			 animEl: document.body,
                         buttons: Ext.MessageBox.OK
                     });
                 }
                 forma004ActividadLista.main.store_lista.load();
                 forma004ActividadEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        forma004ActividadEditar.main.winformPanel_.close();
    }
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:false,
	border:false,
        items:[
		this.nb_meta,
		this.id_tab_unidad_medida,
		this.tx_prog_anual,
		this.comFechaInCul,
		this.nb_responsable,
		this.mo_modificado_anual,
		this.mo_actualizado_anual,
		this.de_desvio
		]
});

this.panelDatos1 = new Ext.Panel({
    title: 'META FISICA',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.fieldset1
	]
});

@if(empty($data->id))

this.gridPanel_ = new Ext.Panel ({
	title: 'META FINANCIERA',
	bodyStyle:'padding:5px;',
	autoHeight:true,
	baseCls : 'x-plain',
	html    : 'Debe guardar primero la meta fisica para luego agregar las metas financieras.',
	cls     : 'icon-autorizacion',
	region  : 'north'
});

@else

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
	width:160,
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
		forma004ActividadEditar.main.store_lista.baseParams={};
		forma004ActividadEditar.main.store_lista.baseParams.paginar = 'si';
		forma004ActividadEditar.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		forma004ActividadEditar.main.store_lista.baseParams.tramite = {{ $data->id }};
		forma004ActividadEditar.main.store_lista.load();
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
			forma004ActividadEditar.main.store_lista.baseParams={}
			forma004ActividadEditar.main.store_lista.baseParams.BuscarBy = true;
			forma004ActividadEditar.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			forma004ActividadEditar.main.store_lista.baseParams.tramite = {{ $data->id }};
			forma004ActividadEditar.main.store_lista.baseParams[this.paramName] = v;
			forma004ActividadEditar.main.store_lista.baseParams.paginar = 'si';
			forma004ActividadEditar.main.store_lista.load();
		}
	}
});

this.nuevo = new Ext.Button({
	text:'Agregar',
	iconCls: 'icon-nuevo',
	handler: function(boton){
		//tramiteTimbreLista.main.mascara.show();
		this.msg = Ext.get('formulariometafinanciera');
		this.msg.load({
		 url:"{{ URL::to('ac/seguimiento/004/actividad/financiera/nuevo') }}/{{ $data->id }}",
		 scripts: true,
		 text: "Cargando.."
		});
	}
});

this.editar = new Ext.Button({
	text:'Editar',
	iconCls: 'icon-editar',
	handler: function(boton){
		this.codigo  = forma004ActividadEditar.main.gridPanel_.getSelectionModel().getSelected().get('id');
		//forma004ActividadEditar.main.mascara.show();
	  this.msg = Ext.get('formulariometafinanciera');
	  this.msg.load({
	      url:"{{ URL::to('ac/seguimiento/004/actividad/financiera/editar') }}/"+this.codigo,
	      scripts: true,
	      text: "Cargando.."
	  });
	}
});

this.eliminar = new Ext.Button({
	text:'Elimiar',
	iconCls: 'icon-eliminar',
	handler: function(boton){
		this.codigo  = forma004ActividadEditar.main.gridPanel_.getSelectionModel().getSelected().get('id');
		Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Eliminar Variable?', function(boton){
		if(boton=="yes"){
	        Ext.Ajax.request({
	            method:'POST',
	            url:'{{ URL::to('ac/seguimiento/004/actividad/financiera/eliminar') }}',
	            params:{
			_token: '{{ csrf_token() }}',
	                id: forma004ActividadEditar.main.gridPanel_.getSelectionModel().getSelected().get('id')
	            },
	            success:function(result, request ) {
	                obj = Ext.util.JSON.decode(result.responseText);
	                if(obj.success=="true"){
			    forma004ActividadEditar.main.store_lista.load();
	                    Ext.Msg.alert("Notificación",obj.msg);
	                }else{
	                    Ext.Msg.alert("Notificación",obj.msg);
	                }
	                forma004ActividadEditar.main.mascara.hide();
	            }});
		}});
	}
});

this.editar.disable();
this.eliminar.disable();

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    title: 'META FINANCIERA',
    border:false,
    store: this.store_lista,
    loadMask:true,
    height:300,
    tbar:[
//      this.nuevo,'-',this.editar,'-',this.eliminar,'-',this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'PRESUPUESTO', width:160,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_presupuesto'},
    {header: 'PARTIDA', width:100,  menuDisabled:true, sortable: true, dataIndex: 'co_partida'},
    {header: 'FUENTE DE FINANCIAMIENTO', width:300,  menuDisabled:true, sortable: true, dataIndex: 'de_fuente_financiamiento'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
			forma004ActividadEditar.main.editar.enable();
			forma004ActividadEditar.main.eliminar.enable();
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 15,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

@endif

this.panel = new Ext.TabPanel({
    activeTab:0,
    height:350,
    enableTabScroll:true,
    deferredRender: false,
		border:false,
    items:[
	this.panelDatos1,
	this.gridPanel_
	]
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:800,
	labelWidth: 180,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:0px;',
	items:[
		this._token,
		this.id_tab_ac_ae,
		this.panel
	]
});

this.winformPanel_ = new Ext.Window({
    title:'F004: DESVIO DE LA GESTIÓN',
    modal:true,
    constrain:true,
width:814,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
forma004ActividadLista.main.mascara.hide();

@if(empty($data->id))

@else
//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
forma004ActividadEditar.main.editar.disable();
forma004ActividadEditar.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
@endif
},
getStoreCO_UNIDADES_MEDIDA:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/unidadmedida') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_unidad_medida'}
            ]
    });
    return this.store;
}
@if(empty($data->id))

@else
,getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'{{ URL::to('ac/seguimiento/004/actividad/financiera/storeLista') }}/{{ $data->id }}',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'mo_presupuesto'},
    {name: 'co_partida'},
    {name: 'de_fuente_financiamiento'},
           ]
    });
    return this.store;
}
@endif
};
Ext.onReady(forma004ActividadEditar.main.init, forma004ActividadEditar.main);
</script>
<div id="formulariometafinanciera"></div>
