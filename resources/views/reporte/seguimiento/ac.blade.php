<script type="text/javascript">
Ext.ns('parametroSeguimientoAC');
parametroSeguimientoAC.main = {
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
						columns: 7,
						defaults: {
								scale: 'medium',
								iconAlign:'top'
						},
						items: [
                                                    	@if (in_array(Session::get('rol'), $rol_planificador))
								{
									text:'REPORTE',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir
								},
                                                                {
									text:'REPORTE ACUMULADO',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimirAcumulada
								},
                                                        @else
                                                                {
									text:'REPORTE EJECUTOR',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir
								},
                                                                {
									text:'REPORTE EJECUTOR ACUMULADO',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimirAcumulada
								},
                                                                {
									text:'REPORTE FORMA 1,2,3 ORGANOS',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir1
								},
                                                                {
									text:'REPORTE FORMA 1,2,3 ENTES',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir2
								},   
                                                                {
									text:'REPORTE FORMA 4,5 ORGANOS',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir3
								},
                                                                {
									text:'REPORTE FORMA 4,5 ENTES',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir4
								},   
                                                                {
									text:'REPORTE FORMA 4 ORGANOS',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir5
								},
                                                                {
									text:'REPORTE FORMA 4 ENTES',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir6
								},  
                                                                {
									text:'REPORTE FORMA 5 ORGANOS',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir7
								},
                                                                {
									text:'REPORTE FORMA 5 ENTES',  // Generar la impresión en pdf
									iconCls:'icon-pdf',
									handler: this.onImprimir8
								},  
                                                                {
									text:'REPORTE CONSOLIDADO',  // Generar la impresión en pdf
									iconCls:'icon-excel',
									handler: this.onExportar1
								},          
                                                                {
									text:'REPORTE CONSOLIDADO X ACT.',  // Generar la impresión en pdf
									iconCls:'icon-excel',
									handler: this.onExportar2
								},                                                                
                                                                {
                                                                        text:'Limpiar',  // Limpiar campos del formulario
                                                                        iconCls:'icon-limpiar',
                                                                        handler: this.onLimpiar
                                                                }
							@endif
						]
				}]
});

this.formpanel = new Ext.form.FormPanel({
	bodyStyle: 'padding:5px',
	autoWidth:true,
	autoHeight:true,
	border:false,
	id: 'forma',
	labelWidth: 120,
	iconCls:'icon-reporteest',
	title: 'SEGUIMIENTO - EJECUTOR',
	items:[
		this.botones
	]
});

this.formpanel.render('parametroSeguimientoAC');
},
onImprimir : function() {
if(!parametroSeguimientoAC.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}

        this.codigo  = parametroSeguimientoAC.main.id_tab_ejecutores.getValue();
	bajar.load({
            url: '{{ URL::to('reporte/ac/seguimiento/ficha/consolidado') }}/{!! $lapso->id !!}/'+this.codigo
//		url: '{{ URL::to('reporte/poa/ac/ubicacion') }}?'+parametroSeguimientoAC.main.formpanel.getForm().getValues(true)
	});
},
onImprimirAcumulada : function() {
if(!parametroSeguimientoAC.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}

        this.codigo  = parametroSeguimientoAC.main.id_tab_ejecutores.getValue();
	bajar.load({
            url: '{{ URL::to('reporte/ac/seguimiento/ficha/consolidado/acumulada') }}/{!! $lapso->id !!}/'+this.codigo
            });
},
onImprimir1 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/ficha/primero') }}/{!! $lapso->id !!}/1'
	});
},
onImprimir2 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/ficha/primero') }}/{!! $lapso->id !!}/2'
	});
},
onImprimir3 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/ficha/segundo') }}/{!! $lapso->id !!}/1'
	});
},
onImprimir4 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/ficha/segundo') }}/{!! $lapso->id !!}/2'
	});
},
onImprimir5 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/ficha/cuarto') }}/{!! $lapso->id !!}/1'
	});
},
onImprimir6 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/ficha/cuarto') }}/{!! $lapso->id !!}/2'
	});
},
onImprimir7 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/ficha/quinto') }}/{!! $lapso->id !!}/1'
	});
},
onImprimir8 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/ficha/quinto') }}/{!! $lapso->id !!}/2'
	});
},
onExportar1 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/consolidado/exportar') }}/{!! $lapso->id !!}'
	});

},
onExportar2 : function() {
	bajar.load({
		url: '{{ URL::to('reporte/ac/seguimiento/consolidado/exportarA') }}/{!! $lapso->id !!}'
	});

},
onLimpiar: function(){
    parametroSeguimientoAC.main.formpanel.getForm().reset();
},
getStoreCO_PERIODO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/lapso') }}',
        root:'data',
        fields:[
          {name: 'id'},
          {name: 'id_tab_ejercicio_fiscal'},
          {name: 'id_tab_periodo'},
          {name: 'fe_inicio'},
          {name: 'fe_fin'},
          {name: 'de_lapso'},
          {
              name: 'rango',
              convert: function(v, r) {
                  return r.de_lapso + ' ( ' + r.fe_inicio + ' - ' + r.fe_fin + ' )';
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
Ext.onReady(parametroSeguimientoAC.main.init, parametroSeguimientoAC.main);
</script>
<div id="parametroSeguimientoAC"></div>
