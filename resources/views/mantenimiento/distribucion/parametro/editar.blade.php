<script type="text/javascript">
Ext.ns("distribucionmunicipioEditar");
distribucionmunicipioEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.nu_total_poblacion = new Ext.form.NumberField({
	fieldLabel:'PROYECCION DE POBLACION',
	name:'poblacion',
	value:this.OBJ.nu_total_poblacion,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.cuatrocinco_ppi = new Ext.form.NumberField({
	fieldLabel:'45% PARTES IGUALES',
	name:'parte_igual',
	value:this.OBJ.cuatrocinco_ppi,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.cincocero_fpp = new Ext.form.NumberField({
	fieldLabel:'50% PARTES PROPORCIONALES',
	name:'parte_proporcional',
	value:this.OBJ.cincocero_fpp,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.nu_superficie = new Ext.form.NumberField({
	fieldLabel:'TOTAL FACTOR SUPERFICIE',
	name:'total_superficie',
	value:this.OBJ.nu_superficie,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.nu_extension_territorio = new Ext.form.NumberField({
	fieldLabel:'5% PROP. EXTENSION TERRITORIAL',
	name:'extension_territorio',
	value:this.OBJ.nu_extension_territorio,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!distribucionmunicipioEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        distribucionmunicipioEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/distribucionmunicipio/parametro/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/distribucionmunicipio/parametro/guardar') }}/{!! $data->id !!}',
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
                 distribucionmunicipioLista.main.store_lista.load();
                 distribucionmunicipioEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        distribucionmunicipioEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:500,
	labelWidth: 200,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
		this.nu_total_poblacion,
    this.cuatrocinco_ppi,
    this.cincocero_fpp,
    this.nu_superficie,
    this.nu_extension_territorio
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Distribucion Parametros',
    modal:true,
    constrain:true,
width:514,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'libro.distribucionmunicipio.parametro.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
distribucionmunicipioLista.main.mascara.hide();
}
};
Ext.onReady(distribucionmunicipioEditar.main.init, distribucionmunicipioEditar.main);
</script>
