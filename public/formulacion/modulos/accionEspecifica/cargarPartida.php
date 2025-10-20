<?php
session_start();
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);

$sql = "SELECT * FROM t26_proyectos WHERE id_proyecto='".$codigo."'";
$result = $comunes->ObtenerFilasBySqlSelect($sql);
foreach($result as $key => $row){
	$data = json_encode(array(
		"co_proyectos"     => trim($row["co_proyectos"]),
		"id_proyecto"     => trim($row["id_proyecto"]),
		"id_ejercicio"     => trim($row["id_ejercicio"]),
		"id_ejecutor"     => trim($row["id_ejecutor"]),
		"tipo_registro"     => trim($row["tipo_registro"]),
		"nb_proyecto"     => trim($row["nombre"]),
	));
}
?>
<script type="text/javascript">
Ext.ns("cargarPartida");
cargarPartida.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_proyecto_acc_espec = new Ext.form.Hidden({
    name:'co_proyecto_acc_espec',
    value:this.OBJ.co_proyecto_acc_espec});
//</ClavePrimaria>
//<ClavePrimaria>
this.op = new Ext.form.Hidden({
    name:'op',
    value:2});
//</ClavePrimaria>

this.id_proyecto = new Ext.form.TextField({
	fieldLabel:'Proyecto',
	name:'id_proyecto',
	value:this.OBJ.id_proyecto,
	readOnly:(this.OBJ.id_proyecto!='')?true:false,
	//style:(this.OBJ.id_proyecto!='')?'background:#c9c9c9;':'',
	allowBlank:false,
	width:300
});

this.nb_proyecto = new Ext.form.TextArea({
	fieldLabel:'Nombre',
	name:'nb_proyecto',
	value:this.OBJ.nb_proyecto,
	readOnly:(this.OBJ.nb_proyecto!='')?true:false,
	//style:(this.OBJ.nb_proyecto!='')?'background:#c9c9c9;':'',
	allowBlank:false,
	width:300
});

this.nb_archivo = new Ext.ux.form.FileUploadField({
	emptyText: 'Seleccione un Archivo',
	fieldLabel: 'Archivo',
	name:'archivo',
	buttonText: '',
	value:this.OBJ.nb_archivo,
	buttonCfg: {
		iconCls: 'icon-excel'
	},
	width:300,
	allowBlank:false,
});

this.Panel = new Ext.Panel ({
	baseCls : 'x-plain',
	html    : 'El Archivo de Excel debe contener n columnas en cuanto n acciones especificas se tengan (montos).',
	cls     : 'icon-autorizacion',
	region  : 'north',
	height  : 50
});

this.guardar = new Ext.Button({
    text:'Procesar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!cargarPartida.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe seleccionar un Archivo");
            return false;
        }
        cargarPartida.main.formPanel_.getForm().submit({
            method:'POST',
            /*url:'formulacion/modulos/accionEspecifica/orm.php',*/
						url:'proyecto/ae/partida/masivo',
            //url:'formulacion/modulos/accionEspecifica/funcion.php?op=3',
            waitMsg: 'Enviando datos, por favor espere..',
            waitTitle:'Enviando',
            failure: function(form, action) {
		var errores = '';
		for(datos in action.result.msg){
			errores += action.result.msg[datos] + '<br>';
		}
                Ext.MessageBox.alert('Error en transacción', errores);
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
                 cargarPartida.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        cargarPartida.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
    //frame:true,
    fileUpload: true,
	border:false,
    width:500,
autoHeight:true,
    autoScroll:true,
    bodyStyle:'padding:10px;',
    items:[
		this.co_proyecto_acc_espec,
		this.id_proyecto,
		this.nb_proyecto,
		this.nb_archivo,
		this.op,
		this.Panel
            ]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Archivo',
    modal:true,
    constrain:true,
width:514,
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
Ext.onReady(cargarPartida.main.init, cargarPartida.main);
</script>
