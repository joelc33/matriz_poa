<?php
session_start();
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

$usuario = (object) $_SESSION;
//anio de ejercicio fiscal activo
$sql2 = "SELECT id as co_ejercicio_fiscal FROM mantenimiento.tab_ejercicio_fiscal WHERE id = ".$_SESSION['ejercicio_fiscal'].";";
$resultado2 = $comunes->ObtenerFilasBySqlSelect($sql2);
$fechaI = '01-01-'.$resultado2[0]['co_ejercicio_fiscal'];
$fechaF = '31-12-'.$resultado2[0]['co_ejercicio_fiscal'];
$id_ejercicio = $resultado2[0]['co_ejercicio_fiscal'];
$id_ejecutor = $_SESSION['id_ejecutor'];
$id_tab_ejecutor = $_SESSION['co_ejecutores'];

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT * FROM t26_proyectos WHERE co_proyectos=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data1 = json_encode(array(
			"co_proyectos"     => trim($row["co_proyectos"]),
			"id_proyecto"     => trim($row["id_proyecto"]),
			"id_ejercicio"     => trim($row["id_ejercicio"]),
			"id_ejecutor"     => trim($row["id_ejecutor"]),
			"tipo_registro"     => trim($row["tipo_registro"]),
			"nb_proyecto"     => trim($row["nombre"]),
			"co_estatus_proyecto"     => trim($row["status_registro"]),
			"codigo_new_etapa"     => trim($row["codigo_new_etapa"]),
			"fecha_inicio"     => trim($row["fecha_inicio"]),
			"fecha_fin"     => trim($row["fecha_fin"]),
			"tx_objetivo_general"     => trim($row["objetivo"]),
			"tx_descripcion_proyecto"     => trim($row["descripcion"]),
			"co_situacion_presupuestaria"     => trim($row["sit_presupuesto"]),
			"mo_total"     => trim($row["monto"]),
			"co_sector"     => trim($row["clase_sector"]),
			"co_sub_sector"     => trim($row["clase_subsector"]),
			"co_plan"     => trim($row["plan_operativo"]),
			"id_tab_ejecutor"     => trim($row["id_tab_ejecutor"]),
		));
	$id_proyecto = trim($row["id_proyecto"]);
	$id_ejercicio = trim($row["id_ejercicio"]);
	$co_estatus = trim($row["co_estatus"]);
		if($co_estatus==3){
			$deshabilitado = 'true';
		}else{
			$deshabilitado = 'false';
		}
	}

$sql3 = "SELECT co_proyecto_vinculos FROM t32_proyecto_vinculos WHERE id_proyecto='".$id_proyecto."';";
$resultado3 = $comunes->ObtenerFilasBySqlSelect($sql3);
$co_proyecto_vinculos = $resultado3[0]['co_proyecto_vinculos'];
$sql4 = "SELECT co_proyecto_localizacion FROM t33_proyecto_localizacion WHERE id_proyecto='".$id_proyecto."';";
$resultado4 = $comunes->ObtenerFilasBySqlSelect($sql4);
$co_proyecto_localizacion = $resultado4[0]['co_proyecto_localizacion'];
$sql5 = "SELECT co_proyecto_imagen FROM t36_proyecto_imagen WHERE id_proyecto='".$id_proyecto."';";
$resultado5 = $comunes->ObtenerFilasBySqlSelect($sql5);
$co_proyecto_imagen = $resultado5[0]['co_proyecto_imagen'];
$sql6 = "SELECT co_proyecto_responsables FROM t37_proyecto_responsables WHERE id_proyecto='".$id_proyecto."';";
$resultado6 = $comunes->ObtenerFilasBySqlSelect($sql6);
$co_proyecto_responsables = $resultado6[0]['co_proyecto_responsables'];
$sql7 = "SELECT co_proyecto_alcance FROM t38_proyecto_alcance WHERE id_proyecto='".$id_proyecto."';";
$resultado7 = $comunes->ObtenerFilasBySqlSelect($sql7);
$co_proyecto_alcance = $resultado7[0]['co_proyecto_alcance'];
$sql9 = "SELECT co_proyecto_financiamiento FROM t63_proyecto_financiamiento WHERE id_proyecto='".$id_proyecto."';";
$resultado9 = $comunes->ObtenerFilasBySqlSelect($sql9);
$co_proyecto_financiamiento = $resultado9[0]['co_proyecto_financiamiento'];

	include("tabuladorDos.php");
	include("tabuladorTres.php");
	include("tabuladorCuatro.php");
	include("tabuladorCinco.php");
	include("tabuladorSeis.php");
	include("tabuladorSiete.php");
	include("tabuladorOcho.php");
	include("tabuladorNueve.php");
}else{
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
		"id_tab_ejecutor"     => $id_tab_ejecutor,
	));
}
?>
<script type="text/javascript">
Ext.ns("editarProyecto");
function formatoNumero(val){
	    return paqueteComunJS.funcion.getNumeroFormateado(val);
return val;
};
editarProyecto.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

