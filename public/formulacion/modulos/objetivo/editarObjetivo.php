<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
} 
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT * FROM t20_planes where co_planes=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_planes"     => trim($row["co_planes"]),
			"co_objetivo_historico"     => trim($row["co_objetivo_historico"]),
			"co_objetivo_nacional"     => trim($row["co_objetivo_nacional"]),
			"co_objetivo_estrategico"     => trim($row["co_objetivo_estrategico"]),
			"co_objetivo_general"     => trim($row["co_objetivo_general"]),
			"nu_nivel"     => trim($row["nu_nivel"]),
			"tx_codigo"     => trim($row["tx_codigo"]),
			"nu_codigo"     => trim($row["nu_codigo"]),
			"tx_descripcion"     => trim($row["tx_descripcion"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_planes"     => "",
		"co_objetivo_historico"     => "",
		"co_objetivo_nacional"     => "",
		"co_objetivo_estrategico"    => "",
		"co_objetivo_general"     => "",
		"nu_nivel"     => "",
		"tx_codigo"     => "",
		"nu_codigo"     => "",
		"tx_descripcion"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("objetivoEditar");
objetivoEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_planes = new Ext.form.Hidden({
    name:'co_planes',
    value:this.OBJ.co_planes});
//</ClavePrimaria>

this.tx_codigo = new Ext.form.TextField({
	fieldLabel:'Codigo',
	name:'tx_codigo',
	value:this.OBJ.tx_codigo,
	width:100,
	maxLength: 10,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 10},
	//readOnly:(this.OBJ.tx_codigo!='')?true:false,
	//style:(this.OBJ.tx_codigo!='')?'background:#c9c9c9;':'',
});

this.co_objetivo_historico = new Ext.form.NumberField({
	fieldLabel:'Objetivo Historico',
	name:'co_objetivo_historico',
	value:this.OBJ.co_objetivo_historico,
	//allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 1},
});

this.co_objetivo_nacional = new Ext.form.TextField({
	fieldLabel:'Objetivo Nacional',
	name:'co_objetivo_nacional',
	value:this.OBJ.co_objetivo_nacional,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2}
});

this.co_objetivo_estrategico = new Ext.form.TextField({
	fieldLabel:'Objetivo Estrategico',
	name:'co_objetivo_estrategico',
	value:this.OBJ.co_objetivo_estrategico,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2}
});

this.co_objetivo_general = new Ext.form.TextField({
	fieldLabel:'Objetivo General',
	name:'co_objetivo_general',
	value:this.OBJ.co_objetivo_general,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2}
});

this.nu_nivel = new Ext.form.NumberField({
	fieldLabel:'Nivel',
	name:'nu_nivel',
	value:this.OBJ.nu_nivel,
	//allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 1},
});

this.tx_descripcion = new Ext.form.TextArea({
	fieldLabel:'Descripcion',
	name:'tx_descripcion',
	value:this.OBJ.tx_descripcion,
	width:400,
	maxLength: 600,
	height:100,
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.tx_codigo,
		this.co_objetivo_historico,
		this.co_objetivo_nacional,
		this.co_objetivo_estrategico,
		this.co_objetivo_general,
		this.nu_nivel,
		this.tx_descripcion
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!objetivoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        objetivoEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/objetivo/funcion.php?op=2',
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
                 objetivoLista.main.store_lista.load();
                 objetivoEditar.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        objetivoEditar.main.winformPanel_.close();
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
		this.co_planes,
		this.fielset1,
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Objetivos',
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
<?php if( in_array( array( 'de_privilegio' => 'objetivos.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
objetivoLista.main.mascara.hide();
}
};
Ext.onReady(objetivoEditar.main.init, objetivoEditar.main);
</script>
