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
	$sql = "SELECT * FROM t28_cargo	where co_cargo=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_cargo"     => trim($row["co_cargo"]),
			"tx_cargo"     => trim($row["tx_cargo"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_cargo"     => "",
		"tx_cargo"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("cargoEditar");
cargoEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_cargo = new Ext.form.Hidden({
    name:'co_cargo',
    value:this.OBJ.co_cargo});
//</ClavePrimaria>

this.tx_cargo = new Ext.form.TextField({
	fieldLabel:'Descripcion',
	name:'tx_cargo',
	value:this.OBJ.tx_cargo,
	width:400
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.tx_cargo
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!cargoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        cargoEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/cargo/funcion.php?op=2',
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
                 cargoLista.main.store_lista.load();
                 cargoEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        cargoEditar.main.winformPanel_.close();
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
		this.co_cargo,
		this.fielset1,
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Cargos',
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
<?php if( in_array( array( 'de_privilegio' => 'cargos.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
cargoLista.main.mascara.hide();
}
};
Ext.onReady(cargoEditar.main.init, cargoEditar.main);
</script>
