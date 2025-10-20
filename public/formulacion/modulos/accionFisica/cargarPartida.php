<?php
session_start();
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);

$sql = "SELECT * FROM t39_proyecto_acc_espec where co_proyecto_acc_espec=".$codigo;
$result = $comunes->ObtenerFilasBySqlSelect($sql);
foreach($result as $key => $row){
	$data = json_encode(array(
			"co_proyecto_acc_espec"     => trim($row["co_proyecto_acc_espec"]),
			"id_proyecto"     => trim($row["id_proyecto"]),
			"nb_accion"     => trim($row["descripcion"]),
			"co_unidades_medida"     => trim($row["co_unidades_medida"]),
			"nu_meta"     => trim($row["meta"]),
			"tx_codigo"     => trim($row["tx_codigo"]),
			"nu_ponderacion"     => trim($row["ponderacion"]),
			"op_bien_servicio"     => trim($row["bien_servicio"]),
			"mo_total_general"     => trim($row["total"]),
			"fecha_inicio"     => trim($row["fec_inicio"]),
			"fecha_culminacion"     => trim($row["fec_termino"]),
			"co_ejecutores"     => trim($row["co_ejecutores"]),
			"tx_objetivo_institucional"     => trim($row["tx_objetivo_institucional"]),
			"id_padre"     => trim($row["id_padre"]),
			"in_definitivo"     => trim($row["in_definitivo"]),
	));
}
if($result[0]['in_definitivo']==true){
?>

<script type="text/javascript">
Ext.ns("cargarPartidaActividad");
cargarPartidaActividad.main = {
init:function(){

this.Panel = new Ext.Panel ({
	baseCls : 'x-plain',
	html    : 'Las Actividades de esta accion especifica se encuentran cerradas, por lo tanto no se puede procesar un nuevo archivo Excel.',
	cls     : 'icon-autorizacion',
	region  : 'north',
	height  : 50
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        cargarPartidaActividad.main.winformPanel_.close();
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
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
accionEspLista.main.mascara.hide();
}
};
Ext.onReady(cargarPartidaActividad.main.init, cargarPartidaActividad.main);
</script>

<?php
}else{
?>
<script type="text/javascript">
Ext.ns("cargarPartidaActividad");
cargarPartidaActividad.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_proyecto_acc_espec = new Ext.form.Hidden({
    name:'co_proyecto_acc_espec',
    value:this.OBJ.co_proyecto_acc_espec});
//</ClavePrimaria>

this.id_proyecto = new Ext.form.TextField({
	fieldLabel:'Proyecto',
	name:'id_proyecto',
	value:this.OBJ.id_proyecto,
	readOnly:(this.OBJ.id_proyecto!='')?true:false,
	style:(this.OBJ.id_proyecto!='')?'background:#c9c9c9;':'',
	allowBlank:false,
	width:300
});

this.tx_codigo = new Ext.form.TextField({
	fieldLabel:'Codigo de la Accion',
	name:'tx_codigo',
	value:this.OBJ.tx_codigo,
	readOnly:(this.OBJ.tx_codigo!='')?true:false,
	style:(this.OBJ.tx_codigo!='')?'background:#c9c9c9;':'',
	allowBlank:false,
	width:300
});

this.nb_accion = new Ext.form.TextArea({
	fieldLabel:'Nombre de la Accion',
	name:'nb_accion',
	value:this.OBJ.nb_accion,
	readOnly:(this.OBJ.nb_accion!='')?true:false,
	style:(this.OBJ.nb_accion!='')?'background:#c9c9c9;':'',
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
	html    : 'El Archivo de Excel debe contener en la columna de la accion especifica se tengan (montos).',
	cls     : 'icon-autorizacion',
	region  : 'north',
	height  : 50
});

this.guardar = new Ext.Button({
    text:'Procesar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!cargarPartidaActividad.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe seleccionar un Archivo");
            return false;
        }
        cargarPartidaActividad.main.formPanel_.getForm().submit({
            method:'POST',
            //url:'formulacion/modulos/accionFisica/funcion.php?op=8',
            /*url:'formulacion/modulos/accionFisica/orm.php/partida/proyecto/ae',*/
						url:'proyecto/ae/partida/individual',
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
                 accionEspLista.main.store_lista.load();
                 cargarPartidaActividad.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        cargarPartidaActividad.main.winformPanel_.close();
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
		this.tx_codigo,
		this.nb_accion,
		this.nb_archivo,
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
accionEspLista.main.mascara.hide();
}
};
Ext.onReady(cargarPartidaActividad.main.init, cargarPartidaActividad.main);
</script>
<?php
}
?>
