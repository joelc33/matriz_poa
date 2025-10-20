<script type="text/javascript">
Ext.ns("tipoaccionEditar");
tipoaccionEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.nu_original = new Ext.form.TextField({
	fieldLabel:'Numero',
	name:'numero',
	value:this.OBJ.nu_original,
	allowBlank:false,
	width:400
});

this.de_nombre = new Ext.form.TextField({
	fieldLabel:'Nombre',
	name:'nombre',
	value:this.OBJ.de_nombre,
	allowBlank:false,
	width:400
});

this.de_accion = new Ext.form.TextArea({
	fieldLabel:'Descripcion',
	name:'descripcion',
	value:this.OBJ.de_accion,
	allowBlank:false,
	width:400,
	height: 150,
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!tipoaccionEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        tipoaccionEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/tipoaccion/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/tipoaccion/guardar') }}/{!! $data->id !!}',
	@endif
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
                 tipoaccionLista.main.store_lista.load();
                 tipoaccionEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        tipoaccionEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	labelWidth: 80,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
		this.nu_original,
		this.de_nombre,
		this.de_accion
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Tipo de Accion',
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
			@if( in_array( array( 'de_privilegio' => 'tipoac.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
tipoaccionLista.main.mascara.hide();
}
};
Ext.onReady(tipoaccionEditar.main.init, tipoaccionEditar.main);
</script>
