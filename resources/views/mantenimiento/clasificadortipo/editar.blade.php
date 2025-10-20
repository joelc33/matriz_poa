<script type="text/javascript">
Ext.ns("clasificadortipoEditar");
clasificadortipoEditar.main = {
init:function(){

//<Stores de fk>
this.storeCO_TIPO_PERSONAL = this.getStoreCO_TIPO_PERSONAL();
//<Stores de fk>
//<Stores de fk>
this.storeCO_EF = this.getStoreCO_EF();
//<Stores de fk>

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

/*this.id_tab_ejercicio_fiscal = new Ext.form.ComboBox({
	fieldLabel:'Ejercicio',
	store: this.storeCO_EF,
	typeAhead: true,
	valueField: 'id',
	displayField:'id',
	hiddenName:'ejercicio_fiscal',
	//readOnly:(this.OBJ.id_tab_ejercicio_fiscal!='')?true:false,
	//style:(this.main.OBJ.id_tab_ejercicio_fiscal!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Ejercicio...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{id}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});
this.storeCO_EF.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_ejercicio_fiscal,
	value:  this.OBJ.id_tab_ejercicio_fiscal,
	objStore: this.storeCO_EF
});*/

this.id_tab_ejercicio_fiscal = new Ext.form.NumberField({
	fieldLabel:'Ejercicio',
	name:'ejercicio_fiscal',
	value:this.OBJ.id_tab_ejercicio_fiscal,
	allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 4,
});

this.id_tab_tipo_personal = new Ext.form.ComboBox({
	fieldLabel:'Tipo de Personal',
	store: this.storeCO_TIPO_PERSONAL,
	typeAhead: true,
	valueField: 'id',
	displayField:'descripcion',
	hiddenName:'tipo_personal',
	//readOnly:(this.OBJ.id_tab_tipo_personal!='')?true:false,
	//style:(this.main.OBJ.id_tab_tipo_personal!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Tipo de Personal...',
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
	objCMB: this.id_tab_tipo_personal,
	value:  this.OBJ.id_tab_tipo_personal,
	objStore: this.storeCO_TIPO_PERSONAL
});

this.nu_masculino = new Ext.form.NumberField({
	fieldLabel:'Total Masculino',
	name:'masculino',
	value:this.OBJ.nu_masculino,
	allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 18,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 18},
});

this.nu_femenino = new Ext.form.NumberField({
	fieldLabel:'Total Femenino',
	name:'femenino',
	value:this.OBJ.nu_femenino,
	allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 18,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 18},
});

this.mo_sueldo = new Ext.form.NumberField({
	fieldLabel:'Total Sueldo',
	name:'sueldo',
	value:this.OBJ.mo_sueldo,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 22,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 22},
});

this.mo_compensacion = new Ext.form.NumberField({
	fieldLabel:'Total Compensacion',
	name:'compensacion',
	value:this.OBJ.mo_compensacion,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 22,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 22},
});

this.mo_primas = new Ext.form.NumberField({
	fieldLabel:'Total Primas',
	name:'primas',
	value:this.OBJ.mo_primas,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 22,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 22},
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!clasificadortipoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        clasificadortipoEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/clasificadortipo/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/clasificadortipo/guardar') }}/{!! $data->id !!}',
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
                 clasificadortipoLista.main.store_lista.load();
                 clasificadortipoEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        clasificadortipoEditar.main.winformPanel_.close();
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
		this.id_tab_ejercicio_fiscal,
		this.id_tab_tipo_personal,
		this.nu_masculino,
		this.nu_femenino,
		this.mo_sueldo,
		this.mo_compensacion,
		this.mo_primas
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Clasificacion de Personal',
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
			@if( in_array( array( 'de_privilegio' => 'libro.clasificadortipo.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
clasificadortipoLista.main.mascara.hide();
},
getStoreCO_EF:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/ef') }}',
        root:'data',
        fields:[
            {name: 'id'}
            ],
            listeners : {
                exception : function(proxy, response, operation) {
                    Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                }
            }
    });
    return this.store;
},
getStoreCO_TIPO_PERSONAL:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/personal/hijo') }}',
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
Ext.onReady(clasificadortipoEditar.main.init, clasificadortipoEditar.main);
</script>
