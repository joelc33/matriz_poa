<script type="text/javascript">
Ext.ns("sectorEditar");
sectorEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.co_sector = new Ext.form.TextField({
	fieldLabel:'Sector',
	name:'sector',
	value:this.OBJ.co_sector,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2},
	//readOnly:(this.OBJ.co_sector!='')?true:false,
	//style:(this.OBJ.co_sector!='')?'background:#c9c9c9;':'',
});

this.co_sub_sector = new Ext.form.TextField({
	fieldLabel:'Sub-Sector',
	name:'sub_sector',
	value:this.OBJ.co_sub_sector,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2},
	//readOnly:(this.OBJ.co_sub_sector!='')?true:false,
	//style:(this.OBJ.co_sub_sector!='')?'background:#c9c9c9;':'',
});

this.nu_nivel = new Ext.form.NumberField({
	fieldLabel:'Nivel',
	name:'nivel',
	value:this.OBJ.nu_nivel,
	//allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 1},
});

this.tx_descripcion = new Ext.form.TextField({
	fieldLabel:'Descripcion',
	name:'descripcion',
	value:this.OBJ.tx_descripcion,
	width:400,
	maxLength: 400,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 400},
	//readOnly:(this.OBJ.tx_descripcion!='')?true:false,
	//style:(this.OBJ.tx_descripcion!='')?'background:#c9c9c9;':'',
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!sectorEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        sectorEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/sector/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/sector/guardar') }}/{!! $data->id !!}',
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
                 sectorLista.main.store_lista.load();
                 sectorEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        sectorEditar.main.winformPanel_.close();
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
		this.co_sector,
		this.co_sub_sector,
		this.nu_nivel,
		this.tx_descripcion
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Sectores',
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
			@if( in_array( array( 'de_privilegio' => 'sectores.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
sectorLista.main.mascara.hide();
}
};
Ext.onReady(sectorEditar.main.init, sectorEditar.main);
</script>
