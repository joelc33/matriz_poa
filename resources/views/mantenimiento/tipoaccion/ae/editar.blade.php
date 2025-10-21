<script type="text/javascript">
Ext.ns("tipoaccionaeEditar");
tipoaccionaeEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>
this.ac = new Ext.form.Hidden({
	name:'ac',
	value:this.OBJ.ac
});

this.nu_numero = new Ext.form.NumberField({
	fieldLabel:'Codigo',
	name:'numero',
	value:this.OBJ.nu_numero,
	allowBlank:false,
	minLength : 1,
	maxLength: 12,
	allowDecimals: false,
	decimalPrecision: 0,
	allowNegative: false,
	// readOnly:true,
	// style:'background:#c9c9c9;',
	// msgTarget: 'under',
	width:100
});

this.de_nombre = new Ext.form.TextField({
	fieldLabel:'Nombre',
	name:'nombre',
	value:this.OBJ.de_nombre,
	allowBlank:false,
	width:400
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!tipoaccionaeEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        tipoaccionaeEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/tipoaccion/ae/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/tipoaccion/ae/guardar') }}/{!! $data->id !!}',
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
                 tipoaccionaeLista.main.store_lista.load();
                 tipoaccionaeEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        tipoaccionaeEditar.main.winformPanel_.close();
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
		this.ac,
		this.nu_numero,
		this.de_nombre
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Proyectos',
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
			@if( in_array( array( 'de_privilegio' => 'tipoac.ae.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
tipoaccionaeLista.main.mascara.hide();
}
};
Ext.onReady(tipoaccionaeEditar.main.init, tipoaccionaeEditar.main);
</script>
