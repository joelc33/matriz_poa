<script type="text/javascript">
Ext.ns("forma003Editar");
forma003Editar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});
//<Stores de fk>

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>
this.observaciones = new Ext.form.TextArea({
	fieldLabel: 'OBSERVACIONES',
	name: 'observaciones',
	value:this.OBJ.de_observacion_003,
	allowBlank: false,
	width:400,
	height: 100,
	maxLength: 6000
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!forma003Editar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        forma003Editar.main.formPanel_.getForm().submit({
		method:'POST',
		url:'{{ URL::to('ac/seguimiento/003/guardar') }}/{!! $data->id !!}',
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
                 forma003Editar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        forma003Editar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	labelWidth: 120,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
                this.observaciones
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Accion Centralizada: Observaciones',
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
        this.guardar,
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
forma003Lista.main.mascara.hide();
}
};
Ext.onReady(forma003Editar.main.init, forma003Editar.main);
</script>
