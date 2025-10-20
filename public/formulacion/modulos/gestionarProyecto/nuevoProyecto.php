<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}  
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();
//anio de ejercicio fiscal activo
$sql2 = "SELECT co_ejercicio_fiscal FROM t25_ejercicio_fiscal WHERE edo_reg is true;";
$resultado2 = $comunes->ObtenerFilasBySqlSelect($sql2);
$fechaI = '01-01-'.$resultado2[0]['co_ejercicio_fiscal'];
$fechaF = '31-12-'.$resultado2[0]['co_ejercicio_fiscal'];
$id_ejercicio = $resultado2[0]['co_ejercicio_fiscal'];
$id_ejecutor = $_SESSION['id_ejecutor'];

$data1 = json_encode(array(
	"co_proyectos"     => "",
	"id_proyecto"     => "",
	"id_ejercicio"     => $id_ejercicio,
	"id_ejecutor"     => $id_ejecutor,
	"tipo_registro"     => "",
	"nb_proyecto"     => "",
	"co_estatus_proyecto"     => "",
	"codigo_new_etapa"     => "",
	"fecha_inicio"     => "",
	"fecha_fin"     => "",
	"tx_objetivo_general"     => "",
	"tx_descripcion_proyecto"     => "",
	"co_situacion_presupuestaria"     => "",
	"mo_total"     => "",
	"co_sector"     => "",
	"co_sub_sector"     => "",
	"co_plan"     => "",
));
?>
<script type="text/javascript">
Ext.ns("nuevoProyecto");
nuevoProyecto.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data1 ?>'});

//<Stores de fk>
this.storeCO_ESTATUS_PROYECTO = this.getStoreCO_ESTATUS_PROYECTO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_SITUACION_PRESUPUESTARIA = this.getStoreCO_SITUACION_PRESUPUESTARIA();
//<Stores de fk>
//<Stores de fk>
this.storeCO_SECTOR = this.getStoreCO_SECTOR();
//<Stores de fk>
//<Stores de fk>
this.storeCO_SUB_SECTOR = this.getStoreCO_SUB_SECTOR();
//<Stores de fk>
//<Stores de fk>
this.storeCO_PLAN = this.getStoreCO_PLAN();
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
	name:'id_ejercicio',
	value:this.OBJ.id_ejercicio,
});
/*this.id_ejecutor = new Ext.form.Hidden({
	name:'id_ejecutor',
	value:this.OBJ.id_ejecutor
});*/
//</ClavePrimaria>

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
	hiddenName:'id_ejecutor',
	readOnly:(this.OBJ.id_ejecutor!='')?true:false,
	style:(this.OBJ.id_ejecutor!='')?'background:#c9c9c9;':'',
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
	allowBlank:false
});
this.storeID_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_ejecutor,
	value:  this.OBJ.id_ejecutor,
	objStore: this.storeID_EJECUTOR
});

