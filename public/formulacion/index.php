<?php 
session_start();
$_SESSION['estatus']='Off';
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
                 ..::NUEVA ETAPA - ZULIA::..<br />
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
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando Login...';</script>
<script type="text/javascript" src="js/util.js"></script>
<script type="text/javascript">
Ext.QuickTips.init();
Ext.form.Field.prototype.msgTarget = 'side';
Ext.onReady(function(){
this.captchaURL = "plugins/captcha/CaptchaSecurityImages.php?t=";
function onCapthaChange(){
	var curr = Ext.get('codigoimagen');
	curr.slideOut('b', {callback: function(){
			Ext.get('codigoimagen').dom.src=this.captchaURL+new Date().getTime();
			curr.slideIn('t');
	}},this);
}

this.boxCaptcha = new Ext.BoxComponent({
	width:150,
	height:80,
	autoEl: {
			tag:'img',
			id:'codigoimagen',
			title:'Click para refrescar codigo',
			src:this.captchaURL+new Date().getTime()
		}
});

//Ventana para validar
function Validar(){
if (validarForm.form.isValid()) {
	validarForm.form.submit({
		waitTitle: "Validando",
		waitMsg : "Espere un momento por favor......",
		failure: function(form,action){
			var errores = '';
			for(datos in action.result.msg){
				errores += action.result.msg[datos] + '<br>';
			}
		        Ext.utiles.msg('Error de Validaci&oacute;n', errores);
		},
		success: function(form,action) {
			winValidar.hide();
			location.href=action.result.url;
		}
	});
}
}

this.usuario = new Ext.form.TextField({
	fieldLabel:'Usuario',
	name: 'usuario',
	id:'usuario',
	allowBlank:false,
	maxLength:250,
	width:235
});

this.password = new Ext.form.TextField({
	fieldLabel:'Contraseña',
	inputType:'password',
	name: 'password',
	id:'password',
	allowBlank:false,
	maxLength:60,
	width:235
});

this.codigoseg = new Ext.form.TextField({
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4 },
	fieldLabel:'Cod. Validacion',
	name: 'codigoseg',
	id:'codigoseg',
	allowBlank:false,
	maxLength:'4',
        width:80
});

this.compositefieldCodigo = new Ext.form.CompositeField({
fieldLabel: 'Codigo de Seguridad',
items: [
	this.codigoseg,
	this.boxCaptcha,
]
});

this.Panel = new Ext.Panel ({
	baseCls : 'x-plain',
	html    : 'El acceso a este lugar está restringido a los usuarios no autorizados.<br>Por favor escriba su nombre de usuario y contraseña.',
	cls     : 'icon-autorizacion',
	region  : 'north',
	height  : 70
});

var validarForm = new Ext.form.FormPanel({
	baseCls: 'x-plain',
	labelWidth: 180,
	autoWidth:true,
	autoHeight:true,
	frame:true,
	autoScroll:false,
	bodyStyle:'padding:10px;',
	url:'modulos/login/funcion.php/validar',
	items: [
		{
		xtype:'box',
		anchor:'',
		autoEl:{tag:'div', style:'margin:0px 0px 8px 80px', children:[{tag:'img',src:'images/logo.png'}]}
		},
		this.Panel,
		{
		xtype:'fieldset',title:'Usuario / Contraseña', autoWidth:true, labelWidth: 90, height:170, frame:false, defaultType: 'textfield',
		items:[
			this.usuario,
			this.password,
			this.compositefieldCodigo
		],
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
	title:'Nueva Etapa - Validaci&oacute;n de Usuario',
	layout:'fit',
	iconCls: 'icon-bloqueado',
	bodyStyle:'padding:5px;',
	width:415,
        height: 455,
	modal:true,
	autoScroll: true,
	maximizable:false,
	closable:false,
	draggable: false,
	resizable: false,
	plain: true,
	buttonAlign:'center',
	html: '<a class="blue" href="#" onclick="cambiar();">¿Olvido su contrase&ntilde;a?</a>',
	items:[
	    validarForm
	],
	buttons: [{
	    text:'Entrar',
	    align:'center',
	    iconCls: 'icon-login',
	    handler: function (){
		            Validar();
	    }
	}]
});

this.boxCaptcha.on('render',function (){
	var curr = Ext.get('codigoimagen');
	curr.on('click',onCapthaChange,this);
},this);

setTimeout(function(){
	usuario.focus(true,true);
	},500);
	winValidar.show();
});

function cambiar(){

this.PanelC = new Ext.Panel ({
	baseCls : 'x-plain',
	html    : '* Es necesario Saber el Numero de Documento Asociado a la Cuenta.<br>* Saber el Correo Electronico Asociado a la Cuenta.',
	cls     : 'icon-autorizacion',
	region  : 'north',
	height  : 70
});

this.nuDocumento = new Ext.form.TextField({
	fieldLabel:'Nº Documento',
	name: 'nuDocumento',
	id:'nuDocumento',
	allowBlank:false,
	maxLength:250,
	width:235,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.txCorreo = new Ext.form.TextField({
	fieldLabel:'Correo',
	name: 'txCorreo',
	id:'txCorreo',
	allowBlank:false,
	maxLength:250,
	width:235,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

var validarCorreo = new Ext.form.FormPanel({
	baseCls: 'x-plain',
	labelWidth: 180,
	autoWidth:true,
	autoHeight:true,
	frame:true,
	autoScroll:false,
	bodyStyle:'padding:10px;',
	url:'modulos/login/funcion.php',
	items: [
		{
		xtype:'box',
		anchor:'',
		autoEl:{tag:'div', style:'margin:0px 0px 8px 80px', children:[{tag:'img',src:'images/logo.png'}]}
		},
		this.PanelC,
		{
		xtype:'fieldset',title:'Datos de la Cuenta', autoWidth:true, labelWidth: 90, height:170, frame:false, defaultType: 'textfield',
		items:[
			this.nuDocumento,this.txCorreo
		]
	    }
	]
});

	winValidar = new Ext.Window({
		title:'Nueva Etapa - Validaci&oacute;n de Usuario - Envio de Contrase&ntilde;a',
		layout:'fit',
		iconCls: 'icon-bloqueado',
		bodyStyle:'padding:5px;',
		width:415,
		height: 455,
		modal:false,
		autoScroll: true,
		maximizable:false,
		closable:false,
		draggable: false,
		resizable: false,
		plain: true,
		buttonAlign:'center',
		items:[
			validarCorreo
		],
		buttons: [
			{
			text: 'Enviar Contrase&ntilde;a',
				handler : function(){
			if (validarCorreo.form.isValid()) {
				validarCorreo.form.submit({
				waitTitle: "Validando",
				waitMsg : "Espere un momento por favor......",
				failure: function(form,action){
				try{
					if(action.result.msg!=null)
						Ext.utiles.msg('Error de Validaci&oacute;n', action.result.msg);
					else
					throw Exception();
				}catch(Exception){
					Ext.utiles.msg('Error durante el proceso','Consulta al administrador del Sistema');
				}
				},
					success: function(form,action) {
					winValidar.close();
				}
				});
			}
				}
			},
			{
				text: 'Cancelar',
				handler : function(){
					winValidar.close();
				}
			}
		]
	});
	winValidar.show();
}
</script>
<input type="hidden" name="url_" id="url_" value="">

       <div id="winValidar">
          <div id="msgValidar" style="margin-bottom: 20px; font-size: 12px; font-weight: bold; color:white; display: none">
            Acceso para usuarios registrados
          </div>
           <div id="principal" align="center" style="padding-bottom: 1%">
                 <!--<img src="images/logo.jpg" width="150" style="position: absolute; top: 40%; left: 80px;" />-->
            </div>
       </div>
  </body>
 </html>
