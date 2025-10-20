<script type="text/javascript">
Ext.ns("usuarioDatos");
usuarioDatos.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});
this.OBJejecutor = paqueteComunJS.funcion.doJSON({stringData:'{!! $ejecutor !!}'});

//<Stores de fk>
this.storeCO_DOCUMENTO = this.getStoreCO_DOCUMENTO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_CARGO = this.getStoreCO_CARGO();
//<Stores de fk>

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>
this.co_ejecutores = new Ext.form.Hidden({
    name:'ejecutor',
    value:this.OBJejecutor.id});

//<ClavePrimaria>
this.id_funcionario = new Ext.form.Hidden({
    name:'id_funcionario',
    value:this.OBJ.id_funcionario});
//</ClavePrimaria>

this.da_usuario = new Ext.form.TextField({
	fieldLabel:'Usuario',
	name:'usuario',
	value:this.OBJ.da_login,
	readOnly:true,
	style:'background:#c9c9c9;',
	allowBlank:false,
	width:200
});

this.id_tab_documento = new Ext.form.ComboBox({
	fieldLabel:'documento',
	store: this.storeCO_DOCUMENTO,
	typeAhead: true,
	valueField: 'id',
	displayField:'inicial',
	hiddenName:'documenton',
	//readOnly:(this.OBJ.id_tab_documento!='')?true:false,
	//style:(this.main.OBJ.id_tab_documento!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'...',
	selectOnFocus: true,
	mode: 'local',
	width:40,
	resizable:true,
	allowBlank:false
});

this.storeCO_DOCUMENTO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_documento,
	value:  this.OBJ.id_tab_documento,
	objStore: this.storeCO_DOCUMENTO
});

this.nu_cedula = new Ext.form.NumberField({
	fieldLabel:'Nu cedula',
	name:'cedula',
	value:this.OBJ.nu_cedula,
	width:155,
	allowBlank:false,
	minLength : 5,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
});

this.compositefieldCI = new Ext.form.CompositeField({
fieldLabel: 'Cédula',
items: [
	this.id_tab_documento,
	this.nu_cedula,
	]
});

this.nb_funcionario = new Ext.form.TextField({
	fieldLabel:'Nombre',
	name:'nombre',
	value:this.OBJ.nb_funcionario,
	allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.ap_funcionario = new Ext.form.TextField({
	fieldLabel:'Apellido',
	name:'apellido',
	value:this.OBJ.ap_funcionario,
	allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.id_tab_cargo = new Ext.form.ComboBox({
	fieldLabel:'Cargo',
	store: this.storeCO_CARGO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_cargo',
	hiddenName:'cargo',
	//readOnly:(this.OBJ.id_tab_cargo!='')?true:false,
	//style:(this.main.OBJ.id_tab_cargo!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Cargo',
	selectOnFocus: true,
	mode: 'local',
	width:200,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_cargo}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});
this.storeCO_CARGO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_cargo,
	value:  this.OBJ.id_tab_cargo,
	objStore: this.storeCO_CARGO
});

this.tx_direccion = new Ext.form.TextArea({
	fieldLabel:'Dirección',
	name:'direccion',
	value:this.OBJ.tx_direccion,
	//allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_telefono = new Ext.form.TextField({
	fieldLabel:'Teléfonos',
	name:'telefono_funcionario',
	value:this.OBJ.tx_telefono,
	//allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.da_email = new Ext.form.TextField({
	fieldLabel:'Correo Electrónico',
	name:'correo_funcionario',
	value:this.OBJ.da_email,
	width:200,
	//allowBlank:false,
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail',
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.de_correo = new Ext.form.TextField({
	fieldLabel:'Correo Institucional',
	name:'correo',
	value:this.OBJejecutor.de_correo,
	width:200,
	allowBlank:false,
	/*emptyText: 'correo@diminio.com',*/
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail'
});

this.de_telefono = new Ext.form.TextField({
	fieldLabel:'Telefono Institucion',
	name:'telefono',
	value:this.OBJejecutor.de_telefono,
	allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset1 = new Ext.form.FieldSet({
	title: 'Datos de Cuenta',
	autoWidth:true,
        items:[
		this.da_usuario
		]
});

this.fieldset2 = new Ext.form.FieldSet({
	title: 'Datos del Funcionario',
	autoWidth:true,
        items:[
		this.compositefieldCI,
		this.nb_funcionario,
		this.ap_funcionario,
		this.id_tab_cargo,
		this.tx_direccion,
		this.tx_telefono,
		this.da_email
		]
});

this.fielset3 = new Ext.form.FieldSet({
              title:'Datos de contacto del Ejecutor',autoWidth:true,
		//labelWidth: 130,
              items:[
		this.co_ejecutores,
		this.de_correo,
		this.de_telefono
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!usuarioDatos.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        usuarioDatos.main.formPanel_.getForm().submit({
		method:'POST',
		url:'{{ URL::to('usuario/cambios') }}',
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
		this.panelCambio = Ext.getCmp('tabpanel');
		this.panelCambio.remove('24');
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Cerrar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.panelCambio = Ext.getCmp('tabpanel');
	this.panelCambio.remove('24');
    }
});

this.formPanel_ = new Ext.form.FormPanel({
    autoWidth:true,
    border:false,
    labelWidth: 160,
    padding:'10px',
    deferredRender: false,
	items:[
		this._token,
		this.id_funcionario,
		this.fieldset1,
		this.fieldset2,
    this.fielset3
	],
    buttonAlign:'left',
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'datospersonales.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
	this.salir
    ]
});

this.formPanel_.render("datosPersonales");
},
getStoreCO_DOCUMENTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/documento') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'inicial'}
            ],
				    listeners : {
				        exception : function(proxy, response, operation) {
				            Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
				        }
				    }
    });
    return this.store;
},
getStoreCO_CARGO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/cargo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_cargo'}
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
Ext.onReady(usuarioDatos.main.init, usuarioDatos.main);
</script>
<div id="datosPersonales"></div>
