<script type="text/Javascript">
Ext.ns('cambioClave');
cambioClave.formulario = {
init: function(){

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.clave_actual = new Ext.form.TextField({
	fieldLabel : 'Contraseña Actual',
	inputType:'password',
	id : 'contraseña_actual',
	name : 'contraseña_actual',
	width: '150px',
	allowBlank:false
});

this.clave = new Ext.form.TextField({
	fieldLabel : 'Contraseña',
	inputType:'password',
	id : 'contraseña',
	name : 'contraseña',
	width: '150px',
	allowBlank:false
});

this.confirmacion = new Ext.form.TextField({
	fieldLabel : 'Confirmación',
	inputType:'password',
	id : 'contraseña_confirmation',
	name : 'contraseña_confirmation',
	width: '150px',
	allowBlank:false
});

var form = new Ext.FormPanel({
	url: '{{ URL::to('usuario/cambioContrasena') }}',
	width: 340,
	height:100,
	padding:'10px',
	labelWidth: 120,
	frame:false,
	border:false,
	items:[this.clave_actual, this.clave, this.confirmacion, this._token],
});

           this.win =new Ext.Window({
              title: 'Cambio de Clave del usuario: {{ Auth::user()->da_login }}',
              constrain:true,
              width: 347,
              modal:true,
	      resizable: false,
              items:[form],
              buttonAlign:'center',
              buttons: [
								@if( in_array( array( 'de_privilegio' => 'cambiarclave.guardar', 'in_habilitado' => true), Session::get('credencial') ))
                     {text: 'Guardar',iconCls: 'icon-login',
                        handler: function(){
                             if (form.form.isValid() ) {
                                  Ext.MessageBox.show({
                                       msg: 'Guardando Registro, por favor espere...',
                                       progressText: 'Guardando...',
                                       width:300,
                                       wait:true,
                                       waitConfig: {interval:200}
                                   });
                                    form.form.submit({

                                    failure: function(form, action) {
					var errores = '';
					for(datos in action.result.msg){
						errores += action.result.msg[datos] + '<br>';
					}
					Ext.MessageBox.alert('Error en transacción', errores);
                                     },
                                     success: function(form, action) {
                                         Ext.MessageBox.show({
                                             title: 'Mensaje',
                                             msg: action.result.msg,
                                             closable: false,
                                             resizable: false,
					     animEl: document.body,
                                             buttons: Ext.MessageBox.OK
                                         });
					cambioClave.formulario.win.close();
					this.panelCambio = Ext.getCmp('tabpanel');
					this.panelCambio.remove('4');
                                     }
                                 });
                             } else {
                                 Ext.Msg.show({
                                    title:'Mensaje',
                                     msg: 'Debe llenar los campos requeridos',
                                     buttons: Ext.Msg.OK,
                                     animEl: document.body,
                                     icon: Ext.MessageBox.INFO
                                 });
                             }
                         }
                 }
								 @endif
		],
		listeners:{
			'close':function(win){
					this.panelCambio = Ext.getCmp('tabpanel');
					this.panelCambio.remove('4');
			}
		}
           }).show();
        }
}
Ext.onReady(cambioClave.formulario.init,cambioClave.formulario);
</script>