this.nb_proyecto = new Ext.form.TextField({
	fieldLabel:'1.3. NOMBRE DEL PROYECTO',
	name:'nb_proyecto',
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
	name:'fecha_inicio',
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
	name:'fecha_fin',
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

this.co_estatus_proyecto = new Ext.form.ComboBox({
	fieldLabel:'1.6. ESTATUS DEL PROYECTO',
	store: this.storeCO_ESTATUS_PROYECTO,
	typeAhead: true,
	valueField: 'co_estatus_proyecto',
	displayField:'tx_estatus_proyecto',
	hiddenName:'co_estatus_proyecto',
	//readOnly:(this.OBJ.co_estatus_proyecto!='')?true:false,
	//style:(this.OBJ.co_estatus_proyecto!='')?'background:#c9c9c9;':'',
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

this.storeCO_ESTATUS_PROYECTO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_estatus_proyecto,
	value:  this.OBJ.co_estatus_proyecto,
	objStore: this.storeCO_ESTATUS_PROYECTO
});

this.tx_objetivo_general = new Ext.form.TextArea({
	fieldLabel:'1.7. OBJETIVO GENERAL DEL PROYECTO',
	name:'tx_objetivo_general',
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

this.co_situacion_presupuestaria = new Ext.form.ComboBox({
	fieldLabel:'1.8. SITUACIÓN PRESUPUESTARIA',
	store: this.storeCO_SITUACION_PRESUPUESTARIA,
	typeAhead: true,
	valueField: 'co_situacion_presupuestaria',
	displayField:'tx_situacion_presupuestaria',
	hiddenName:'co_situacion_presupuestaria',
	//readOnly:(this.OBJ.co_situacion_presupuestaria!='')?true:false,
	//style:(this.OBJ.co_situacion_presupuestaria!='')?'background:#c9c9c9;':'',
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

this.storeCO_SITUACION_PRESUPUESTARIA.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_situacion_presupuestaria,
	value:  this.OBJ.co_situacion_presupuestaria,
	objStore: this.storeCO_SITUACION_PRESUPUESTARIA
});

this.mo_total = new Ext.form.NumberField({
	fieldLabel:'1.9. MONTO TOTAL PROYECTO BS.',
	name:'mo_total',
	value:this.OBJ.mo_total,
	allowBlank:false,
	width:200,
	minLength : 1,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	blankText: '0.00',
	decimalPrecision: 2,
	allowNegative: false,
   	//style: 'text-align: right',
	emptyText: '0.00',
});

this.tx_descripcion_proyecto = new Ext.form.TextArea({
	fieldLabel:'1.10. DESCRIPCIÓN DEL PROYECTO',
	name:'tx_descripcion_proyecto',
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

this.co_sector = new Ext.form.ComboBox({
	fieldLabel:'1.11.1. SECTOR',
	store: this.storeCO_SECTOR,
	typeAhead: true,
	valueField: 'co_sector',
	displayField:'tx_descripcion',
	hiddenName:'co_sector',
	//readOnly:(this.OBJ.co_sector!='')?true:false,
	//style:(this.OBJ.co_sector!='')?'background:#c9c9c9;':'',
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
                nuevoProyecto.main.storeCO_SUB_SECTOR.load({
                    params: {co_sector:this.getValue()}
                })
            }
        }
});

this.storeCO_SECTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_sector,
	value:  this.OBJ.co_sector,
	objStore: this.storeCO_SECTOR
});

if(this.OBJ.co_sector){
	this.storeCO_SUB_SECTOR.load({
		params: {co_sector:this.OBJ.co_sector},
		callback: function(){nuevoProyecto.main.co_sub_sector.setValue(nuevoProyecto.main.OBJ.co_sub_sector);}
	});
}

this.co_sector.on('beforeselect',function(cmb,record,index){
        	this.co_sub_sector.clearValue();
},this);

this.co_sub_sector = new Ext.form.ComboBox({
	fieldLabel:'1.11.2. SUB-SECTOR',
	store: this.storeCO_SUB_SECTOR,
	typeAhead: true,
	valueField: 'co_sub_sector',
	displayField:'tx_sub_sector',
	hiddenName:'co_sub_sector',
	//readOnly:(this.OBJ.co_sub_sector!='')?true:false,
	//style:(this.OBJ.co_sub_sector!='')?'background:#c9c9c9;':'',
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
		this.co_sector,
		this.co_sub_sector
		]
});

this.co_plan = new Ext.form.ComboBox({
	fieldLabel:'1.12. ¿A CUÁL PLAN OPERATIVO CONSIDERA QUE PERTENECE EL PROYECTO?',
	store: this.storeCO_PLAN,
	typeAhead: true,
	valueField: 'co_plan',
	displayField:'tx_plan',
	hiddenName:'co_plan',
	//readOnly:(this.OBJ.co_plan!='')?true:false,
	//style:(this.OBJ.co_plan!='')?'background:#c9c9c9;':'',
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

this.storeCO_PLAN.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_plan,
	value:  this.OBJ.co_plan,
	objStore: this.storeCO_PLAN
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.id_ejercicio,
		this.comCodigo,
		this.id_ejecutor,
		this.nb_proyecto,
		this.comFecha,
		this.co_estatus_proyecto,
		this.tx_objetivo_general,
		this.co_situacion_presupuestaria,
		this.mo_total,
		this.tx_descripcion_proyecto,
		this.fieldset2,
		this.co_plan
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
	//nuevoProyecto.main.panel.remove(tabuladorDos.main.panelDatos);
        if(!nuevoProyecto.main.formulario.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        nuevoProyecto.main.formulario.getForm().submit({
            method:'POST',
	    /*headers: {'Content-Type': 'application/json;charset=utf-8'},
            params: 'data='+Ext.util.JSON.encode(nuevoExpendedor.main.formulario.getForm().getValues()),*/
            url:'formulacion/modulos/proyecto/funcion.php?op=99',
            waitMsg: 'Enviando datos, por favor espere..',
            waitTitle:'Enviando',
            failure: function(form, action) {
                Ext.MessageBox.alert('Error en transacción', action.result.msg);
            },
            success: function(form, action) {
                 if(action.result.success){
		//window.open("modulos/reportes/rex.php?c="+action.result.c);
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
		var direccionar = Ext.get('contenedornuevoProyecto');
		direccionar.load({ url: 'formulacion/modulos/gestionarProyecto/nuevoProyecto.php', scripts: true, text: 'Cargando...',              params:'codigo='+action.result.c});
             }
        });

   
    }
});

this.salir = new Ext.Button({
	text:'Atras',
	iconCls:'icon-atras',
	handler:function(){
		var direccionar = Ext.get('contenedoropcionEjecutor');
		direccionar.load({ url: 'formulacion/modulos/actividades/opTranscriptor.php', scripts: true, text: 'Cargando...'});
	}
});

this.formulario = new Ext.form.FormPanel({
    autoWidth:true,
    border:false,
    labelWidth: 210,
    padding:'10px',
    deferredRender: false,
    items: [
	this.panel
    ],
    buttonAlign:'left',
    buttons:[
        this.guardar,
	this.salir
    ]
});

this.formulario.render("contenedornuevoProyecto");
},
getStoreCO_ESTATUS_PROYECTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=1',
        root:'data',
        fields:[
            {name: 'co_estatus_proyecto'},{name: 'tx_estatus_proyecto'}
            ]
    });
    return this.store;
},
getStoreCO_SITUACION_PRESUPUESTARIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=2',
        root:'data',
        fields:[
            {name: 'co_situacion_presupuestaria'},{name: 'tx_situacion_presupuestaria'}
            ]
    });
    return this.store;
},
getStoreCO_SECTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=3',
        root:'data',
        fields:[
            {name: 'co_sector'},{name: 'tx_descripcion'}
            ]
    });
    return this.store;
},
getStoreCO_SUB_SECTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=4',
        root:'data',
        fields:[
            {name: 'co_sub_sector'},{name: 'tx_sub_sector'}
            ]
    });
    return this.store;
},
getStoreCO_PLAN:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=5',
        root:'data',
        fields:[
            {name: 'co_plan'},{name: 'tx_plan'}
            ]
    });
    return this.store;
},
getStoreID_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/usuario/funcion.php?op=5',
        root:'data',
        fields:[
            {name: 'id_ejecutor'},{name: 'tx_ejecutor'}
            ]
    });
    return this.store;
}
};
Ext.onReady(nuevoProyecto.main.init, nuevoProyecto.main);
</script>
<div id="contenedornuevoProyecto"></div>
