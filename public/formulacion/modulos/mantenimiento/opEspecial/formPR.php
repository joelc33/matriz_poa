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
Ext.ns("crearProyecto");
crearProyecto.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeID_ESTATUS_PROYECTO = this.getStoreID_ESTATUS_PROYECTO();
//<Stores de fk>
//<Stores de fk>
this.storeID_SITUACION_PRESUPUESTARIA = this.getStoreID_SITUACION_PRESUPUESTARIA();
//<Stores de fk>
//<Stores de fk>
this.storeID_SECTOR = this.getStoreID_SECTOR();
//<Stores de fk>
//<Stores de fk>
this.storeID_SUB_SECTOR = this.getStoreID_SUB_SECTOR();
//<Stores de fk>
//<Stores de fk>
this.storeID_PLAN = this.getStoreID_PLAN();
//<Stores de fk>
//<Stores de fk>
this.storeID_EJECUTOR = this.getStoreID_EJECUTOR();
//<Stores de fk>

//<ClavePrimaria>
this.co_proyectos = new Ext.form.Hidden({
	name:'co_proyectos',
	value:this.OBJ.co_proyectos
});
this.id_ejercicio = new Ext.form.Hidden({
	name:'ejercicio_proyecto',
	value:this.OBJ.id_ejercicio,
});
this.id_tab_ejecutor = new Ext.form.Hidden({
	name:'id_tab_ejecutor',
	value:this.OBJ.id_tab_ejecutor,
});

this.id_proyecto = new Ext.form.TextField({
	fieldLabel:'1.0. CÓDIGO DEL PROYECTO',
	name:'id_proyecto',
	value:this.OBJ.id_proyecto,
	width:120,
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

this.comCodigo = new Ext.form.CompositeField({
fieldLabel: '1.0. CÓDIGO DEL PROYECTO',
items: [
	this.id_proyecto,
             {
                   xtype: 'displayfield',
                   value: '&nbsp;&nbsp;&nbsp; 1.1. CÓDIGO DEL SISTEMA:',
                   width: 210
             },
	this.co_sistema
	]
});

this.id_ejecutor = new Ext.form.ComboBox({
	fieldLabel:'1.2. UNIDAD EJECUTORA RESPONSABLE',
	store: this.storeID_EJECUTOR,
	typeAhead: true,
	valueField: 'id_ejecutor',
	displayField:'tx_ejecutor',
	hiddenName:'ejecutor_proyecto',
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
	width:500,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_ejecutor}</div></div></tpl>'),
	//listWidth:'600',
	resizable:true,
	allowBlank:false,
        onSelect: function(record){
		crearProyecto.main.id_ejecutor.setValue(record.data.id_ejecutor);
		crearProyecto.main.id_tab_ejecutor.setValue(record.data.id);
		this.collapse();
        }
});
this.storeID_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_ejecutor,
	value:  this.OBJ.id_ejecutor,
	objStore: this.storeID_EJECUTOR
});

this.nb_proyecto = new Ext.form.TextField({
	fieldLabel:'1.3. NOMBRE DEL PROYECTO',
	name:'nombre_proyecto',
	value:this.OBJ.nb_proyecto,
	width:500,
	allowBlank:false,
	maxLength: 150,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 150},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
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
	fieldLabel:'1.4. FECHA DE INICIO',
	name:'fecha_ini_proyecto',
	value:(this.OBJ.fecha_inicio)?this.OBJ.fecha_inicio:'<?= date('d-m-Y',strtotime($fechaI)); ?>',
	allowBlank:false,
	width:100,
	minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',
	validationEvent: 'change',
	validator: validarFecha
});

this.fecha_fin = new Ext.form.DateField({
	fieldLabel:'1.5. FECHA DE CULMINACIÓN',
	name:'fecha_fin_proyecto',
	value:(this.OBJ.fecha_fin)?this.OBJ.fecha_fin:'<?= date('d-m-Y',strtotime($fechaF)); ?>',
	allowBlank:false,
	width:100,
	minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',
	validationEvent: 'change',
	validator: validarFecha
});

this.comFecha = new Ext.form.CompositeField({
fieldLabel: '1.4. FECHA DE INICIO',
items: [
	this.fecha_inicio,
             {
                   xtype: 'displayfield',
                   value: '&nbsp;&nbsp;&nbsp; 1.5. FECHA DE CULMINACIÓN:',
                   width: 210
             },
	this.fecha_fin
	]
});

this.id_tab_estatus_proyecto = new Ext.form.ComboBox({
	fieldLabel:'1.6. ESTATUS DEL PROYECTO',
	store: this.storeID_ESTATUS_PROYECTO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_estatus_proyecto',
	hiddenName:'status_proyecto',
	//readOnly:(this.OBJ.id_tab_estatus_proyecto!='')?true:false,
	//style:(this.OBJ.id_tab_estatus_proyecto!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Estatus',
	selectOnFocus: true,
	mode: 'local',
	width:200,
	resizable:true,
	allowBlank:false
});

this.storeID_ESTATUS_PROYECTO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_estatus_proyecto,
	value:  this.OBJ.id_tab_estatus_proyecto,
	objStore: this.storeID_ESTATUS_PROYECTO
});

