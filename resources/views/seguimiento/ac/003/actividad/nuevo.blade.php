<script type="text/javascript">
Ext.ns("forma004ActividadEditar");
forma004ActividadEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});
this.FECHA = paqueteComunJS.funcion.doJSON({stringData:'{!! $fecha !!}'});

//<Stores de fk>
this.storeCO_UNIDADES_MEDIDA = this.getStoreCO_UNIDADES_MEDIDA();
//<Stores de fk>

//<token>
this.JsonDetalle = new Ext.form.Hidden({
	name:'json_detalle',
	value:''
});

this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.id_tab_ac_ae = new Ext.form.Hidden({
	name:'ac_ae',
	value:this.OBJ.id_tab_ac_ae
});

this.eliminar= new Ext.Button({
	text:'Quitar',
	iconCls: 'icon-eliminar',
	handler: function(boton){
		forma004ActividadEditar.main.eliminarRequerimiento();
	}
});

this.eliminar.disable();

this.Registro = Ext.data.Record.create([
	{ name: 'co_metas_detalle', type: 'number'},
	{ name: 'co_metas', type: 'number'},
	{ name: 'co_municipio', type: 'number'},
	{ name: 'tx_municipio', type: 'string'},
	{ name: 'co_parroquia', type: 'number'},
	{ name: 'tx_parroquia', type: 'string'},
	{ name: 'mo_presupuesto', type: 'number'},
	{ name: 'co_partida', type: 'number'},
	{ name: 'co_fuente_financiamiento', type: 'number' },
	{ name: 'tx_fuente_financiamiento', type: 'string' },
]);

this.store_lista =  new Ext.data.GroupingStore({
	reader: new Ext.data.JsonReader({fields:forma004ActividadEditar.main.Registro})
});


this.nb_meta = new Ext.form.TextField({
	fieldLabel:'NOMBRE DE LA ACTIVIDAD',
	name:'actividad',
	value:this.OBJ.nb_meta,
	width:550,
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
	fieldLabel:'META MODIFICADA',
	name:'programado_anual',
	allowBlank:false,
	width:200,
	maxLength: 8,
	decimalPrecision: 0,
 	minValue : 0,
 	maxValue : 99999999,
	msgTarget : 'Rango Entre 0 y 9',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
	allowDecimals: false,
	allowNegative: false
});

this.fecha_inicio = new Ext.form.DateField({
	fieldLabel:'FECHA DE INICIO',
	name:'fecha_inicio',
	value:this.FECHA.fe_ini,
	minValue:this.FECHA.fe_ini,
	maxValue:this.FECHA.fe_fin,
	allowBlank:false,
	width:100,
});

this.fecha_fin = new Ext.form.DateField({
	fieldLabel:'FECHA DE CULMINACIÓN',
	name:'fecha_culminacion',
	value:this.FECHA.fe_fin,
	minValue:this.FECHA.fe_ini,
	maxValue:this.FECHA.fe_fin,
	allowBlank:false,
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
	allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
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
		 url:"{{ URL::to('ac/seguimiento/002/actividad/financiera/nuevo') }}/"+forma004ActividadEditar.main.OBJ.id_tab_ac_ae,
		 scripts: true,
		 text: "Cargando.."
		});
	}
});
//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    title: 'META FINANCIERA',
    border:false,
    store: this.store_lista,
    loadMask:true,
    height:300,
    tbar:[
      this.nuevo,'-',this.eliminar
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'PRESUPUESTO', width:160,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_presupuesto'},
    {header: 'PARTIDA', width:100,  menuDisabled:true, sortable: true, dataIndex: 'co_partida'},
    {header: 'FUENTE DE FINANCIAMIENTO', width:300,  menuDisabled:true, sortable: true, dataIndex: 'tx_fuente_financiamiento'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
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

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!forma004ActividadEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        if (forma004ActividadEditar.main.store_lista.getCount() == 0) {
            Ext.Msg.alert("Alerta", "Debe agregar las metas financieras");
            return false;
        }        
//*****Array del Grid********//
	listado = paqueteComunJS.funcion.getJsonByObjStore({
		store:forma004ActividadEditar.main.gridPanel_.getStore()
	});
	forma004ActividadEditar.main.JsonDetalle.setValue(listado);
        
        forma004ActividadEditar.main.formPanel_.getForm().submit({
		method:'POST',
		url:'{{ URL::to('ac/seguimiento/002/actividad/guardar') }}',
		waitMsg: 'Enviando datos, por favor espere..',
		waitTitle:'Enviando',
            failure: function(form, action) {
		var errores = '';
		for(datos in action.result.msg){
			errores += action.result.msg[datos] + '<br>';
		}
                Ext.MessageBox.alert('Error en transacción', action.result.msg);
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
                 forma003ActividadLista.main.store_lista.load();
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
		this.nb_responsable
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

this.panelDatos2 = new Ext.Panel({
    title: 'METAS FINANCIERAS',
    bodyStyle:'padding:5px;',
    height:300,
    autoScroll:true,
    items:[
	this.gridPanel_
	]
});

this.panel = new Ext.TabPanel({
    activeTab:0,
    height:250,
    enableTabScroll:true,
    deferredRender: false,
		border:false,
    items:[
	this.panelDatos1,
        this.panelDatos2
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
                this.JsonDetalle,
		this.panel
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Nueva Actividad',
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
forma003ActividadLista.main.mascara.hide();

},
eliminarRequerimiento:function(){
                var s = forma004ActividadEditar.main.gridPanel_.getSelectionModel().getSelections();
                for(var i = 0, r; r = s[i]; i++){
                      forma004ActividadEditar.main.store_lista.remove(r);
                }

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
};
Ext.onReady(forma004ActividadEditar.main.init, forma004ActividadEditar.main);
</script>
<div id="formulariometafinanciera"></div>
