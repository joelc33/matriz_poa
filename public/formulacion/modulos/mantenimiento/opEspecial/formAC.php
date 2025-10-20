<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../../model/tab_ejercicio_fiscal.php');

$ejercicio_fiscal = tab_ejercicio_fiscal::select('id')
->where('id', '=', $_SESSION['ejercicio_fiscal'])
->first();

$fechaI = '01-01-'.$ejercicio_fiscal->id;
$fechaF = '31-12-'.$ejercicio_fiscal->id;
$id_ejecutor = $_SESSION['id_ejecutor'];
$id_tab_ejecutor = $_SESSION['co_ejecutores'];

$data = json_encode(array(
	"id_ejercicio"     => $ejercicio_fiscal->id,
	"id_ejecutor"     => $id_ejecutor,
	"id_tab_ejecutor"     => $id_tab_ejecutor,
));
?>
<script type="text/javascript">
Ext.ns('crearAC');
crearAC.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeID_TIPO_ACCION = this.getStoreID_TIPO_ACCION();
//<Stores de fk>
//<Stores de fk>
this.storeID_EJECUTOR = this.getStoreID_EJECUTOR();
//<Stores de fk>
//<Stores de fk>
this.storeID_SECTOR = this.getStoreID_SECTOR();
//<Stores de fk>
//<Stores de fk>
this.storeID_SUB_SECTOR = this.getStoreID_SUB_SECTOR();
//<Stores de fk>
//<Stores de fk>
this.storeID_SITUACION_PRESUPUESTARIA = this.getStoreID_SITUACION_PRESUPUESTARIA();
//<Stores de fk>

this.id_ejercicio = new Ext.form.Hidden({
	name:'ejercicio_ac',
	value:this.OBJ.id_ejercicio,
});
this.id_tab_ejecutor = new Ext.form.Hidden({
	name:'id_tab_ejecutor',
	value:this.OBJ.id_tab_ejecutor,
});

this.id_ac = new Ext.form.TextField({
	fieldLabel:'1.0. CÓDIGO DE LA ACCIÓN CENTRALIZADA',
	name:'id_proyecto',
	value:this.OBJ.id_ac,
	width:160,
	readOnly:true,
	style:'background:#c9c9c9;',
	//allowBlank:false
});

this.co_sistema = new Ext.form.TextField({
	fieldLabel:'1.1. CÓDIGO DEL SISTEMA',
	name:'co_sistema',
	value:this.OBJ.co_sistema,
	width:160,
	readOnly:true,
	style:'background:#c9c9c9;',
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	//allowBlank:false
});

this.accion_id = new Ext.form.ComboBox({
	fieldLabel:'1.2. TIPO DE ACCIÓN',
	store: this.storeID_TIPO_ACCION,
	typeAhead: true,
	valueField: 'id',
	displayField:'nombre',
	hiddenName:'accion_ac',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText: 'Seleccione el tipo de Acción Centralizada',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false,
	onSelect: function(record){
		crearAC.main.accion_id.setValue(record.data.id);
		crearAC.main.de_accion.setValue(record.data.de_accion);
		this.collapse();
	}
});

this.storeID_TIPO_ACCION.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.accion_id,
	value:  this.OBJ.accion_id,
	objStore: this.storeID_TIPO_ACCION
});

this.de_accion = new Ext.form.TextArea({
	fieldLabel: '1.3. DESCRIPCIÓN',
	name: 'descripcion_ac',
	allowBlank: false,
	height: 100,
	width:400,
	maxLength: 600
});

this.id_ejecutor = new Ext.form.ComboBox({
	fieldLabel:'1.4. UNIDAD EJECUTORA RESPONSABLE',
	store: this.storeID_EJECUTOR,
	typeAhead: true,
	valueField: 'id_ejecutor',
	displayField:'tx_ejecutor',
	hiddenName:'ejecutor_ac',
	<?php 
	if($_SESSION['co_rol']==3){
	echo "readOnly:true,style:'background:#c9c9c9;',";		 
	}else{}?>
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Unidad Ejecutora',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_ejecutor}</div></div></tpl>'),
	//listWidth:'600',
	resizable:true,
	allowBlank:false,
        onSelect: function(record){
		crearAC.main.id_ejecutor.setValue(record.data.id_ejecutor);
		crearAC.main.id_tab_ejecutor.setValue(record.data.id);
		this.collapse();
        }
});
this.storeID_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_ejecutor,
	value:  this.OBJ.id_ejecutor,
	objStore: this.storeID_EJECUTOR
});

