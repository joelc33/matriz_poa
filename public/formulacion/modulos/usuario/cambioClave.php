<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}
?>
<script type="text/Javascript">

    Ext.ns('cambioClave');

    cambioClave.formulario = {

        init: function(){

            this.clave = new Ext.form.TextField({
                fieldLabel : 'Contraseña',
                inputType:'password',
                id : 'clave',
                name : 'clave',
                width: '150px',
                allowBlank:false

           });

           this.confirmacion = new Ext.form.TextField({
                fieldLabel : 'Confirmación',
                inputType:'password',
                id : 'confirmacion',
                name : 'confirmacion',
                width: '150px',
                allowBlank:false

           });

           var form = new Ext.FormPanel({
               url: 'formulacion/modulos/usuario/funcion.php?op=1',
               width: 320,
               height:100,
               padding:'10px',
	       frame:false,
		border:false,
               items:[this.clave,this.confirmacion],             

           });

           this.win =new Ext.Window({
              title: 'Cambio de Clave del Usuario: <?php echo ($_SESSION[nb_usuario]); ?>',
              constrain:true,
              width: 320,
              modal:true,
              items:[form],
              buttonAlign:'center',
              buttons: [
<?php if( in_array( array( 'de_privilegio' => 'cambiarclave.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
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
                                        // this.error_code = action.result.error_code;
                                        // if(error_code == 1) {
                                             Ext.MessageBox.alert('Mensaje', action.result.message);
                                        // }
                                     },
                                     success: function(form, action) {
                                         Ext.MessageBox.show({
                                             title: 'Mensaje',
                                             msg: action.result.message,
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
<?php } ?>
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
