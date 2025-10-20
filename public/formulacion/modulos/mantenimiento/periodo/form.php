<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../../model/tab_apertura_ef.php');

$codigo = $_POST['codigo'];
if($codigo!=''||$codigo!=null){
	$data = tab_apertura_ef::select('id', 'id_tab_ejercicio_fiscal', 'fe_desde', 'fe_hasta', 'de_apertura')
	->where('id', '=', $_POST['codigo'])
	->first();
}else{
	$data = json_encode(array("id_tab_ejercicio_fiscal" => $_POST['ef']));
}

$fechaI = '01-01-'.((empty($_POST['ef'])? $data->id_tab_ejercicio_fiscal : $_POST['ef'] )-1);
$fechaF = '31-12-'.((empty($_POST['ef'])? $data->id_tab_ejercicio_fiscal : $_POST['ef'] )-1);

?>
<script type="text/javascript">
Ext.ns("periodoEFEditar");
periodoEFEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.id_tab_ejercicio_fiscal = new Ext.form.Hidden({
    name:'id_tab_ejercicio_fiscal',
    value:this.OBJ.id_tab_ejercicio_fiscal});
//</ClavePrimaria>

this.fe_desde = new Ext.form.DateField({
	fieldLabel:'Fecha Apertura',
	name:'fecha_apertura',
	value:this.OBJ.fe_desde,
	minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',
	allowBlank:false,
	width:100
});

this.fe_hasta = new Ext.form.DateField({
	fieldLabel:'Fecha Cierre',
	name:'fecha_cierre',
	value:this.OBJ.fe_hasta,
	minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',
	allowBlank:false,
	width:100
});

this.de_apertura = new Ext.form.TextField({
	fieldLabel:'Descipcion',
	name:'descripcion',
	value:this.OBJ.de_apertura,
	allowBlank:false,
	width:400
});

this.fieldset1 = new Ext.form.FieldSet({
	title: 'Datos del Periodo',
	autoWidth:true,
        items:[
		this.fe_desde,
		this.fe_hasta,
		this.de_apertura
		]
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!periodoEFEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        periodoEFEditar.main.formPanel_.getForm().submit({
		method:'POST',
	<?php if(empty($data->id)){ ?>
		url:'formulacion/modulos/mantenimiento/periodo/orm.php/guardar',
	<?php }else{ ?>
		url:'formulacion/modulos/mantenimiento/periodo/orm.php/guardar/<?php echo $data->id ?>',
	<?php } ?>
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
                 periodoEFLista.main.store_lista.load();
                 periodoEFEditar.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        periodoEFEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	labelWidth: 120,
	border:false,
	autoHeight:true,  
	autoScramol:true,
	bodyStyle:'padding:10px;',
	items:[
		this.id_tab_ejercicio_fiscal,
		this.fieldset1
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Periodo',
    modal:true,
    constrain:true,
width:614,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
<?php if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.cronograma.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
periodoEFLista.main.mascara.hide();
}
};
Ext.onReady(periodoEFEditar.main.init, periodoEFEditar.main);
</script>
