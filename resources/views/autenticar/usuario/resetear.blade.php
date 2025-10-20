<script type="text/javascript">
Ext.ns("cambioContrasena");
cambioContrasena.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.id = new Ext.form.Hidden({
	name:'codigo',
	value:this.OBJ.id
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

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!cambioContrasena.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        cambioContrasena.main.formPanel_.getForm().submit({
        		method:'POST',
        		url:'{{ URL::to('usuario/cambiar/clave') }}',
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
                 usuarioLista.main.store_lista.load();
                 cambioContrasena.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        cambioContrasena.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:340,
	labelWidth: 120,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
		this.id,
    this.clave,
    this.confirmacion
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Cambio de Clave del usuario {{ $data->da_login }}',
    modal:true,
    constrain:true,
    width:354,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'usuarios.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
usuarioLista.main.mascara.hide();
}
};
Ext.onReady(cambioContrasena.main.init, cambioContrasena.main);
</script>
