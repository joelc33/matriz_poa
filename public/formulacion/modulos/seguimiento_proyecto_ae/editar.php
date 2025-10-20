<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

//anio de ejercicio fiscal activo
$sql2 = "SELECT id FROM mantenimiento.tab_ejercicio_fiscal WHERE in_activo is true;";
$resultado2 = $comunes->ObtenerFilasBySqlSelect($sql2);
$fechaI = '01-01-'.$resultado2[0]['id'];
$fechaF = '31-12-'.$resultado2[0]['id'];

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT * FROM proyecto_seguimiento.tab_proyecto_ae where id=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_proyecto_acc_espec"     => trim($row["id"]),
			"id_proyecto"     => trim($row["id_tab_proyecto"]),
			"nb_accion"     => trim($row["descripcion"]),
			"co_unidades_medida"     => trim($row["id_tab_unidad_medida"]),
			"nu_meta"     => trim($row["meta"]),
			"nu_ponderacion"     => trim($row["ponderacion"]),
			"op_bien_servicio"     => trim($row["bien_servicio"]),
			"mo_total_general"     => trim($row["total"]),
			"fecha_inicio"     => trim($row["fec_inicio"]),
			"fecha_culminacion"     => trim($row["fec_termino"]),
			"co_ejecutores"     => trim($row["co_ejecutores"]),
			"tx_objetivo_institucional"     => trim($row["tx_objetivo_institucional"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_proyecto_acc_espec"     => "",
		"id_proyecto"     => $_POST['id_proyecto'],
			"nb_accion"     => "",
			"co_unidades_medida"     => "",
			"nu_meta"     => "",
			"nu_ponderacion"     => "",
			"op_bien_servicio"     => "",
			"mo_total_general"     => "",
			"fecha_inicio"     => "",
			"fecha_culminacion"     => "",
			"co_ejecutores"     => "",
			"tx_objetivo_institucional"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("importarEditar");
importarEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeCO_UNIDADES_MEDIDA = this.getStoreCO_UNIDADES_MEDIDA();
//<Stores de fk>
//<Stores de fk>
this.storeID_EJECUTOR = this.getStoreID_EJECUTOR();
//<Stores de fk>

//<ClavePrimaria>
this.co_proyecto_acc_espec = new Ext.form.Hidden({
    name:'co_proyecto_acc_espec',
    value:this.OBJ.co_proyecto_acc_espec});
this.id_proyecto = new Ext.form.Hidden({
    name:'id_proyecto',
    value:this.OBJ.id_proyecto});
//</ClavePrimaria>
this.op = new Ext.form.Hidden({
	name:'op',
	value:99
});

this.nb_accion = new Ext.form.TextField({
	fieldLabel:'NOMBRE DE LA ACCION',
	name:'nb_accion',
	value:this.OBJ.nb_accion,
	width:400,
	maxLength: 600,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 600},
	allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.co_unidades_medida = new Ext.form.ComboBox({
	fieldLabel:'UNIDAD DE MEDIDA',
	store: this.storeCO_UNIDADES_MEDIDA,
	typeAhead: true,
	valueField: 'co_unidades_medida',
	displayField:'tx_unidades_medida',
	hiddenName:'co_unidades_medida',
	//readOnly:(this.OBJ.co_unidades_medida!='')?true:false,
	//style:(this.OBJ.co_unidades_medida!='')?'background:#c9c9c9;':'',
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
	objCMB: this.co_unidades_medida,
	value:  this.OBJ.co_unidades_medida,
	objStore: this.storeCO_UNIDADES_MEDIDA
});

this.fecha_inicio = new Ext.form.DateField({
	fieldLabel:'FECHA DE INICIO',
	name:'fecha_inicio',
	value:(this.OBJ.fecha_inicio)?this.OBJ.fecha_inicio:'<?= date('d-m-Y',strtotime($fechaI)); ?>',
	allowBlank:false,
	width:100,
	minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',
});

this.fecha_culminacion = new Ext.form.DateField({
	fieldLabel:'FECHA DE CULMINACIÓN',
	name:'fecha_culminacion',
	value:(this.OBJ.fecha_culminacion)?this.OBJ.fecha_culminacion:'<?= date('d-m-Y',strtotime($fechaF)); ?>',
	allowBlank:false,
	width:100,
	minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',
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
	this.fecha_culminacion
	]
});

this.mo_total_general = new Ext.form.NumberField({
	fieldLabel:'TOTAL GENERAL BS.',
	name:'mo_total_general',
	value:this.OBJ.mo_total_general,
	allowBlank:false,
	width:200,
	minLength : 1,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.nu_meta = new Ext.form.NumberField({
	fieldLabel:'META',
	name:'nu_meta',
	value:this.OBJ.nu_meta,
	allowBlank:false,
	width:200,
	maxLength: 10,
	emptyText: '0',
	decimalPrecision: 0,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 10},
});

this.nu_ponderacion = new Ext.form.NumberField({
	fieldLabel:'PONDERACIÓN (%)',
	name:'nu_ponderacion',
	value:this.OBJ.nu_ponderacion,
	allowBlank:false,
	width:200,
	maxLength: 2,
	emptyText: '0',
	decimalPrecision: 0,
 	minValue : 0,
 	maxValue : 99,
	msgTarget : 'Rango Entre 0 y 9',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 2},
});

this.op_bien_servicio = new Ext.form.TextField({
	fieldLabel:'BIEN O SERVICIO',
	name:'op_bien_servicio',
	value:this.OBJ.op_bien_servicio,
	width:400,
	maxLength: 120,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 120},
	allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.co_ejecutores = new Ext.form.ComboBox({
	fieldLabel:'UNIDAD EJECUTORA RESPONSABLE',
	store: this.storeID_EJECUTOR,
	typeAhead: true,
	valueField: 'co_ejecutores',
	displayField:'tx_ejecutor',
	hiddenName:'co_ejecutores',
	//readOnly:(this.OBJ.co_ejecutores!='')?true:false,
	//style:(this.main.OBJ.co_ejecutores!='')?'background:#c9c9c9;':'',
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
	allowBlank:false
});
this.storeID_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_ejecutores,
	value:  this.OBJ.co_ejecutores,
	objStore: this.storeID_EJECUTOR
});

this.tx_objetivo_institucional= new Ext.form.TextArea({
	fieldLabel:'OBJETIVO INSTITUCIONAL',
	name:'tx_objetivo_institucional',
	value:this.OBJ.tx_objetivo_institucional,
	allowBlank:false,
	width:400,
	height:100,
	maxLength: 600,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.nb_accion,
		this.co_unidades_medida,  
		this.nu_meta, 
		this.nu_ponderacion,
		this.op_bien_servicio,
		this.mo_total_general,  
		this.comFechaInCul,   
		this.co_ejecutores,  
		this.tx_objetivo_institucional,
		this.op
]});

