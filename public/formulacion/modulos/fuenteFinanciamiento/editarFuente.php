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
	$sql = "SELECT * FROM mantenimiento.tab_fuente_financiamiento	where id=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_fuente"     => trim($row["id"]),
			"tx_fuente"     => trim($row["de_fuente_financiamiento"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_fuente"     => "",
		"tx_fuente"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("fuenteEditar");
fuenteEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_fuente = new Ext.form.Hidden({
    name:'co_fuente',
    value:this.OBJ.co_fuente});
//</ClavePrimaria>

this.tx_fuente = new Ext.form.TextField({
	fieldLabel:'Descripcion',
	name:'tx_fuente',
	value:this.OBJ.tx_fuente,
	width:400
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.tx_fuente
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!fuenteEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        fuenteEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/fuenteFinanciamiento/funcion.php?op=2',
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
                 fuenteFinanciamientoLista.main.store_lista.load();
                 fuenteEditar.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        fuenteEditar.main.winformPanel_.close();
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
		this.co_fuente,
		this.fielset1,
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Fuente de Financiamiento',
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
<?php if( in_array( array( 'de_privilegio' => 'ff.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
fuenteFinanciamientoLista.main.mascara.hide();
}
};
Ext.onReady(fuenteEditar.main.init, fuenteEditar.main);
</script>