this.tx_objetivo_general = new Ext.form.TextArea({
	fieldLabel:'1.7. OBJETIVO GENERAL DEL PROYECTO',
	name:'objetivo_proyecto',
	value:this.OBJ.tx_objetivo_general,
	allowBlank:false,
	width:500,
	height:100,
	maxLength: 200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.id_tab_situacion_presupuestaria = new Ext.form.ComboBox({
	fieldLabel:'1.8. SITUACIÓN PRESUPUESTARIA',
	store: this.storeID_SITUACION_PRESUPUESTARIA,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_situacion_presupuestaria',
	hiddenName:'sit_presupuesto_proyecto',
	//readOnly:(this.OBJ.id_tab_situacion_presupuestaria!='')?true:false,
	//style:(this.OBJ.id_tab_situacion_presupuestaria!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Situacion Presup...',
	selectOnFocus: true,
	mode: 'local',
	width:200,
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
	fieldLabel:'1.9. MONTO TOTAL PROYECTO BS.',
	name:'monto_proyecto',
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

this.tx_descripcion_proyecto = new Ext.form.TextArea({
	fieldLabel:'1.10. DESCRIPCIÓN DEL PROYECTO',
	name:'descripcion_proyecto',
	value:this.OBJ.tx_descripcion_proyecto,
	allowBlank:false,
	width:500,
	height:100,
	maxLength: 200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.id_tab_sectores = new Ext.form.ComboBox({
	fieldLabel:'1.11.1. SECTOR',
	store: this.storeID_SECTOR,
	typeAhead: true,
	valueField: 'co_sector',
	displayField:'nu_descripcion',
	hiddenName:'clase_sector_proyecto',
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
                crearProyecto.main.storeID_SUB_SECTOR.load({
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
		callback: function(){crearProyecto.main.id_tab_sub_sector.setValue(crearProyecto.main.OBJ.id_tab_sub_sector);}
	});
}

this.id_tab_sectores.on('beforeselect',function(cmb,record,index){
        	this.id_tab_sub_sector.clearValue();
},this);

this.id_tab_sub_sector = new Ext.form.ComboBox({
	fieldLabel:'1.11.2. SUB-SECTOR',
	store: this.storeID_SUB_SECTOR,
	typeAhead: true,
	valueField: 'co_sub_sector',
	displayField:'nu_descripcion',
	hiddenName:'clase_subsector_proyecto',
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

this.fieldset2 = new Ext.form.FieldSet({
	title:'1.11. CLASIFICACIÓN SECTORIAL',
	autoWidth:true,
        items:[
		this.id_tab_sectores,
		this.id_tab_sub_sector
		]
});

this.id_tab_plan_operativo = new Ext.form.ComboBox({
	fieldLabel:'1.12. ¿A CUÁL PLAN OPERATIVO CONSIDERA QUE PERTENECE EL PROYECTO?',
	store: this.storeID_PLAN,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_plan_operativo',
	hiddenName:'plan_operativo_proyecto',
	//readOnly:(this.OBJ.id_tab_plan_operativo!='')?true:false,
	//style:(this.OBJ.id_tab_plan_operativo!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Plan',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	resizable:true,
	allowBlank:false
});

this.storeID_PLAN.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_plan_operativo,
	value:  this.OBJ.id_tab_plan_operativo,
	objStore: this.storeID_PLAN
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.id_ejercicio,
		this.id_tab_ejecutor,
		this.comCodigo,
		this.id_ejecutor,
		this.nb_proyecto,
		this.comFecha,
		this.id_tab_estatus_proyecto,
		this.tx_objetivo_general,
		this.id_tab_situacion_presupuestaria,
		this.mo_total,
		this.tx_descripcion_proyecto,
		this.fieldset2,
		this.id_tab_plan_operativo
		]
});

this.panelDatos1 = new Ext.Panel({
    title: '1. DATOS BÁSICOS DEL PROYECTO',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.fieldset1
	]
});

this.panel = new Ext.TabPanel({
    activeTab:0,
    autoHeight:true,
    enableTabScroll:true,
    deferredRender: false,
    items:[
	this.panelDatos1
	]
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){
        if(!crearProyecto.main.formulario.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        crearProyecto.main.formulario.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/mantenimiento/opEspecial/orm.php/guardar/proyecto',
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
                 opcionPlanificador.main.store_lista.load();
		this.panelCambio = Ext.getCmp('tabpanel');
		this.panelCambio.remove('53');
             }
        });

   
    }
});

this.formulario = new Ext.form.FormPanel({
    autoWidth:true,
    border:false,
    labelWidth: 210,
    padding:'5px',
    deferredRender: false,
    items: [
	this.panel
    ],
    buttonAlign:'left',
    buttons:[
<?php if( in_array( array( 'de_privilegio' => 'oe.proyecto.agregar.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar
<?php } ?>
    ]
});

this.formulario.render("contenedorcrearProyecto");
},
getStoreID_ESTATUS_PROYECTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/mantenimiento/opEspecial/orm.php/estatusproyecto',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_estatus_proyecto'}
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
getStoreID_PLAN:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/mantenimiento/opEspecial/orm.php/planoperativo',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_plan_operativo'}
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
}
};
Ext.onReady(crearProyecto.main.init, crearProyecto.main);
</script>
<div id="contenedorcrearProyecto"></div>
