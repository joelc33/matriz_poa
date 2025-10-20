<script type="text/javascript">
Ext.ns('parametroUbicacionPR');
parametroUbicacionPR.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<Stores de fk>
this.storeCO_MUNICIPIO = this.getStoreCO_MUNICIPIO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_PARROQUIA = this.getStoreCO_PARROQUIA();
//<Stores de fk>
//<Stores de fk>
this.storeCO_EJECUTOR = this.getStoreID_EJECUTOR();
//<Stores de fk>
//<Stores de fk>
this.storeCO_FUENTE_FINANCIAMIENTO = this.getStoreCO_FUENTE_FINANCIAMIENTO();
//<Stores de fk>

<?php $rol_planificador = array( 3, 8); ?>

this.co_municipio = new Ext.form.ComboBox({
	fieldLabel:'MUNICIPIO',
	store: this.storeCO_MUNICIPIO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_municipio',
	hiddenName:'id_tab_municipio',
	//readOnly:(this.OBJ.co_municipio!='')?true:false,
	//style:(this.OBJ.co_municipio!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Municipio',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	//allowBlank:false,
	listeners:{
            change: function(){
                parametroUbicacionPR.main.storeCO_PARROQUIA.load({
                    params: {id_tab_municipio:this.getValue(), _token: '{{ csrf_token() }}'}
                })
            }
        }
});

this.storeCO_MUNICIPIO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_municipio,
	value:  this.OBJ.co_municipio,
	objStore: this.storeCO_MUNICIPIO
});

this.co_municipio.on('beforeselect',function(cmb,record,index){
        	this.co_parroquia.clearValue();
},this);

this.co_parroquia = new Ext.form.ComboBox({
	fieldLabel:'PARROQUIA',
	store: this.storeCO_PARROQUIA,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_parroquia',
	hiddenName:'id_tab_parroquia',
	//readOnly:(this.OBJ.co_parroquia!='')?true:false,
	//style:(this.OBJ.co_parroquia!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Parroquia',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false
});

this.id_tab_ejecutores = new Ext.form.ComboBox({
	fieldLabel:'UNIDAD EJECUTORA',
	store: this.storeCO_EJECUTOR,
	typeAhead: true,
	valueField: 'id_ejecutor',
	displayField:'de_ejecutor',
	hiddenName:'ejecutor',
	//readOnly:(this.OBJ.id_tab_ejecutores!='')?true:false,
	//style:(this.main.OBJ.id_tab_ejecutores!='')?'background:#c9c9c9;':'',
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
	//allowBlank:false,
	listeners:{
			keyup: function() {
				this.store.filter('tx_ejecutor', this.getRawValue(), true, false);
			},
			beforequery: function(queryEvent) {
				queryEvent.combo.onLoad();
				return false;
			}
	}
});
this.storeCO_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_ejecutores,
	value:  this.OBJ.id_ejecutor,
	objStore: this.storeCO_EJECUTOR
});

this.id_tab_fuente_financiamiento = new Ext.form.ComboBox({
	fieldLabel:'FUENTE DE FINANCIAMIENTO',
	store: this.storeCO_FUENTE_FINANCIAMIENTO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_fuente_financiamiento',
	hiddenName:'fuente_financiamiento',
	//readOnly:(this.OBJ.id_tab_fuente_financiamiento!='')?true:false,
	//style:(this.OBJ.id_tab_fuente_financiamiento!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Fuente de Fianciamiento',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_fuente_financiamiento}</div></div></tpl>'),
	resizable:true,
	//allowBlank:false
});

this.storeCO_FUENTE_FINANCIAMIENTO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_fuente_financiamiento,
	value:  this.OBJ.id_tab_fuente_financiamiento,
	objStore: this.storeCO_FUENTE_FINANCIAMIENTO
});

this.fieldset1 = new Ext.form.FieldSet({
    title: 'Seleccione Parametros',
    items:[
			this.co_municipio,
			this.id_tab_ejecutores,
			this.id_tab_fuente_financiamiento
		]
});

this.GrupoBotones = Ext.extend(Ext.Panel, {
		autoWidth:true,
		autoHeight:true,
		style: 'margin-top:5px',
		bodyStyle: 'padding:5px',
		autoScroll: true
});

