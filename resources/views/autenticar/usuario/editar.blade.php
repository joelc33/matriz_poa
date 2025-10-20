<script type="text/javascript">
Ext.ns("usuarioEditar");
usuarioEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<Stores de fk>
this.storeCO_DOCUMENTO = this.getStoreCO_DOCUMENTO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_ROL = this.getStoreCO_ROL();
//<Stores de fk>
//<Stores de fk>
this.storeCO_CARGO = this.getStoreCO_CARGO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_EJECUTOR = this.getStoreCO_EJECUTOR();
//<Stores de fk>

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>
//<ClavePrimaria>
this.id_usuario = new Ext.form.Hidden({
    name:'id_usuario',
    value:this.OBJ.id});
//</ClavePrimaria>

//<ClavePrimaria>
this.id_funcionario = new Ext.form.Hidden({
    name:'id_funcionario',
    value:this.OBJ.id_funcionario});
//</ClavePrimaria>

this.da_usuario = new Ext.form.TextField({
	fieldLabel:'Usuario',
	name:'usuario',
	value:this.OBJ.da_login,
	allowBlank:false,
	width:200
});

this.id_tab_rol = new Ext.form.ComboBox({
	fieldLabel:'Rol',
	store: this.storeCO_ROL,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_rol',
	hiddenName:'rol',
	//readOnly:(this.OBJ.id_tab_rol!='')?true:false,
	//style:(this.main.OBJ.id_tab_rol!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Perfil',
	selectOnFocus: true,
	mode: 'local',
	width:200,
	resizable:true,
	allowBlank:false
});
this.storeCO_ROL.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_rol,
	value:  this.OBJ.id_tab_rol,
	objStore: this.storeCO_ROL
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
	validator: function(){
		return this.validFlag;
	},
        /*listeners:{
            change: function(textfield, newValue, oldValue){
		var me = this;
		Ext.Ajax.request({
			method:'POST',
			url: 'cedula/funcionario',
			params: { nacionalidad: usuarioEditar.main.id_tab_documento.getValue() , cedula: newValue, _token: '{{ csrf_token() }}' },
			success : function(response) {
				var errores = '';
				for(datos in Ext.decode(response.responseText).msg){
					errores += Ext.decode(response.responseText).msg[datos] + '<br>';
				}
				me.validFlag = Ext.decode(response.responseText).valido ? true : errores;
				me.validate();

				obj = Ext.util.JSON.decode(response.responseText);
                   		if(!obj.data){
					usuarioEditar.main.nb_funcionario.setValue("");
					usuarioEditar.main.ap_funcionario.setValue("");
                    		}else{
					usuarioEditar.main.nb_funcionario.setValue(obj.data.nb_funcionario);
					usuarioEditar.main.ap_funcionario.setValue(obj.data.ap_funcionario);
				}
			}
		});
            }
        }*/
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
        /*listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }*/
});

this.ap_funcionario = new Ext.form.TextField({
	fieldLabel:'Apellido',
	name:'apellido',
	value:this.OBJ.ap_funcionario,
	allowBlank:false,
	width:200,
        /*listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }*/
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

this.id_tab_ejecutores = new Ext.form.ComboBox({
	fieldLabel:'Unidad Ejecutora',
	store: this.storeCO_EJECUTOR,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_ejecutor',
	hiddenName:'ejecutor',
	//readOnly:(this.OBJ.id_tab_ejecutores!='')?true:false,
	//style:(this.main.OBJ.id_tab_ejecutores!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Unidad Ejecutora',
	selectOnFocus: true,
	mode: 'local',
	width:200,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_ejecutor}</div></div></tpl>'),
	//listWidth:'600',
	resizable:true,
	allowBlank:false
});
this.storeCO_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_ejecutores,
	value:  this.OBJ.id_tab_ejecutores,
	objStore: this.storeCO_EJECUTOR
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
        /*listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }*/
});

this.fieldset1 = new Ext.form.FieldSet({
	title: 'Datos de Cuenta',
	autoWidth:true,
        items:[
		this.da_usuario,
		this.id_tab_rol
		]
});

this.fieldset2 = new Ext.form.FieldSet({
	title: 'Datos del Funcionario',
	autoWidth:true,
        items:[
		this.compositefieldCI,
		this.nb_funcionario,
		this.ap_funcionario,
		this.id_tab_ejecutores,
		this.id_tab_cargo,
		this.tx_direccion,
		this.tx_telefono,
		this.da_email
		]
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!usuarioEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        usuarioEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('usuario/guardar') }}',
	@else
		url:'{{ URL::to('usuario/guardar') }}/{!! $data->id !!}',
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
                 usuarioLista.main.store_lista.load();
                 usuarioEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        usuarioEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:500,
	labelWidth: 150,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
		this.id_usuario,
		this.id_funcionario,
		this.fieldset1,
		this.fieldset2
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Usuario',
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
			@if( in_array( array( 'de_privilegio' => 'usuarios.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
usuarioLista.main.mascara.hide();
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
getStoreCO_ROL:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/rol') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_rol'}
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
},
getStoreCO_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/ejecutor/todo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'tx_ejecutor'},
						{name: 'de_ejecutor',
								convert: function(v, r) {
										return r.id_ejecutor + ' - ' + r.tx_ejecutor;
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
Ext.onReady(usuarioEditar.main.init, usuarioEditar.main);
</script>
