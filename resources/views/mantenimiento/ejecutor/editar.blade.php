<script type="text/javascript">
Ext.ns("ejecutorEditar");
ejecutorEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<Stores de fk>
this.storeCO_TIPO_EJECUTOR = this.getStoreCO_TIPO_EJECUTOR();
//<Stores de fk>
//<Stores de fk>
this.storeID_AMBITO_EJECUTOR = this.getStoreID_AMBITO_EJECUTOR();
//<Stores de fk>

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.id_ejecutor = new Ext.form.TextField({
	fieldLabel:'Codigo',
	name:'codigo',
	value:this.OBJ.id_ejecutor,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
	readOnly:(this.OBJ.id_ejecutor!='')?true:false,
	style:(this.OBJ.id_ejecutor!='')?'background:#c9c9c9;':'',
});

this.tx_ejecutor = new Ext.form.TextField({
	fieldLabel:'Nombre',
	name:'nombre',
	value:this.OBJ.tx_ejecutor,
	width:400,
	maxLength: 250,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.car_01 = new Ext.form.TextField({
	fieldLabel:'car_01',
	name:'car_01',
	value:this.OBJ.car_01,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.car_02 = new Ext.form.TextField({
	fieldLabel:'car_02',
	name:'car_02',
	value:this.OBJ.car_02,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.car_03 = new Ext.form.TextField({
	fieldLabel:'car_03',
	name:'car_03',
	value:this.OBJ.car_03,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.car_04 = new Ext.form.TextField({
	fieldLabel:'car_04',
	name:'car_04',
	value:this.OBJ.car_04,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.co_tipo_ejecutor = new Ext.form.ComboBox({
	fieldLabel:'Tipo de Ejecutor',
	store: this.storeCO_TIPO_EJECUTOR,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_tipo_ejecutor',
	hiddenName:'tipo',
	//readOnly:(this.OBJ.co_tipo_ejecutor!='')?true:false,
	//style:(this.main.OBJ.co_tipo_ejecutor!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione tipo Ejecutor',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false
});
this.storeCO_TIPO_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_tipo_ejecutor,
	value:  this.OBJ.id_tab_tipo_ejecutor,
	objStore: this.storeCO_TIPO_EJECUTOR
});

this.id_ambito_ejecutor = new Ext.form.ComboBox({
	fieldLabel:'Ambito del Ejecutor',
	store: this.storeID_AMBITO_EJECUTOR,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_ambito_ejecutor',
	hiddenName:'ambito',
	//readOnly:(this.OBJ.id_ambito_ejecutor!='')?true:false,
	//style:(this.main.OBJ.id_ambito_ejecutor!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Ambito Ejecutor',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false
});
this.storeID_AMBITO_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_ambito_ejecutor,
	value:  this.OBJ.id_tab_ambito_ejecutor,
	objStore: this.storeID_AMBITO_EJECUTOR
});

this.codigo_01 = new Ext.form.TextField({
	fieldLabel:'codigo_01',
	name:'codigo_01',
	value:this.OBJ.codigo_01,
	width:100,
	maxLength: 8,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 8},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.codigo_eje = new Ext.form.TextField({
	fieldLabel:'codigo_eje',
	name:'codigo_eje',
	value:this.OBJ.codigo_eje,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.de_correo = new Ext.form.TextField({
	fieldLabel:'Correo Institucional',
	name:'correo',
	value:this.OBJ.de_correo,
	width:200,
	//allowBlank:false,
	//emptyText: 'correo@diminio.com',
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail'
});

this.de_telefono = new Ext.form.TextField({
	fieldLabel:'Telefono',
	name:'telefono',
	value:this.OBJ.de_telefono,
	//allowBlank:false,
	//emptyText: '0000-0000000',
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!ejecutorEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        ejecutorEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/ejecutor/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/ejecutor/guardar') }}/{!! $data->id !!}',
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
                 ejecutorLista.main.store_lista.load();
                 ejecutorEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        ejecutorEditar.main.winformPanel_.close();
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
    this.id_ejecutor,
    this.tx_ejecutor,
    this.car_01,
    this.car_02,
    this.car_03,
    this.car_04,
    this.co_tipo_ejecutor,
    this.id_ambito_ejecutor,
    this.codigo_01,
    this.codigo_eje,
    this.de_correo,
    this.de_telefono
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Ejecutor',
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
			@if( in_array( array( 'de_privilegio' => 'ejecutor.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
ejecutorLista.main.mascara.hide();
},
getStoreCO_TIPO_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/ejecutor/tipo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_tipo_ejecutor'}
            ],
            listeners : {
                exception : function(proxy, response, operation) {
                    Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                }
            }
    });
    return this.store;
},
getStoreID_AMBITO_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/ejecutor/ambito') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_ambito_ejecutor'}
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
Ext.onReady(ejecutorEditar.main.init, ejecutorEditar.main);
</script>
