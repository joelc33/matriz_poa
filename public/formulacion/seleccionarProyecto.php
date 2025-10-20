<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: .');
}  
include("configuracion/ConexionComun.php");

if($_POST && !empty($_POST['co_login_accion'])) 
{
	echo json_encode(array(
		    "success" => false,
		    "msg" => "Seleccione una Opcion"
	));
}else{

function  ArmaOpcion($co_menu){
	$comunes = new ConexionComun();
	$sql= "SELECT co_login_accion, tx_login_accion FROM t05_login_accion;";
	$resultado = $comunes->ObtenerFilasBySqlSelect($sql);			
        $radio = '';
        foreach($resultado as $key => $fila){
			$radio.="{
				boxLabel: '".$fila["tx_login_accion"]."',
				name: 'co_login_accion',
				inputValue: '".$fila["co_login_accion"]."'
			},";
                }
        return $radio;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
<title>Secretaria de Planificacion y Estadistica</title>
<link rel="shortcut icon" href="images/favicon.ico" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ext/ext-all.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/iconos.css" />
</head>
  <body background="white" >
    <style type="text/css">
	body {
		background-color:white;
	}
        .x-window-mc {background-color : white !important;}
    </style>
	<div style="background-color:white; padding-left:0px; padding-right:0px; padding-bottom:0px;">
	<img height="75" src="images/cintillo_planificacion.png"> 
	<!--<img align="right" src="images/logo.png">-->
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
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando Proyectos...';</script> 
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

var validarForm = new Ext.form.FormPanel({
	baseCls: 'x-plain',
	labelWidth: 180,
	autoWidth:true,
	autoHeight:true,
	frame:true,
	autoScroll:false,
	bodyStyle:'padding:10px;',
	url:'',
	items: [
		{
		xtype:'fieldset',title:'Nombre del Proyecto', autoWidth:true, labelWidth: 90, autoScroll:true, height: 300, frame:false, 		defaultType: 'radio', items: [<?php echo ArmaOpcion(); ?>],
		keys: [
			{key: [Ext.EventObject.ENTER], handler: function() {
				Validar();
			}
		   }
		]
	    }
	]
});

var winValidar;

winValidar = new Ext.Window({
	title:'Seleccione un Proyecto',
	layout:'fit',
	bodyStyle:'padding:5px;',
	width:485,
        autoHeight: true,
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
                 <img src="images/logo.jpg" width="150" style="position: absolute; top: 40%; left: 6px;" />
            </div>         
       </div>   
  </body>
 </html>
<?php
}
?>
