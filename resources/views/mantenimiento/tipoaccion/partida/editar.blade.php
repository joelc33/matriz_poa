<script type="text/javascript">
Ext.ns("tipoaccionpartidaEditar");
tipoaccionpartidaEditar.main = {
init:function(){

//<Stores de fk>
this.storeCO_AE = this.getStoreCO_AE();
//<Stores de fk>

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>
this.ac = new Ext.form.Hidden({
	name:'ac',
	value:this.OBJ.id_tab_ac_predefinida
});

this.id_tab_ac_ae_predefinida = new Ext.form.ComboBox({
	fieldLabel:'Accion Especifica',
	store: this.storeCO_AE,
	typeAhead: true,
	valueField: 'id',
	displayField:'descripcion',
	hiddenName:'ae',
	//readOnly:(this.OBJ.id_tab_ac_ae_predefinida!='')?true:false,
	//style:(this.main.OBJ.id_tab_ac_ae_predefinida!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Accion Especifica...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{descripcion}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});
this.storeCO_AE.load({
		params: {id_accion:this.OBJ.id_tab_ac_predefinida, _token: '{{ csrf_token() }}'}
});
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_ac_ae_predefinida,
	value:  this.OBJ.id_tab_ac_ae_predefinida,
	objStore: this.storeCO_AE
});

this.nu_numero = new Ext.form.NumberField({
	fieldLabel:'Partida',
	name:'partida',
	value:this.OBJ.nu_partida,
	allowBlank:false,
	minLength : 1,
	maxLength: 12,
	allowDecimals: false,
	decimalPrecision: 0,
	allowNegative: false,
	// readOnly:true,
	// style:'background:#c9c9c9;',
	msgTarget: 'under',
	width:100,
	validator: function(){
		return this.validFlag;
	},
	listeners:{
		change: function(textfield, newValue, oldValue){
			tipoaccionpartidaEditar.main.formPanel_.el.mask('Por Favor Espere...', 'x-mask-loading');
			var me = this;
			Ext.Ajax.request({
				method:'POST',
				url: 'auxiliar/partida/buscar',
				params: {
					partida: newValue,
					_token: '{{ csrf_token() }}'
				},
				failure: function(response){
					tipoaccionpartidaEditar.main.formPanel_.el.unmask();
				},
				success : function(response) {
					tipoaccionpartidaEditar.main.formPanel_.el.unmask();
					var errores = '';
					for(datos in Ext.decode(response.responseText).msg){
						errores += Ext.decode(response.responseText).msg[datos] + '<br>';
					}
					me.validFlag = Ext.decode(response.responseText).valido ? true : errores;
					me.validate();

					obj = Ext.util.JSON.decode(response.responseText);
					if(!obj.data){
						tipoaccionpartidaEditar.main.de_nombre.setValue("");
					}else{
						tipoaccionpartidaEditar.main.de_nombre.setValue(obj.data.tx_nombre);
					}
				}
			});
		}
	}
});

this.de_nombre = new Ext.form.TextArea({
	fieldLabel:'Denominacion',
	name:'denominacion',
	value:this.OBJ.de_partida,
	allowBlank:false,
	width:400,
	height: 100,
	readOnly:true
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!tipoaccionpartidaEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        tipoaccionpartidaEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/tipoaccion/partida/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/tipoaccion/partida/guardar') }}/{!! $data->id !!}',
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
                 tipoaccionpartidaLista.main.store_lista.load();
                 tipoaccionpartidaEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        tipoaccionpartidaEditar.main.winformPanel_.close();
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
		this.ac,
		this.id_tab_ac_ae_predefinida,
		this.nu_numero,
		this.de_nombre
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Partida Permitida',
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
			@if( in_array( array( 'de_privilegio' => 'tipoac.partida.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
tipoaccionpartidaLista.main.mascara.hide();
},
getStoreCO_AE:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/ac/ae/activo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'nu_numero'},{name: 'de_nombre'},
						{name: 'descripcion',
								convert: function(v, r) {
										return r.nu_numero + ' - ' + r.de_nombre;
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
Ext.onReady(tipoaccionpartidaEditar.main.init, tipoaccionpartidaEditar.main);
</script>
