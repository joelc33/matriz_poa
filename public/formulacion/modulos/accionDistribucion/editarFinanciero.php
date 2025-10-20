<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT t40.*,descripcion,tx_codigo,total FROM t40_proyecto_acc_espec_rec as t40
	inner join t39_proyecto_acc_espec as t39 on t40.co_proyecto_acc_espec=t39.co_proyecto_acc_espec
	where t40.co_proyecto_acc_espec_rec=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_proyecto_acc_espec_rec"     => trim($row["co_proyecto_acc_espec_rec"]),
			"id_proyecto"     => trim($row["id_proyecto"]),
			"co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
			"nb_accion"     => trim($row["descripcion"]),
			"tx_codigo"     => trim($row["tx_codigo"]),
			"presup_01"     => trim($row["presup_01"]),
			"presup_02"     => trim($row["presup_02"]),
			"presup_03"     => trim($row["presup_03"]),
			"presup_04"     => trim($row["presup_04"]),
			"presup_05"     => trim($row["presup_05"]),
			"presup_06"     => trim($row["presup_06"]),
			"presup_07"     => trim($row["presup_07"]),
			"presup_08"     => trim($row["presup_08"]),
			"presup_09"     => trim($row["presup_09"]),
			"presup_10"     => trim($row["presup_10"]),
			"presup_11"     => trim($row["presup_11"]),
			"presup_12"     => trim($row["presup_12"]),
		));
	$total = trim($row["total"]);
	}
}else{
	$data = json_encode(array(
		"co_proyecto_acc_espec_rec"     => "",
		"id_proyecto"     => "",
		"co_proyecto_acc_espec"     => "",
		"tx_codigo"     => "",
		"presup_01"     => "",
		"presup_02"     => "",
		"presup_03"     => "",
		"presup_04"     => "",
		"presup_05"     => "",
		"presup_06"     => "",
		"presup_07"     => "",
		"presup_08"     => "",
		"presup_09"     => "",
		"presup_10"     => "",
		"presup_11"     => "",
		"presup_12"     => "",
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

this.presup_01 = new Ext.form.NumberField({
	fieldLabel:'ENERO',
	name:'presup_01',
	value:this.OBJ.presup_01,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_02 = new Ext.form.NumberField({
	fieldLabel:'FEBRERO',
	name:'presup_02',
	value:this.OBJ.presup_02,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_03 = new Ext.form.NumberField({
	fieldLabel:'MARZO',
	name:'presup_03',
	value:this.OBJ.presup_03,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_04 = new Ext.form.NumberField({
	fieldLabel:'ABRIL',
	name:'presup_04',
	value:this.OBJ.presup_04,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_05 = new Ext.form.NumberField({
	fieldLabel:'MAYO',
	name:'presup_05',
	value:this.OBJ.presup_05,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_06 = new Ext.form.NumberField({
	fieldLabel:'JUNIO',
	name:'presup_06',
	value:this.OBJ.presup_06,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_07 = new Ext.form.NumberField({
	fieldLabel:'JULIO',
	name:'presup_07',
	value:this.OBJ.presup_07,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_08 = new Ext.form.NumberField({
	fieldLabel:'AGOSTO',
	name:'presup_08',
	value:this.OBJ.presup_08,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_09 = new Ext.form.NumberField({
	fieldLabel:'SEPTIEMBRE',
	name:'presup_09',
	value:this.OBJ.presup_09,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_10 = new Ext.form.NumberField({
	fieldLabel:'OCTUBRE',
	name:'presup_10',
	value:this.OBJ.presup_10,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_11 = new Ext.form.NumberField({
	fieldLabel:'NOVIEMBRE',
	name:'presup_11',
	value:this.OBJ.presup_11,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.presup_12 = new Ext.form.NumberField({
	fieldLabel:'DICIEMBRE',
	name:'presup_12',
	value:this.OBJ.presup_12,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 12,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 12},
	//decimalPrecision: 2,
	decimalPrecision: 0,
	allowDecimals: false,
	allowNegative: false,
});

this.fielset2 = new Ext.form.FieldSet({
              title:'MESES: Distribucion Presupuestaria',width:620,
              items:[
		this.presup_01,
		this.presup_02,
		this.presup_03,
		this.presup_04,
		this.presup_05,
		this.presup_06,
		this.presup_07,
		this.presup_08,
		this.presup_09,
		this.presup_10,
		this.presup_11,
		this.presup_12
]});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.tx_codigo,
		this.nb_accion,
		this.fielset2
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!importarEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

	var total = importarEditar.main.presup_01.getValue()+importarEditar.main.presup_02.getValue()+importarEditar.main.presup_03.getValue()+importarEditar.main.presup_04.getValue()+importarEditar.main.presup_05.getValue()+importarEditar.main.presup_06.getValue()+importarEditar.main.presup_07.getValue()+importarEditar.main.presup_08.getValue()+importarEditar.main.presup_09.getValue()+importarEditar.main.presup_10.getValue()+importarEditar.main.presup_11.getValue()+importarEditar.main.presup_12.getValue();

        if(total !== <?php echo $total ?> ){
            Ext.Msg.alert("Atención","La cantidad total por Acción Específica debe coincidir con el declarado").setIcon(Ext.MessageBox.ERROR);
            return false;
        }

        importarEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/accionDistribucion/funcion.php?op=8',
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
    title:'Formulario: Distribucion Finaciera',
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
