<script type="text/javascript">
Ext.ns("fondoEditar");
fondoEditar.main = {
init:function(){

//<Stores de fk>
this.storeCO_RECURSO = this.getStoreCO_RECURSO();
//<Stores de fk>

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.id_tab_tipo_recurso = new Ext.form.ComboBox({
	fieldLabel:'Tipo de Recurso',
	store: this.storeCO_RECURSO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_tipo_recurso',
	hiddenName:'recurso',
	//readOnly:(this.OBJ.id_tab_tipo_recurso!='')?true:false,
	//style:(this.main.OBJ.id_tab_tipo_recurso!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Recurso...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_tipo_recurso}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});
this.storeCO_RECURSO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_tipo_recurso,
	value:  this.OBJ.id_tab_tipo_recurso,
	objStore: this.storeCO_RECURSO
});

this.de_tipo_fondo = new Ext.form.TextField({
	fieldLabel:'Descripcion',
	name:'fondo',
	value:this.OBJ.de_tipo_fondo,
	width:400,
	maxLength: 600,
	allowBlank:false
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!fondoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        fondoEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/fondo/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/fondo/guardar') }}/{!! $data->id !!}',
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
                 fondoLista.main.store_lista.load();
                 fondoEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        fondoEditar.main.winformPanel_.close();
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
		this.id_tab_tipo_recurso,
		this.de_tipo_fondo
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Tipo de Fondo',
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
			@if( in_array( array( 'de_privilegio' => 'fondo.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
fondoLista.main.mascara.hide();
},
getStoreCO_RECURSO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/recurso/tipo') }}',
        root:'data',
        fields:[
            {name: 'id'},
						{name: 'de_tipo_recurso'},
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
Ext.onReady(fondoEditar.main.init, fondoEditar.main);
</script>
