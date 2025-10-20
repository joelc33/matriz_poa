@extends('app')

@section('htmlheader_title')  Iniciar Sesion @endsection

@section('main-content')

<style type="text/css">
body {
background-color:white;
}
.x-window-mc {background-color : white !important;}
</style>

 <script>

        $(function () {
	    <?php
		$backgrounds = array("imagen_2.jpg","imagen_3.jpg","imagen_4.jpg","imagen_5.jpg","imagen_6.jpg","imagen_7.jpg");
//                $backgrounds = array("1.png","2.png","3.png","3.png");
		$random_keys=array_rand($backgrounds,4);
	    ?>
	    $.backstretch([
		"{{ asset('/images/backgrounds') }}/{{ $backgrounds[$random_keys[0]] }}",
		"{{ asset('/images/backgrounds') }}/{{ $backgrounds[$random_keys[1]] }}",
		"{{ asset('/images/backgrounds') }}/{{ $backgrounds[$random_keys[2]] }}",
		"{{ asset('/images/backgrounds') }}/{{ $backgrounds[$random_keys[3]] }}"
	    ], {duration: 3000, fade: 750});
        });
  </script>
<script type="text/javascript">  
Ext.QuickTips.init();
Ext.form.Field.prototype.msgTarget = 'side';

Ext.onReady(function(){

	this.captchaURL = "{{ URL::to('autenticar/captcha') }}?t=";

	function onCapthaChange(){
			var curr = Ext.get('codigoimagen');
			curr.slideOut('b', {callback: function(){
					Ext.get('codigoimagen').dom.src=this.captchaURL+new Date().getTime();
					curr.slideIn('t');
			}},this);
	};

	/*Ventana para validar*/
	function Validar(){
	if (this.validarForm.form.isValid()) {
		this.validarForm.form.submit({
			waitTitle: "Validando",
			waitMsg : "Espere un momento por favor......",
			failure: function(form,action){
			    try{
						if(action.result.msg!=null){
						    Ext.utiles.msg('Error de Validaci&oacute;n', action.result.msg);
						    onCapthaChange();
						}else{
						    throw Exception();
						}
			    }catch(Exception){
				    Ext.utiles.msg('Error durante el proceso','Consulta al administrador del Sistema');
			    }
			},
			success: function(form,action) {
					Ext.MessageBox.show({title: 'Iniciando sesi&oacute;n', msg: '<br>Por favor  Espere...',width:300,closable:false,icon:Ext.MessageBox.INFO});
			    location.href=action.result.url;
			}
		});
	}
	};

	this._token = new Ext.form.Hidden({
			name:'_token',
			value:'{{ csrf_token() }}'
	});

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
			name: 'contraseña',
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

	this.compositefieldCódigo = new Ext.form.CompositeField({
			fieldLabel: 'Código de Seguridad',
			items: [
				this.codigoseg,
				this.boxCaptcha
			]
	});

	this.Panel = new Ext.Panel ({
			baseCls : 'x-plain',
			html    : '<b>El acceso a este lugar está restringido a los usuarios no autorizados.<br>Por favor escriba su nombre de usuario y contraseña.</b>',
			cls     : 'icon-autorizacion',
			region  : 'north',
			height  : 40
	});

	this.validarForm = new Ext.form.FormPanel({
		baseCls: 'x-plain',
		labelWidth: 180,
		autoWidth:true,
		autoHeight:true,
		frame:true,
		autoScroll:false,
		bodyStyle:'padding:10px;',
		url:'{{ URL::to('autenticar') }}',
		items: [
			{
				xtype:'box',
				anchor:'',
				autoEl:{tag:'div', style:'margin:0px 0px 2px 130px', children:[{tag:'img', src:'images/zulia.png', height: 110, width: 110 }]}
			},
				this.Panel,
			{
				xtype:'fieldset',title:'Usuario / Contraseña', autoWidth:true, labelWidth: 90, height:170, frame:false, defaultType: 'textfield',
				items:[
					this._token,
					this.usuario,
					this.password
				],
				keys: [
					{
						key: [
							Ext.EventObject.ENTER
						],
						handler: function() {
							Validar();
						}
				  }
				]
		  }
		]
	});

  this.login = new Ext.Window({
							title:'POA - Validaci&oacute;n de Usuario',
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
									this.validarForm
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

  this.login.show();
});

function cambiar(){

	this._token_recuperar = new Ext.form.Hidden({
			name:'_token',
			value:'{{ csrf_token() }}'
	});

	this.usuario_recuperar = new Ext.form.TextField({
			fieldLabel:'Usuario',
			name: 'usuario',
			allowBlank:false,
			maxLength:250,
			width:200
	});

	this.correo = new Ext.form.TextField({
			fieldLabel:'Correo Electronico',
			name: 'correo',
			allowBlank:false,
			regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
			regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas.',
			maxLength:250,
			width:200
	});

	var formCorreo = new Ext.form.FormPanel({
		baseCls: 'x-plain',
		labelWidth: 120,
		autoWidth:true,
		autoHeight:true,
		frame:true,
		autoScroll:false,
		bodyStyle:'padding:10px;',
		url:'{{ URL::to('autenticar/recuperar') }}',
		items: [
			this._token_recuperar,
			this.usuario_recuperar,
			this.correo
		]
	});

	var ventanaRecuperar = new Ext.Window({
				title:'POA - Recuperar Contraseña',
				layout:'fit',
				iconCls: 'icon-bloqueado',
				width:400,
				autoHeight:true,
				modal:true,
				frame:true,
				autoScroll: true,
				maximizable:false,
				closable:false,
				draggable: false,
				resizable: false,
				constrain:true,
				plain: true,
				buttonAlign:'center',
				items:[
					formCorreo
				],
				buttons: [{
						text:'Enviar',
						align:'center',
						iconCls: 'icon-enviarcorreo',
						handler: function (){

							if(!formCorreo.getForm().isValid()){
									Ext.MessageBox.show({
											title: 'Alerta',
											msg: "Debe ingresar los campos en rojo",
											closable: false,
											icon: Ext.MessageBox.INFO,
											resizable: false,
											animEl: document.body,
											buttons: Ext.MessageBox.OK
									});
									return false;
							}

							formCorreo.form.submit({
								waitTitle: "Validando",
								waitMsg : "Espere un momento por favor......",
								failure: function(form,action){
										try{
											if(action.result.msg!=null){
													var errores = '';
													for(datos in action.result.msg){
														errores += action.result.msg[datos] + '<br>';
													}
													Ext.utiles.msg('Error de Validaci&oacute;n', errores);
											}else{
													throw Exception();
											}
										}catch(Exception){
											Ext.utiles.msg('Error durante el proceso','Consulta al administrador del Sistema');
										}
								},
								success: function(form,action) {
										ventanaRecuperar.hide();
										Ext.MessageBox.show({title: 'Envio de Correo', msg: '<br>Contraseña enviada con Exito!.',width:300,closable:true,icon:Ext.MessageBox.INFO});
								}
							});

						}
				},{
						align:'center',
						iconCls: 'icon-cancelar',
	          text : 'Cancelar',
	          handler : function(){
	              ventanaRecuperar.hide();
	          }
	       }]
	});

	ventanaRecuperar.show();
};

</script>

@endsection
