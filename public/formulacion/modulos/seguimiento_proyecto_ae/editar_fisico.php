<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT t40.*,descripcion,tx_codigo FROM proyecto_seguimiento.tab_proyecto_aerec as t40
	inner join proyecto_seguimiento.tab_proyecto_ae as t39 on t40.id_tab_proyecto_ae=t39.id
	where t40.id=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_proyecto_acc_espec_rec"     => trim($row["id"]),
			"id_proyecto"     => trim($row["id_tab_proyecto"]),
			"co_proyecto_acc_espec"     => trim($row["id_tab_proyecto_ae"]),
			"nb_accion"     => trim($row["descripcion"]),
			"tx_codigo"     => trim($row["tx_codigo"]),
			"fisico_01"     => trim($row["fisico_01"]),
			"fisico_02"     => trim($row["fisico_02"]),
			"fisico_03"     => trim($row["fisico_03"]),
			"fisico_04"     => trim($row["fisico_04"]),
			"fisico_05"     => trim($row["fisico_05"]),
			"fisico_06"     => trim($row["fisico_06"]),
			"fisico_07"     => trim($row["fisico_07"]),
			"fisico_08"     => trim($row["fisico_08"]),
			"fisico_09"     => trim($row["fisico_09"]),
			"fisico_10"     => trim($row["fisico_10"]),
			"fisico_11"     => trim($row["fisico_11"]),
			"fisico_12"     => trim($row["fisico_12"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_proyecto_acc_espec_rec"     => "",
		"id_proyecto"     => "",
		"co_proyecto_acc_espec"     => "",
		"tx_codigo"     => "",
		"fisico_01"     => "",
		"fisico_02"     => "",
		"fisico_03"     => "",
		"fisico_04"     => "",
		"fisico_05"     => "",
		"fisico_06"     => "",
		"fisico_07"     => "",
		"fisico_08"     => "",
		"fisico_09"     => "",
		"fisico_10"     => "",
		"fisico_11"     => "",
		"fisico_12"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("importarEditar");
importarEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_proyecto_acc_espec_rec = new Ext.form.Hidden({
    name:'co_proyecto_acc_espec_rec',
    value:this.OBJ.co_proyecto_acc_espec_rec});
//</ClavePrimaria>
this.op = new Ext.form.Hidden({
	name:'op',
	value:5
});

this.tx_codigo = new Ext.form.TextField({
	fieldLabel:'CODIGO',
	name:'tx_codigo',
	value:this.OBJ.tx_codigo,
	width:100,
	maxLength: 250,
	readOnly:true,
	style:'background:#c9c9c9;'
});

this.nb_accion = new Ext.form.TextArea({
	fieldLabel:'NOMBRE DE LA ACCION',
	name:'nb_accion',
	value:this.OBJ.nb_accion,
	width:400,
	maxLength: 250,
	readOnly:true,
	style:'background:#c9c9c9;'
});

this.fisico_01 = new Ext.form.NumberField({
	fieldLabel:'ENERO',
	name:'fisico_01',
	value:this.OBJ.fisico_01,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_02 = new Ext.form.NumberField({
	fieldLabel:'FEBRERO',
	name:'fisico_02',
	value:this.OBJ.fisico_02,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_03 = new Ext.form.NumberField({
	fieldLabel:'MARZO',
	name:'fisico_03',
	value:this.OBJ.fisico_03,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_04 = new Ext.form.NumberField({
	fieldLabel:'ABRIL',
	name:'fisico_04',
	value:this.OBJ.fisico_04,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_05 = new Ext.form.NumberField({
	fieldLabel:'MAYO',
	name:'fisico_05',
	value:this.OBJ.fisico_05,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_06 = new Ext.form.NumberField({
	fieldLabel:'JUNIO',
	name:'fisico_06',
	value:this.OBJ.fisico_06,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_07 = new Ext.form.NumberField({
	fieldLabel:'JULIO',
	name:'fisico_07',
	value:this.OBJ.fisico_07,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_08 = new Ext.form.NumberField({
	fieldLabel:'AGOSTO',
	name:'fisico_08',
	value:this.OBJ.fisico_08,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_09 = new Ext.form.NumberField({
	fieldLabel:'SEPTIEMBRE',
	name:'fisico_09',
	value:this.OBJ.fisico_09,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_10 = new Ext.form.NumberField({
	fieldLabel:'OCTUBRE',
	name:'fisico_10',
	value:this.OBJ.fisico_10,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_11 = new Ext.form.NumberField({
	fieldLabel:'NOVIEMBRE',
	name:'fisico_11',
	value:this.OBJ.fisico_11,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fisico_12 = new Ext.form.NumberField({
	fieldLabel:'DICIEMBRE',
	name:'fisico_12',
	value:this.OBJ.fisico_12,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
});

this.fielset2 = new Ext.form.FieldSet({
              title:'MESES',width:620,
              items:[
		this.fisico_01,
		this.fisico_02,
		this.fisico_03,
		this.fisico_04,
		this.fisico_05,
		this.fisico_06,
		this.fisico_07,
		this.fisico_08,
		this.fisico_09,
		this.fisico_10,
		this.fisico_11,
		this.fisico_12
]});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.tx_codigo,
		this.nb_accion,
		this.fielset2,
		this.op
]});

this.guardar = new Ext.Button({
    text:'Guardar',
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
	fileUpload: true,
	width:700,
	autoHeight:true,  
	autoScroll:true,
	labelWidth: 180,
	border:false,
	bodyStyle:'padding:10px;',
	items:[
		this.co_proyecto_acc_espec_rec,
		this.fielset1,
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Distribucion Fisica',
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
}
};
Ext.onReady(importarEditar.main.init, importarEditar.main);
</script>
