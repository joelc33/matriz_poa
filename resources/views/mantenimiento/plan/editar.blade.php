<script type="text/javascript">
Ext.ns("planzuliaEditar");
planzuliaEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.co_ambito_zulia = new Ext.form.NumberField({
	fieldLabel:'Ambito',
	name:'ambito',
	value:this.OBJ.co_ambito_zulia,
	width:100,
	maxLength: 10,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 10},
	//readOnly:(this.OBJ.tx_codigo!='')?true:false,
	//style:(this.OBJ.tx_codigo!='')?'background:#c9c9c9;':'',
});

this.co_objetivo_zulia = new Ext.form.NumberField({
	fieldLabel:'Objetivo',
	name:'objetivo',
	value:this.OBJ.co_objetivo_zulia,
	//allowBlank:false,
	width:100,
});

this.co_macroproblema = new Ext.form.NumberField({
	fieldLabel:'Macroproblema',
	name:'macroproblema',
	value:this.OBJ.co_macroproblema,
	width:100
});

this.co_nodo = new Ext.form.NumberField({
	fieldLabel:'Línea Matriz',
	name:'nodo',
	value:this.OBJ.co_nodo,
	width:100
});

this.nu_nivel = new Ext.form.NumberField({
	fieldLabel:'Nivel',
	name:'nivel',
	value:this.OBJ.nu_nivel,
	allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 1},
});

this.tx_descripcion = new Ext.form.TextArea({
	fieldLabel:'Descripcion',
	name:'descripcion',
	value:this.OBJ.tx_descripcion,
	width:400,
	maxLength: 600,
	height:100,
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!planzuliaEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        planzuliaEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/planzulia/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/planzulia/guardar') }}/{!! $data->id !!}',
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
                 planzuliaLista.main.store_lista.load();
                 planzuliaEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        planzuliaEditar.main.winformPanel_.close();
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
		this.co_ambito_zulia,
		this.co_objetivo_zulia,
		this.co_macroproblema,
		this.co_nodo,
		this.nu_nivel,
		this.tx_descripcion
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Planes de Desarrollo',
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
			@if( in_array( array( 'de_privilegio' => 'planes.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
planzuliaLista.main.mascara.hide();
}
};
Ext.onReady(planzuliaEditar.main.init, planzuliaEditar.main);
</script>
