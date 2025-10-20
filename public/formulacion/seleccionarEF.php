<?php
session_start();
if($_SESSION['estatus']!='OK'){
	header('Location: .');
}
include("configuracion/ConexionComun.php");

if($_POST && !empty($_POST['ejercicio_fiscal']))
{
	$codigo = decode($_POST['id']);
	if($codigo!=''||$codigo!=null){
		$comunes = new ConexionComun();
		try{
			$paraTransaccion->BeginTrans();
			$tabla="mantenimiento.tab_ejecutores";
			$tquery="UPDATE";
			$id = 'id = '.$codigo;
			$variable["updated_at"] = date("Y-m-d H:i:s");
			$variable["de_correo"] = decode($_POST['de_correo']);
			$variable["de_telefono"] = decode($_POST['de_telefono']);
			$variable["in_verificado"] = 'TRUE';
			$co_ejecutores = $comunes->InsertUpdate($tabla,$variable,$tquery,$id);

			if ($co_ejecutores){
				$paraTransaccion->CommitTrans();

				$url="inicio.php";
				echo json_encode(array(
					    "success" => true,
					    "url" => $url,
					    "msg" => 'Opcion Validada'
				));
			}
			else{
				$paraTransaccion->RollbackTrans();
			}
		}catch(Exception $e){
			echo json_encode(array(
				    "success" => false,
				    "msg" => "Error en Transaccion."
			));
		}
	}else{
		$url="inicio.php";
		$_SESSION['ejercicio_fiscal']=$_POST['ejercicio_fiscal'];
		echo  "{success:true,msg:'Opcion Validada',url:'".$url."'}";
	}

}else{
	$comunes = new ConexionComun();
	$sql = "SELECT * FROM mantenimiento.tab_ejecutores where id_ejecutor='".$_SESSION['id_ejecutor']."'";
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"id"     => trim($row["id"]),
			"de_correo"     => trim($row["de_correo"]),
			"de_telefono"     => trim($row["de_telefono"]),
		));
	$in_verificado = trim($row["in_verificado"]);
	}

function  ArmaOpcion(){
	$comunes = new ConexionComun();
	$sql= "SELECT id FROM mantenimiento.tab_ejercicio_fiscal;";
	$resultado = $comunes->ObtenerFilasBySqlSelect($sql);
        $radio = '';
        foreach($resultado as $key => $fila){
			$radio.="{
				boxLabel: '".$fila["id"]."',
				name: 'ejercicio_fiscal',
				inputValue: '".$fila["id"]."'
			},";
                }
        return $radio;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
<title>..::NUEVA ETAPA | SPE::..</title>
<link rel="shortcut icon" href="images/favicon.ico" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ext/ext-all.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/iconos.css" />
</head>
  <body>
    <style type="text/css">
	body {
		background-color:white;
	}
        .x-window-mc {background-color : white !important;}
    </style>
	<div style="background-color:white; padding-left:0px; padding-right:0px; padding-bottom:0px;">
<!--	<img height="75" src="images/izquierda.png">
	<img height="75" align="right" src="images/derecha.png">-->
	</div>
	<div id="loading-mask" style=""></div>
  	<div id="loading">
		<div class="loading-indicator">
                <img src="images/32x32/blue-loading.gif" width="32" height="32" style="margin-right:2px; padding-left:20px; float:left;vertical-align:top;"/>
                 ..::Nueva Etapa::..<br />
                <span id="loading-msg">Cargando...</span>
            </div>
        </div>
   <!-- <img src="../images/banner.gif" align="bottom" width="100%" height="110"/>-->
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando el Componente Central ...';</script>
<script type="text/javascript" src="js/ext-3.4.1/adapter/ext/ext-base.js"></script>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando la Interfaz Grafica...';</script>
<script type="text/javascript" src="js/ext-3.4.1/ext-all.js"></script>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando el idioma...';</script>
<script type="text/javascript" src="js/ext-3.4.1/locale/ext-lang-es.js"></script>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando Esquema General...';</script>
<script type="text/javascript" src="js/funciones_comunes/paqueteComun.js"></script>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando Ejercicio Fiscal...';</script>
<script type="text/javascript" src="js/util.js"></script>
<script type="text/javascript">
Ext.QuickTips.init();
Ext.onReady(function(){

//Ventana para validar
function Validar(){
if (validarForm.form.isValid()) {
	validarForm.form.submit({
		waitTitle: "Verificando",
		waitMsg : "Espere un momento por favor......",
		failure: function(form,action){
		    try{
			if(action.result.msg!=null)
			    Ext.utiles.msg('Error de Selección', action.result.msg);
			else
			    throw Exception();
		    }catch(Exception){
			    Ext.utiles.msg('Error durante el proceso','Seleccione una Opcion');
		    }
		},
		success: function(form,action) {
		    winValidar.hide();
		    location.href=action.result.url;
		}
	});
}
}

<?php if($in_verificado=='f'){?>
this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.id = new Ext.form.Hidden({
    name:'id',
    value:this.OBJ.id});
//</ClavePrimaria>

this.de_correo = new Ext.form.TextField({
	fieldLabel:'Correo Institucional',
	name:'de_correo',
	value:this.OBJ.de_correo,
	width:300,
	allowBlank:false,
	emptyText: 'correo@diminio.com',
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail'
});

this.de_telefono = new Ext.form.TextField({
	fieldLabel:'Telefono',
	name:'de_telefono',
	value:this.OBJ.de_telefono,
	allowBlank:false,
	//emptyText: '0000-0000000',
	width:300,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos de contacto del Ejecutor',autoWidth:true,
		labelWidth: 130,
              items:[
		this.id,
		this.de_correo,
		this.de_telefono
]});
<?php }?>

var validarForm = new Ext.form.FormPanel({
	border: false,
	items: [
<?php if($in_verificado=='f'){?>
		this.fielset1,
<?php }?>
		{
		html: "<br><h1>Año en Ejercicio:</h1><br>",
		border: false},
		{xtype: 'fieldset', height: 120, autoScroll: true,
        		items: [{
							xtype: 'radiogroup',
							autoHeight: true,
							defaultType: 'radio',
							allowBlank: false,
							anchor: '95%',
							items: [
								{	columnWidth: '.25', items: [<?php echo ArmaOpcion(); ?>]}]
		}]},
		{html : "<p><br>Seleccione la opcion a realizar y presione Aceptar:</p>",border : false}
]
});

var winValidar;

winValidar = new Ext.Window({
	title:'Seleccione Ejercicio Fiscal',
	layout:'fit',
	bodyStyle:'padding:5px;',
	width:485,
        //autoHeight: true,
        height: 280,
	modal:true,
	autoScroll: true,
	maximizable:false,
	closable:false,
	draggable: false,
	resizable: false,
	plain: true,
	buttonAlign:'center',
	items:[
	    validarForm
	],
	buttons: [{
	    text:'Aceptar',
	    align:'center',
	    iconCls: 'icon-fin',
	    handler: function (){
		            Validar();
	    }
	}]
});

setTimeout(function(){
	},500);
	winValidar.show();
});
</script>
</script>
<input type="hidden" name="url_" id="url_" value="">

       <div id="winValidar">
          <div id="msgValidar" style="margin-bottom: 20px; font-size: 12px; font-weight: bold; color:white; display: none">
            Acceso para usuarios registrados
          </div>
           <div id="principal" align="center" style="padding-bottom: 1%">
                 <!--<img src="images/logo.jpg" width="150" style="position: absolute; top: 40%; left: 6px;" />-->
            </div>
       </div>
  </body>
 </html>
<?php
}
?>