this.botones = new this.GrupoBotones({
				//title: 'Opciones',
				items:[
					this.fieldset1
				],
				bbar: [{
						xtype: 'buttongroup',
						title: 'Formatos',
						columns: 6,
						defaults: {
								scale: 'medium',
								iconAlign:'top'
						},
						items: [
							@if( in_array( array( 'de_privilegio' => 'ubicacion.proyecto.municipio', 'in_habilitado' => true), Session::get('credencial') ))
								{
									text:'REPORTE por Municipio',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir
								},
							@endif
							@if( in_array( array( 'de_privilegio' => 'ubicacion.proyecto.todos', 'in_habilitado' => true), Session::get('credencial') ))
								{
									text:'REPORTE Todos',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir1
								},
							@endif
							@if( in_array( array( 'de_privilegio' => 'ubicacion.proyecto.exportar.municipio', 'in_habilitado' => true), Session::get('credencial') ))
								{
									text:'Exportar por Municipio',  // Generar la impresión en pdf
									iconCls:'icon-excel',
									handler: this.onExportar1
								},
							@endif
							@if( in_array( array( 'de_privilegio' => 'ubicacion.proyecto.exportar.todo', 'in_habilitado' => true), Session::get('credencial') ))
								{
									text:'Exportar Todos',  // Generar la impresión en pdf
									iconCls:'icon-excel',
									handler: this.onExportar2
								},
							@endif
							{
								text:'Limpiar',  // Limpiar campos del formulario
								iconCls:'icon-limpiar',
								handler: this.onLimpiar
							}
						]
				}]
});

this.formpanel = new Ext.form.FormPanel({
	bodyStyle: 'padding:5px',
	autoWidth:true,
	autoHeight:true,
	border:false,
	id: 'forma',
	labelWidth: 160,
	iconCls:'icon-reporteest',
	title: 'UBICACION - PROYECTOS',
	items:[
		this.botones
	]
});

this.formpanel.render('parametroUbicacionPR');
},
onImprimir : function() {
if(!parametroUbicacionPR.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}
   //window.open('formulacion/modulos/reportes/proyResumen.php?'+parametroUbicacionPR.main.formpanel.getForm().getValues(true));
	bajar.load({
		url: 'formulacion/modulos/reportes/ormPDF.php/reporte/ubicacion?'+parametroUbicacionPR.main.formpanel.getForm().getValues(true)
	});
},
onImprimir1 : function() {
   //window.open('formulacion/modulos/reportes/proyResumen.php');
	bajar.load({
		url: 'formulacion/modulos/reportes/ormPDF.php/reporte/ubicacion/todo'
	});
},
onExportar1 : function() {
if(!parametroUbicacionPR.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}
	bajar.load({
		url: 'formulacion/modulos/reportes/orm.php/exportar/ubicacion?'+parametroUbicacionPR.main.formpanel.getForm().getValues(true)
	});
},
onExportar2 : function() {
   //window.open('formulacion/modulos/reportes/proyResumen.php');
	bajar.load({
		url: 'formulacion/modulos/reportes/orm.php/exportar/ubicacion/todo'
	});
},
onLimpiar: function(){
    parametroUbicacionPR.main.formpanel.getForm().reset();
},
getStoreCO_MUNICIPIO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/municipio/todo') }}',
        root:'data',
        fields:[
						{name: 'id'},{name: 'de_municipio'}
            ],
            listeners : {
                exception : function(proxy, response, operation) {
                    Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                }
            }
    });
    return this.store;
},
getStoreCO_PARROQUIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/parroquia/todo') }}',
        root:'data',
        fields:[
						{name: 'id'},{name: 'de_parroquia'}
            ],
            listeners : {
                exception : function(proxy, response, operation) {
                    Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                }
            }
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
},
getStoreCO_FUENTE_FINANCIAMIENTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/fuentefinanciamiento') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_fuente_financiamiento'}
            ]
    });
    return this.store;
}
};
Ext.onReady(parametroUbicacionPR.main.init, parametroUbicacionPR.main);
</script>
<div id="parametroUbicacionPR"></div>
