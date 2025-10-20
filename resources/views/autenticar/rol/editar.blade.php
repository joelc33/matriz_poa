<script type="text/javascript">
Ext.ns("rolEditar");
rolEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});
//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>
//<ClavePrimaria>
this.co_rol = new Ext.form.Hidden({
    name:'co_rol',
    value:this.OBJ.co_rol});
//</ClavePrimaria>


this.tx_rol = new Ext.form.TextField({
	fieldLabel:'Nombre del Rol',
	name:'nombre',
	value:this.OBJ.tx_rol,
	allowBlank:false,
	width:200
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!rolEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        rolEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'{{ URL::to('rol/guardar') }}',
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
                 rolLista.main.store_lista.load();
                 rolEditar.main.winformPanel_.hide();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        rolEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
    //frame:true,
    width:400,border:false,
autoHeight:true,
    autoScroll:true,
    bodyStyle:'padding:10px;',
    items:[
		this._token,
		this.co_rol,
		this.tx_rol,
            ]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Rol',
    modal:true,
    constrain:true,
width:400,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'privilegios.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
rolLista.main.mascara.hide();
}
};
Ext.onReady(rolEditar.main.init, rolEditar.main);
</script>
