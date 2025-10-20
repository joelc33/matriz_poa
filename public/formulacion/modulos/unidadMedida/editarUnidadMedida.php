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
	$sql = "SELECT * FROM mantenimiento.tab_unidad_medida where id=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_unidades_medida"     => trim($row["id"]),
			"tx_unidades_medida"     => trim($row["de_unidad_medida"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_unidades_medida"     => "",
		"tx_unidades_medida"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("unidadMedidaEditar");
unidadMedidaEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_unidades_medida = new Ext.form.Hidden({
    name:'co_unidades_medida',
    value:this.OBJ.co_unidades_medida});
//</ClavePrimaria>

this.tx_unidades_medida = new Ext.form.TextField({
	fieldLabel:'Descripcion',
	name:'tx_unidades_medida',
	value:this.OBJ.tx_unidades_medida,
	width:400,
	maxLength: 400
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.tx_unidades_medida
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!unidadMedidaEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        unidadMedidaEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/unidadMedida/funcion.php?op=2',
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
                 unidadMedidaLista.main.store_lista.load();
                 unidadMedidaEditar.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        unidadMedidaEditar.main.winformPanel_.close();
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
		this.co_unidades_medida,
		this.fielset1,
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Unidades de Medida',
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
<?php if( in_array( array( 'de_privilegio' => 'unidades.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
unidadMedidaLista.main.mascara.hide();
}
};
Ext.onReady(unidadMedidaEditar.main.init, unidadMedidaEditar.main);
</script>
