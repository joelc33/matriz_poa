<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);

$sql = "SELECT * FROM proyecto_seguimiento.tab_proyecto WHERE nu_codigo='".$codigo."'";
$result = $comunes->ObtenerFilasBySqlSelect($sql);
foreach($result as $key => $row){
	$data = json_encode(array(
		"co_proyectos"     => trim($row["id"]),
		"id_proyecto"     => trim($row["nu_codigo"]),
		"id_ejercicio"     => trim($row["id_tab_ejercicio_fiscal"]),
		"id_ejecutor"     => trim($row["id_tab_ejecutores"]),
		"tipo_registro"     => trim($row["id_tab_tipo_registro"]),
		"nb_proyecto"     => trim($row["de_nombre"]),
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
this.op = new Ext.form.Hidden({
	name:'op',
	value:6
});

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
		this.Panel,
		this.op
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
