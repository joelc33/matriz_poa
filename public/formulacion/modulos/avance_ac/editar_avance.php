<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");
include("subir_imagen.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT * FROM ac_seguimiento.tab_meta_seguimiento
	where id=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"id_tab_meta_seguimiento"     => trim($row["id"]),
			"id_tab_meta_fisica"     => trim($row["id_tab_meta_fisica"]),
			"co_partida"     => trim($row["nu_partida"]),
			"mo_presupuesto"     => trim($row["mo_presupuesto"]),
			"nu_ponderacion"     => trim($row["nu_ponderacion"]),
			"fe_inicio"     => trim($row["fe_inicio"]),
			"fe_fin"     => trim($row["fe_fin"]),
			"tx_observacion"     => trim($row["tx_observacion"]),
			"nu_lapso"     => trim($row["nu_lapso"]),
			"tx_lapso"     => decode($_POST['tx_lapso']),
		));
	}
}else{
	$data = json_encode(array(
		"id_tab_meta_seguimiento"     => decode($_POST['id']),
		"id_tab_meta_fisica"     => decode($_POST['id_tab_meta_fisica']),
		"nu_lapso"     => decode($_POST['nu_lapso']),
		"tx_lapso"     => decode($_POST['tx_lapso']),
	));
}
?>
<script type="text/javascript">
Ext.ns("avanceEditarPR");
avanceEditarPR.main = {
imagen: function(codigo){
	return  '<img width="330" height="240" src="modulos/imagen/funcion.php?op=6&codigo='+codigo+'">';
},
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeCO_PARTIDA = this.getStoreCO_PARTIDA();
//<Stores de fk>

//<Stores de imagenes>
this.upload_number=1;

this.storeDatosImagenes=avanceEditarPR.main.getStoreImagenes();

if(this.OBJ.id_tab_meta_seguimiento){
    avanceEditarPR.main.storeDatosImagenes.baseParams.id_tab_meta_seguimiento = this.OBJ.id_tab_meta_seguimiento;
    avanceEditarPR.main.storeDatosImagenes.load();
}
//<Stores de imagenes>

//<ClavePrimaria>
this.id_tab_meta_seguimiento = new Ext.form.Hidden({
	name:'id_tab_meta_seguimiento',
	value:this.OBJ.id_tab_meta_seguimiento
});
this.id_tab_meta_fisica = new Ext.form.Hidden({
	name:'id_tab_meta_fisica',
	value:this.OBJ.id_tab_meta_fisica
});
this.nu_lapso = new Ext.form.Hidden({
	name:'nu_lapso',
	value:this.OBJ.nu_lapso
});
//</ClavePrimaria>
this.op = new Ext.form.Hidden({
	name:'op',
	value:5
});

this.fecha_inicio = new Ext.form.DateField({
	fieldLabel:'FECHA DE INICIO',
	name:'fe_inicio',
	//value:(this.OBJ.fecha_inicio)?this.OBJ.fecha_inicio:'<?= date('d-m-Y',strtotime($fechaI)); ?>',
	value:this.OBJ.fe_inicio,
	allowBlank:false,
	width:100,
	/*minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',*/
});

this.fecha_culminacion = new Ext.form.DateField({
	fieldLabel:'FECHA DE CULMINACIÓN',
	name:'fe_fin',
	//value:(this.OBJ.fecha_culminacion)?this.OBJ.fecha_culminacion:'<?= date('d-m-Y',strtotime($fechaF)); ?>',
	value:this.OBJ.fe_fin,
	allowBlank:false,
	width:100,
	/*minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',*/
});

this.co_partida = new Ext.form.ComboBox({
	fieldLabel:'PARTIDA',
	store: this.storeCO_PARTIDA,
	typeAhead: true,
	valueField: 'co_partida',
	displayField:'co_partida',
	hiddenName:'co_partida',
	//readOnly:(this.OBJ.co_partida!='')?true:false,
	//style:(this.OBJ.co_partida!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Partida',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	resizable:true,
	allowBlank:false
});

this.storeCO_PARTIDA.load({
		params: {id_tab_meta_fisica:this.OBJ.id_tab_meta_fisica}
	});
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_partida,
	value:  this.OBJ.co_partida,
	objStore: this.storeCO_PARTIDA
});

this.mo_presupuesto = new Ext.form.NumberField({
	fieldLabel:'PRESUPUESTO BS.',
	name:'mo_presupuesto',
	value:this.OBJ.mo_presupuesto,
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

this.tx_observacion = new Ext.form.TextArea({
	fieldLabel:'OBSERVACIONES',
	name:'tx_observacion',
	value:this.OBJ.tx_observacion,
	allowBlank:false,
	width:300,
	height:80,
	maxLength: 200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.comFechaInCul = new Ext.form.CompositeField({
fieldLabel: 'INICIO',
items: [
	this.fecha_inicio,
             {
                   xtype: 'displayfield',
                   value: '&nbsp;&nbsp;&nbsp; FIN:',
                   width: 190
             },
	this.fecha_culminacion
	]
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:false,
	border:true,
	title:'Tiempo de Ejecucion',
        items:[
		this.comFechaInCul,
		this.op
		]
});

this.fieldset2 = new Ext.form.FieldSet({
	autoWidth:false,
	border:true,
	title:'Detalles del Avance',
        items:[
		this.co_partida,
		this.mo_presupuesto,
		this.nu_ponderacion,
		this.tx_observacion
		]
});

this.panelDatos1 = new Ext.Panel({
    title: 'Lapso '+this.OBJ.tx_lapso,
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.fieldset1,
		this.fieldset2
	]
});

upload.main.init();
this.panel = new Ext.TabPanel({
    activeTab:0,
    height:370,
    enableTabScroll:true,
    deferredRender: false,
    items:[
	this.panelDatos1,
	upload.main.PanelUpload
	]
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!avanceEditarPR.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        avanceEditarPR.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/avance_ac/funcion.php',
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
		avanceListaSAP.main.store_lista_<?php echo trim($_POST['nu_lapso'])?>.load();
		var d=document.getElementById('moreUploads');
		while (d.hasChildNodes()){d.removeChild(d.firstChild);}
		upload.main.addFileInput()
		avanceEditarPR.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        avanceEditarPR.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	fileUpload: true,
	width:700,
	autoHeight:true,  
	autoScroll:true,
	labelWidth: 180,
	border:false,
	bodyStyle:'padding:5px;',
	items:[this.id_tab_meta_seguimiento,
		this.id_tab_meta_fisica,
		this.nu_lapso,
		this.panel
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Avance',
    modal:true,
    constrain:true,
width:714,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
	bbar: [
		'->',this.guardar,
	],
    /*buttons:[
        this.guardar,
        this.salir
    ],*/
    buttonAlign:'center'
});
this.winformPanel_.show();
avanceListaSAP.main.mascara.hide();
},
getStoreCO_PARTIDA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/avance_ac/funcion.php',
        root:'data',
	baseParams: {
		op: 2,
		id_tab_meta_fisica: this.OBJ.id_tab_meta_fisica
	},
        fields:[
            {name: 'co_partida'}
            ]
    });
    return this.store;
},
getStoreImagenes: function(){
	this.store = new Ext.data.JsonStore({
		url:'formulacion/modulos/avance_ac/funcion.php',
		root:'data',
		baseParams: {
			op: 6
		},
		fields:[{name: 'co_img_avance'}]
	});
	return this.store;
}
};
Ext.onReady(avanceEditarPR.main.init, avanceEditarPR.main);
</script>
