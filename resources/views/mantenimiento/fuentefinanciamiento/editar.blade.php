<script type="text/javascript">
Ext.ns("fuentefinanciamientoEditar");
fuentefinanciamientoEditar.main = {
init:function(){

//<Stores de fk>
this.storeCO_FONDO = this.getStoreCO_FONDO();
//<Stores de fk>

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.id_tab_tipo_fondo = new Ext.form.ComboBox({
	fieldLabel:'Tipo de Fondo',
	store: this.storeCO_FONDO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_tipo_fondo',
	hiddenName:'fondo',
	//readOnly:(this.OBJ.id_tab_tipo_fondo!='')?true:false,
	//style:(this.main.OBJ.id_tab_tipo_fondo!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Fondo...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_tipo_fondo}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});
this.storeCO_FONDO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_tipo_fondo,
	value:  this.OBJ.id_tab_tipo_fondo,
	objStore: this.storeCO_FONDO
});

this.de_fuente_financiamiento = new Ext.form.TextField({
	fieldLabel:'Descripcion',
	name:'fuente',
	value:this.OBJ.de_fuente_financiamiento,
	width:400,
	maxLength: 600,
	allowBlank:false
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!fuentefinanciamientoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        fuentefinanciamientoEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/fuentefinanciamiento/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/fuentefinanciamiento/guardar') }}/{!! $data->id !!}',
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
                 fuentefinanciamientoLista.main.store_lista.load();
                 fuentefinanciamientoEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        fuentefinanciamientoEditar.main.winformPanel_.close();
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
		this.id_tab_tipo_fondo,
		this.de_fuente_financiamiento
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Fuente de Financiamiento',
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
			@if( in_array( array( 'de_privilegio' => 'ff.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
fuentefinanciamientoLista.main.mascara.hide();
},
getStoreCO_FONDO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/fondo/tipo') }}',
        root:'data',
        fields:[
            {name: 'id'},
						{name: 'de_tipo_fondo'},
            ],
            listeners : {
                exception : function(proxy, response, operation) {
                    Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                }
            }
    });
    return this.store;
}
};
Ext.onReady(fuentefinanciamientoEditar.main.init, fuentefinanciamientoEditar.main);
</script>
