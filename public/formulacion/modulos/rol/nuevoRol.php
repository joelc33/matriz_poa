<?php        
session_start(); 
if( $_SESSION['estatus'] !== 'OK' ) {
    http_response_code(403);
	die();
}
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){

	try{

	$paraTransaccion->BeginTrans();
	$tabla="autenticacion.tab_rol";
	$primaryKey="id";
	$variable["de_rol"] = decode($_POST['tx_rol']);
	$variable["created_at"] = date("Y-m-d H:i:s");
	$variable["in_estatus"] = 'TRUE';
	$co_rol = $comunes->InsertConID($tabla,$variable,$primaryKey);

	if ($co_rol){
		$paraTransaccion->CommitTrans();
		echo json_encode(array(
			    "success" => true,
			    "msg" => 'Proceso realizado exitosamente.'
		));
	}
	else{
		$paraTransaccion->RollbackTrans();
		}
	}catch(Exception $e){
		echo json_encode(array(
			    "success" => false,
			    "msg" => "Error en Transaccion.\n".$e->getMessage()
		));
	}

}else{
	$data = json_encode(array(
		"co_rol"     => "",
		"tx_rol"     => "",
	));
?>
<script type="text/javascript">
Ext.ns("rolEditar");
rolEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_rol = new Ext.form.Hidden({
    name:'co_rol',
    value:this.OBJ.co_rol});
//</ClavePrimaria>


this.tx_rol = new Ext.form.TextField({
	fieldLabel:'Nombre del Rol',
	name:'tx_rol',
	value:this.OBJ.tx_rol,
	allowBlank:false,
	width:200
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!rolEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        rolEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/rol/nuevoRol.php?op=1',
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
                 rolLista.main.store_lista.load();
                 rolEditar.main.winformPanel_.hide();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        rolEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
    //frame:true,
    width:400,border:false,
autoHeight:true,  
    autoScroll:true,
    bodyStyle:'padding:10px;',
    items:[

                    this.co_rol,
                    this.tx_rol,
            ]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: rol',
    modal:true,
    constrain:true,
width:400,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
<?php if( in_array( array( 'de_privilegio' => 'privilegios.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
rolLista.main.mascara.hide();
}
};
Ext.onReady(rolEditar.main.init, rolEditar.main);
</script>
<?php
}
?>
