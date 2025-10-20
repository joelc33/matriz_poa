<script type="text/javascript">
Ext.ns("desagregadoEditar");
desagregadoEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.ae = new Ext.form.Hidden({
	name:'ae',
	value:this.OBJ.co_proyecto_acc_espec
});

this.codigo = new Ext.form.TextField({
	fieldLabel:'Codigo',
	name:'codigo',
	value:this.OBJ.tx_codigo,
  readOnly:true,
	width:100,
	allowBlank:false
});

this.descripcion = new Ext.form.TextArea({
	fieldLabel:'Descripcion',
	name:'descripcion',
	value:this.OBJ.descripcion,
  readOnly:true,
	width:400,
	allowBlank:false
});

this.nb_archivo = new Ext.ux.form.FileUploadField({
	emptyText: 'Seleccione un Archivo',
	fieldLabel: 'Archivo',
	name:'archivo',
	buttonText: '',
	value:this.OBJ.nb_archivo,
	buttonCfg: {
		iconCls: 'icon-excel'
	},
	width:400,
	allowBlank:false,
});

this.Panel = new Ext.Panel ({
	baseCls : 'x-plain',
	html    : 'El Archivo de Excel debe contener n columnas en cuanto n acciones especificas se tengan (montos).',
	cls     : 'icon-autorizacion',
	region  : 'north',
	height  : 50
});

this.guardar = new Ext.Button({
    text:'Cargar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!desagregadoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        desagregadoEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('proyecto/ae/partida/cargar') }}',
	@else
		url:'{{ URL::to('proyecto/ae/partida/cargar') }}',
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
                 partidaLista.main.store_lista.load();
                 desagregadoEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        desagregadoEditar.main.winformPanel_.close();
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
  fileUpload: true,
	items:[
		this._token,
		this.ae,
		this.codigo,
		this.descripcion,
    this.nb_archivo,
    this.Panel
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Cargar Partidas',
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
			@if( in_array( array( 'de_privilegio' => 'aplicacion.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
partidaLista.main.mascara.hide();
}
};
Ext.onReady(desagregadoEditar.main.init, desagregadoEditar.main);
</script>