this.mision_ac = new Ext.form.TextArea({
	fieldLabel: '1.4.1. MISION',
	name: 'mision_ac',
	allowBlank: false,
	height: 60,
	width:400,
	maxLength: 600
});

this.vision_ac = new Ext.form.TextArea({
	fieldLabel: '1.4.2. VISION',
	name: 'vision_ac',
	allowBlank: false,
	height: 60,
	width:400,
	maxLength: 600
});

this.objetivo_ac = new Ext.form.TextArea({
	fieldLabel: '1.4.3. OBJETIVOS DE LA INSTITUCION',
	name: 'objetivo_ac',
	allowBlank: false,
	height: 100,
	width:400,
	maxLength: 600
});

this.id_tab_sectores = new Ext.form.ComboBox({
	fieldLabel:'1.5.1. SECTOR',
	store: this.storeID_SECTOR,
	typeAhead: true,
	valueField: 'co_sector',
	displayField:'nu_descripcion',
	hiddenName:'sector_ac',
	//readOnly:(this.OBJ.id_tab_sectores!='')?true:false,
	//style:(this.OBJ.id_tab_sectores!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Sector',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false,
	listeners:{
            change: function(){
                crearAC.main.storeID_SUB_SECTOR.load({
                    params: {co_sector:this.getValue()}
                })
            }
        }
});

this.storeID_SECTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_sectores,
	value:  this.OBJ.id_tab_sectores,
	objStore: this.storeID_SECTOR
});

if(this.OBJ.id_tab_sectores){
	this.storeID_SUB_SECTOR.load({
		params: {co_sector:this.OBJ.id_tab_sectores},
		callback: function(){crearAC.main.id_tab_sub_sector.setValue(crearAC.main.OBJ.id_tab_sub_sector);}
	});
}

this.id_tab_sectores.on('beforeselect',function(cmb,record,index){
        	this.id_tab_sub_sector.clearValue();
},this);

this.id_tab_sub_sector = new Ext.form.ComboBox({
	fieldLabel:'1.5.2. SUB-SECTOR',
	store: this.storeID_SUB_SECTOR,
	typeAhead: true,
	valueField: 'id',
	displayField:'nu_descripcion',
	hiddenName:'subsector_ac',
	//readOnly:(this.OBJ.id_tab_sub_sector!='')?true:false,
	//style:(this.OBJ.id_tab_sub_sector!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Sub Sector',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false
});

var self = this;

var validarFecha = function(value){
	var fi = self.fecha_inicio.getValue();
	var ff = self.fecha_fin.getValue();
	if( ff >= fi ) {
		return true;
	}
	Ext.Msg.alert("Notificación",'La Fechas deben estar en un Rango Logico');
	return false;
};

this.fecha_inicio = new Ext.form.DateField({
	fieldLabel:'1.6. FECHA DE INICIO',
	name:'fecha_ini_ac',
	value:(this.OBJ.fecha_inicio)?this.OBJ.fecha_inicio:'<?= date('d-m-Y',strtotime($fechaI)); ?>',
	allowBlank:false,
	width:100,
	minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',
	validationEvent: 'change',
	validator: validarFecha
});

this.fecha_fin = new Ext.form.DateField({
	fieldLabel:'1.7. FECHA DE CULMINACIÓN',
	name:'fecha_fin_ac',
	value:(this.OBJ.fecha_fin)?this.OBJ.fecha_fin:'<?= date('d-m-Y',strtotime($fechaF)); ?>',
	allowBlank:false,
	width:100,
	minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',
	validationEvent: 'change',
	validator: validarFecha
});

this.id_tab_situacion_presupuestaria = new Ext.form.ComboBox({
	fieldLabel:'1.8. SITUACIÓN PRESUPUESTARIA',
	store: this.storeID_SITUACION_PRESUPUESTARIA,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_situacion_presupuestaria',
	hiddenName:'sit_presupuesto_ac',
	//readOnly:(this.OBJ.id_tab_situacion_presupuestaria!='')?true:false,
	//style:(this.OBJ.id_tab_situacion_presupuestaria!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Situacion Presup...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false
});

this.storeID_SITUACION_PRESUPUESTARIA.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_situacion_presupuestaria,
	value:  this.OBJ.id_tab_situacion_presupuestaria,
	objStore: this.storeID_SITUACION_PRESUPUESTARIA
});

this.mo_total = new Ext.form.NumberField({
	fieldLabel:'1.9. MONTO TOTAL (BS.)',
	name:'monto_ac',
	value:this.OBJ.mo_total,
	allowBlank:false,
	width:200,
	minLength : 1,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	blankText: '0.00',
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
   	//style: 'text-align: right',
	emptyText: '0.00',
});

