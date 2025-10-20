
function Validar(){
  if (validarForm.form.isValid()) {
      validarForm.form.submit({
          waitTitle: "Validando",
          waitMsg : "Espere un momento por favor......",
          failure: function(sender,action){
          	try{
            	if(action.result.msg!=null)
	            	Ext.utiles.msg('Error de Validaci&oacute;n', action.result.msg);
	            else	
					throw Exception();
          	}catch(Exception){
          		Ext.utiles.msg('Error durante el proceso','Usuario y/o contraseña invalida');
          	}
          },
          success: function(sender,action) {	 
	        winValidar.hide();
                location.href='elector';
           }
      });
   }else{
	Ext.Msg.alert("Alerta!","Verifique usuario y clave");
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
      url:'login/validar',

      items: [
		{xtype:'fieldset',title:'Usuario / Password', autoWidth:true, labelWidth: 90, autoHeight:true, defaultType: 'textfield', 
			items:[
				{fieldLabel:'Usuario', name: 'usuario', allowBlank:false, maxLength:250},
				{fieldLabel:'Password', inputType:'password', allowBlank:false, maxLength:20, name: 'password'}
				
			]
		}]
  });
  
  var winValidar; 
  if(winValidar==null){
    // create the window on the first click and reuse on subsequent clicks
    winValidar = new Ext.Window({
    		el:'winValidar',
    		title:'Validaci&oacute;n de Usuario',
            layout:'fit',
            bodyStyle:'padding:5px;',
            width:450,
            height:200,
            
            modal:true,
            autoScroll: true,
            maximizable:false,
            closable:false,
            plain: true,
            buttonAlign:'center',
            items:[
            validarForm
            ],
            buttons: [{
                text:'Entrar',
                align:'center',
                handler: function (){
				Validar();
          	}
            }]
        });
}
