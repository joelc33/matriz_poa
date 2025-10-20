<script type="text/javascript">
Ext.ns('parametroRESUMEN');
parametroRESUMEN.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<Stores de fk>
this.storeCO_EJECUTOR = this.getStoreID_EJECUTOR();
//<Stores de fk>

<?php $rol_planificador = array( 3, 8); ?>

this.id_tab_ejecutores = new Ext.form.ComboBox({
	fieldLabel:'UNIDAD EJECUTORA',
	store: this.storeCO_EJECUTOR,
	typeAhead: true,
	valueField: 'id_ejecutor',
	displayField:'de_ejecutor',
	hiddenName:'id_ejecutor',
	//readOnly:(this.OBJ.id_tab_ejecutores!='')?true:false,
	//style:(this.main.OBJ.id_tab_ejecutores!='')?'background:#c9c9c9;':'',
	@if (in_array(Session::get('rol'), $rol_planificador))
		readOnly:true,
		style:'background:#c9c9c9;',
	@endif
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
	allowBlank:false,
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

this.fieldset1 = new Ext.form.FieldSet({
    title: 'Seleccione Parametros',
    items:[
			this.id_tab_ejecutores
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
							@if( in_array( array( 'de_privilegio' => 'ff.ejecutor', 'in_habilitado' => true), Session::get('credencial') ))
								{
									text:'REPORTE por Ejecutor',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir
								},
							@endif
							@if( in_array( array( 'de_privilegio' => 'ff.todos', 'in_habilitado' => true), Session::get('credencial') ))
								{
									text:'REPORTE Todos',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir1
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
	title: 'RESUMEN - PROYECTOS',
	items:[
		this.botones
	]
});

this.formpanel.render('parametroRESUMEN');
},
onImprimir : function() {
if(!parametroRESUMEN.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}
   //window.open('formulacion/modulos/reportes/proyResumen.php?'+parametroRESUMEN.main.formpanel.getForm().getValues(true));
	bajar.load({
		url: 'formulacion/modulos/reportes/proyResumen.php?'+parametroRESUMEN.main.formpanel.getForm().getValues(true)
	});
},
onImprimir1 : function() {
   //window.open('formulacion/modulos/reportes/proyResumen.php');
	bajar.load({
		url: 'formulacion/modulos/reportes/proyResumen.php'
	});
},
onLimpiar: function(){
    parametroRESUMEN.main.formpanel.getForm().reset();
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
}
};
Ext.onReady(parametroRESUMEN.main.init, parametroRESUMEN.main);
</script>
<div id="parametroRESUMEN"></div>
