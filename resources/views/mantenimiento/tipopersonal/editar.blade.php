<script type="text/javascript">
Ext.ns("tipopersonalEditar");
tipopersonalEditar.main = {
init:function(){

//<Stores de fk>
this.storeCO_TIPO_PERSONAL = this.getStoreCO_TIPO_PERSONAL();
//<Stores de fk>

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.nu_codigo = new Ext.form.TextField({
	fieldLabel:'Codigo',
	name:'codigo',
	value:this.OBJ.nu_codigo,
	width:100,
	maxLength: 10,
	allowBlank:false,
	listeners:{
			change: function(){
					this.setValue(String(this.getValue()).toUpperCase());
			}
	}
});

this.de_tipo_personal = new Ext.form.TextField({
	fieldLabel:'Descripcion',
	name:'descripcion',
	value:this.OBJ.de_tipo_personal,
	width:400,
	maxLength: 600,
	allowBlank:false,
	listeners:{
			change: function(){
					this.setValue(String(this.getValue()).toUpperCase());
			}
	}
});

this.id_padre = new Ext.form.ComboBox({
	fieldLabel:'Padre',
	store: this.storeCO_TIPO_PERSONAL,
	typeAhead: true,
	valueField: 'id',
	displayField:'descripcion',
	hiddenName:'padre',
	//readOnly:(this.OBJ.id_padre!='')?true:false,
	//style:(this.main.OBJ.id_padre!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Padre...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{descripcion}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});

this.storeCO_TIPO_PERSONAL.load();

paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_padre,
	value:  this.OBJ.id_padre,
	objStore: this.storeCO_TIPO_PERSONAL
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!tipopersonalEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        tipopersonalEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/tipopersonal/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/tipopersonal/guardar') }}/{!! $data->id !!}',
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
                 tipopersonalLista.main.store_lista.load();
                 tipopersonalEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        tipopersonalEditar.main.winformPanel_.close();
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
		this.id_padre,
		this.nu_codigo,
		this.de_tipo_personal
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Tipo de Personal',
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
			@if( in_array( array( 'de_privilegio' => 'tipopersonal.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
tipopersonalLista.main.mascara.hide();
},
getStoreCO_TIPO_PERSONAL:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/personal/tipo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'nu_codigo'},{name: 'de_tipo_personal'},
						{name: 'descripcion',
								convert: function(v, r) {
										return r.nu_codigo + ' - ' + r.de_tipo_personal;
								}
						}
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
Ext.onReady(tipopersonalEditar.main.init, tipopersonalEditar.main);
</script>
