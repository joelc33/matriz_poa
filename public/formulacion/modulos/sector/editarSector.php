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
	$sql = "SELECT * FROM t18_sectores WHERE co_sectores=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_sectores"     => trim($row["co_sectores"]),
			"co_sector"     => trim($row["co_sector"]),
			"co_sub_sector"     => trim($row["co_sub_sector"]),
			"nu_nivel"     => trim($row["nu_nivel"]),
			"tx_codigo"     => trim($row["tx_codigo"]),
			"tx_descripcion"     => trim($row["tx_descripcion"]),
			"nu_descripcion"     => trim($row["nu_descripcion"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_sectores"     => "",
		"co_sector"     => "",
		"co_sub_sector"     => "",
		"nu_nivel"     => "",
		"tx_codigo"    => "",
		"tx_descripcion"     => "",
		"nu_descripcion"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("sectorEditar");
sectorEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_sectores = new Ext.form.Hidden({
    name:'co_sectores',
    value:this.OBJ.co_sectores});
//</ClavePrimaria>

this.co_sector = new Ext.form.TextField({
	fieldLabel:'Sector',
	name:'co_sector',
	value:this.OBJ.co_sector,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2},
	//readOnly:(this.OBJ.co_sector!='')?true:false,
	//style:(this.OBJ.co_sector!='')?'background:#c9c9c9;':'',
});

this.co_sub_sector = new Ext.form.TextField({
	fieldLabel:'Sub-Sector',
	name:'co_sub_sector',
	value:this.OBJ.co_sub_sector,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2},
	//readOnly:(this.OBJ.co_sub_sector!='')?true:false,
	//style:(this.OBJ.co_sub_sector!='')?'background:#c9c9c9;':'',
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

this.tx_descripcion = new Ext.form.TextField({
	fieldLabel:'Descripcion',
	name:'tx_descripcion',
	value:this.OBJ.tx_descripcion,
	width:400,
	maxLength: 400,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 400},
	//readOnly:(this.OBJ.tx_descripcion!='')?true:false,
	//style:(this.OBJ.tx_descripcion!='')?'background:#c9c9c9;':'',
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.co_sector,
		this.co_sub_sector,
		this.nu_nivel,
		this.tx_descripcion
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!sectorEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        sectorEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/sector/funcion.php?op=2',
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
                 sectorLista.main.store_lista.load();
                 sectorEditar.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        sectorEditar.main.winformPanel_.close();
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
		this.co_sectores,
		this.fielset1,
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Sectores',
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
<?php if( in_array( array( 'de_privilegio' => 'sectores.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
sectorLista.main.mascara.hide();
}
};
Ext.onReady(sectorEditar.main.init, sectorEditar.main);
</script>
