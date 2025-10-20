<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT * FROM t45_planes_zulia WHERE co_planes_zulia=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_planes_zulia"     => trim($row["co_planes_zulia"]),
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
		"co_planes_zulia"     => "",
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
Ext.ns("planEditar");
planEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_planes_zulia = new Ext.form.Hidden({
    name:'co_planes_zulia',
    value:this.OBJ.co_planes_zulia});
//</ClavePrimaria>

this.tx_descripcion = new Ext.form.TextArea({
	fieldLabel:'Descripcion',
	name:'tx_descripcion',
	value:this.OBJ.tx_descripcion,
	width:400,
	height:150,
	maxLength: 600
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.tx_descripcion
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!planEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        planEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/planDesarrollo/funcion.php?op=2',
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
                 planLista.main.store_lista.load();
                 planEditar.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        planEditar.main.winformPanel_.close();
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
		this.co_planes_zulia,
		this.fielset1,
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Plan de Desarrollo',
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
planLista.main.mascara.hide();
}
};
Ext.onReady(planEditar.main.init, planEditar.main);
</script>