this.guardar = new Ext.Button({
    text:'Procesar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!importarEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        importarEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/seguimiento_proyecto_ae/funcion.php',
            waitMsg: 'Enviando datos, por favor espere..',
            waitTitle:'Enviando',
            failure: function(form, action) {
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
                 tabuladorSiete.main.store_lista_accion.load();
		 tabuladorSiete.main.store_lista_fisica.load();
		 tabuladorOcho.main.store_lista_especifica.load();
		 tabuladorOcho.main.store_lista_partidas.load();
                 importarEditar.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        importarEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	//fileUpload: true,
	width:700,
	autoHeight:true,  
	autoScroll:true,
	labelWidth: 180,
	border:false,
	bodyStyle:'padding:10px;',
	items:[
		this.co_proyecto_acc_espec,
		this.id_proyecto,
		this.fielset1,
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Acciones Especificas del Proyecto',
    modal:true,
    constrain:true,
width:714,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
        this.guardar,
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
tabuladorSiete.main.mascara.hide();
},
getStoreCO_UNIDADES_MEDIDA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto_ae/funcion.php',
        root:'data',
	baseParams: {
		op: 2
	},
        fields:[
            {name: 'co_unidades_medida'},{name: 'tx_unidades_medida'}
            ]
    });
    return this.store;
},
getStoreID_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto_ae/funcion.php',
        root:'data',
	baseParams: {
		op: 3
	},
        fields:[
            {name: 'co_ejecutores'},{name: 'tx_ejecutor'}
            ]
    });
    return this.store;
}
};
Ext.onReady(importarEditar.main.init, importarEditar.main);
</script>