this.nu_poblacion = new Ext.form.NumberField({
	fieldLabel:'1.9.1. POBLACIÓN A BENEFICIAR',
	name:'poblacion_ac',
	value:this.OBJ.nu_poblacion,
	allowBlank:false,
	width:200,
	minLength : 1,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	blankText: '0.00',
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
   	//style: 'text-align: right',
	emptyText: '0',
});

this.nu_empleo = new Ext.form.NumberField({
	fieldLabel:'1.9.2. EMPLEOS PREVISTOS',
	name:'empleo_ac',
	value:this.OBJ.nu_empleo,
	allowBlank:false,
	width:200,
	minLength : 1,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	blankText: '0.00',
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
   	//style: 'text-align: right',
	emptyText: '0',
});

this.producto_ac = new Ext.form.TextArea({
	fieldLabel: '1.9.3. PRODUCTO PROGRAMADO DEL OBJETIVO',
	name: 'producto_ac',
	allowBlank: false,
	height: 60,
	width:400,
	maxLength: 600
});

this.resultado_ac = new Ext.form.TextArea({
	fieldLabel: '1.9.4. RESULTADOS PROGRAMADOS',
	name: 'resultado_ac',
	allowBlank: false,
	height: 60,
	width:400,
	maxLength: 600
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.id_ac,
		this.co_sistema,
		this.accion_id,
		this.de_accion,
		this.id_ejecutor,
		this.mision_ac,
		this.vision_ac,
		this.objetivo_ac
		]
});

this.fieldset2 = new Ext.form.FieldSet({
    	title: '1.5. CLASIFICACIÓN SECTORIAL',
	autoWidth:true,
        items:[
		this.id_tab_sectores,
		this.id_tab_sub_sector
		]
});

this.fieldset3 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.fecha_inicio,
		this.fecha_fin,
		this.id_tab_situacion_presupuestaria,
		this.mo_total,
		this.nu_poblacion,
		this.nu_empleo,
		this.producto_ac,
		this.resultado_ac
		]
});

this.panelDatos1 = new Ext.Panel({
    title: '1. DATOS BÁSICOS',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.id_ejercicio,
		this.id_tab_ejecutor,
		this.fieldset1,
		this.fieldset2,
		this.fieldset3
	]
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){
        if(!crearAC.main.formulario.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        crearAC.main.formulario.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/mantenimiento/opEspecial/orm.php/guardar/ac',
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
                 opcionPlanificador.main.store_acciones.load();
		this.panelCambio = Ext.getCmp('tabpanel');
		this.panelCambio.remove('54');
             }
        });

   
    }
});

this.panel = new Ext.TabPanel({
    activeTab:0,
    autoHeight:true,
    enableTabScroll:true,
    deferredRender: false,
    items:[
	this.panelDatos1
    ],
    bbar: [
<?php if( in_array( array( 'de_privilegio' => 'oe.ac.agregar.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
      '->', this.guardar
<?php } ?>
    ]
});

this.formulario = new Ext.form.FormPanel({
    autoWidth:true,
    border:false,
    labelWidth: 250,
    padding:'5px',
    deferredRender: false,
    items: [
	this.panel
    ]
});

this.formulario.render("contenedorcrearAC");
},
getStoreID_TIPO_ACCION:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/mantenimiento/opEspecial/orm.php/tipoaccion',
        root:'data',
        fields: [
            'id', {
                name: 'nombre',
                convert: function(v, r) {
                    return r.id + ' - ' + r.nombre;
                }
            },
	    'de_accion'
        ]
    });
    return this.store;
},
getStoreID_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/mantenimiento/opEspecial/orm.php/ejecutor',
        root:'data',
        fields:[
            {name: 'id'},{name: 'id_ejecutor'},{name: 'tx_ejecutor'}
            ]
    });
    return this.store;
},
getStoreID_SECTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/mantenimiento/opEspecial/orm.php/sector',
        root:'data',
        fields:[
            {name: 'id'},{name: 'co_sector'},{name: 'nu_descripcion'}
            ]
    });
    return this.store;
},
getStoreID_SUB_SECTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/mantenimiento/opEspecial/orm.php/subsector',
        root:'data',
        fields:[
            {name: 'id'},{name: 'co_sub_sector'},{name: 'nu_descripcion'}
            ]
    });
    return this.store;
},
getStoreID_SITUACION_PRESUPUESTARIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/mantenimiento/opEspecial/orm.php/situacionpresupuestaria',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_situacion_presupuestaria'}
            ]
    });
    return this.store;
}
};
Ext.onReady(crearAC.main.init, crearAC.main);
</script>
<div id="contenedorcrearAC"></div>