<?php if($codigo!=''||$codigo!=null){?>
tabuladorDos.main.init();
tabuladorTres.main.init();
tabuladorCuatro.main.init();
tabuladorCinco.main.init();
tabuladorSeis.main.init();
tabuladorSiete.main.init();
tabuladorOcho.main.init();
tabuladorNueve.main.init();
<?php }?>

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
this.id_tab_ejecutor = new Ext.form.Hidden({
	name:'id_tab_ejecutor',
	value:this.OBJ.id_tab_ejecutor,
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
	readOnly:<?php echo $deshabilitado ?>,
	//readOnly:(this.OBJ.co_sistema!='')?false:true,
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
	allowBlank:false,
        onSelect: function(record){
		editarProyecto.main.id_ejecutor.setValue(record.data.id_ejecutor);
		editarProyecto.main.id_tab_ejecutor.setValue(record.data.co_ejecutores);
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
	name:'nb_proyecto',
	value:this.OBJ.nb_proyecto,
	width:500,
<?php if( $usuario->co_rol < 3 ){ ?>
	readOnly:<?php echo $deshabilitado ?>,
<?php }else{ ?>
	readOnly:true,
	style:'background:#c9c9c9;',
<?php } ?>
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
	readOnly:<?php echo $deshabilitado ?>,
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
	readOnly:<?php echo $deshabilitado ?>,
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
	readOnly:<?php echo $deshabilitado ?>,
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
	readOnly:<?php echo $deshabilitado ?>,
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
	readOnly:<?php echo $deshabilitado ?>,
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
	readOnly:<?php echo $deshabilitado ?>,
	minLength : 1,
	maxLength: 20,
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
	name:'tx_descripcion_proyecto',
	value:this.OBJ.tx_descripcion_proyecto,
	allowBlank:false,
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
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
	readOnly:<?php echo $deshabilitado ?>,
	resizable:true,
	allowBlank:false,
	listeners:{
            change: function(){
                editarProyecto.main.storeCO_SUB_SECTOR.load({
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
		callback: function(){editarProyecto.main.co_sub_sector.setValue(editarProyecto.main.OBJ.co_sub_sector);}
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
	readOnly:<?php echo $deshabilitado ?>,
	resizable:true,
//	allowBlank:false
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
	readOnly:<?php echo $deshabilitado ?>,
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
		this.id_tab_ejecutor,
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
	this.panelDatos1,
<?php if($codigo!=''||$codigo!=null){?>
	tabuladorDos.main.panelDatos,
	tabuladorTres.main.panelDatos,
	tabuladorCuatro.main.panelDatos,
	tabuladorCinco.main.panelDatos,
	tabuladorSeis.main.panelDatos,
	tabuladorSiete.main.panelDatos,
	tabuladorOcho.main.panelDatos,
	tabuladorNueve.main.panelDatos,
<?php }?>
	]
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){
	//editarProyecto.main.panel.remove(tabuladorDos.main.panelDatos);
        if(!editarProyecto.main.formulario.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        editarProyecto.main.formulario.getForm().submit({
            method:'POST',
	    /*headers: {'Content-Type': 'application/json;charset=utf-8'},
            params: 'data='+Ext.util.JSON.encode(nuevoExpendedor.main.formulario.getForm().getValues()),*/
            url:'formulacion/modulos/proyecto/funcion.php?op=999',
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
                 opcionPlanificador.main.store_lista.load();
		var direccionar = Ext.get('contenedoreditarProyecto<?php echo $codigo ?>');
		direccionar.load({ url: 'formulacion/modulos/proyecto/editarProyecto.php', scripts: true, text: 'Cargando...',              params:'codigo='+action.result.c});
             }
        });


    }
});

<?php if($co_estatus==3){?>
this.guardar.disable();
<?php }?>

this.formulario = new Ext.form.FormPanel({
    autoWidth:true,
    fileUpload: true,
    border:false,
    labelWidth: 210,
    padding:'5px',
    deferredRender: false,
    items: [
	this.co_proyectos,
	this.panel
    ],
<?php if( in_array( array( 'de_privilegio' => 'proyecto.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
    buttonAlign:'left',
    buttons:[
        this.guardar
    ]
<?php } ?>
});

this.formulario.render("contenedoreditarProyecto<?php echo $codigo ?>");
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
            {name: 'co_ejecutores'},{name: 'id_ejecutor'},{name: 'tx_ejecutor'}
            ]
    });
    return this.store;
}
};
Ext.onReady(editarProyecto.main.init, editarProyecto.main);
</script>
<div id="contenedoreditarProyecto<?php echo $codigo ?>"></div>
<div id="formulario_ubicacion"></div>
