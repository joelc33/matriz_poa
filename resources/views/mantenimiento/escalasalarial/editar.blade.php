<script type="text/javascript">
Ext.ns("escalasalarialEditar");
escalasalarialEditar.main = {
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

this.id_tab_tipo_empleado = new Ext.form.ComboBox({
	fieldLabel:'Tipo de Empleado',
	store: this.storeCO_TIPO_PERSONAL,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_tipo_empleado',
	hiddenName:'tipo_empleado',
	//readOnly:(this.OBJ.id_tab_tipo_empleado!='')?true:false,
	//style:(this.main.OBJ.id_tab_tipo_empleado!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Tipo de Empleado...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_tipo_empleado}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});

this.storeCO_TIPO_PERSONAL.load();

paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_tipo_empleado,
	value:  this.OBJ.id_tab_tipo_empleado,
	objStore: this.storeCO_TIPO_PERSONAL
});

this.de_grupo = new Ext.form.TextField({
	fieldLabel:'Grupo',
	name:'grupo',
	value:this.OBJ.de_grupo,
	width:100,
	maxLength: 600,
	allowBlank:false,
	listeners:{
			change: function(){
					this.setValue(String(this.getValue()).toUpperCase());
			}
	}
});

this.de_escala_salarial = new Ext.form.TextField({
	fieldLabel:'Escala Salarial',
	name:'escala_salarial',
	value:this.OBJ.de_escala_salarial,
	width:400,
	maxLength: 600,
	allowBlank:false,
	listeners:{
			change: function(){
					this.setValue(String(this.getValue()).toUpperCase());
			}
	}
});

this.nu_masculino = new Ext.form.NumberField({
	fieldLabel:'Total Masculino',
	name:'masculino',
	value:this.OBJ.nu_masculino,
	//allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 18,
	//autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 18},
});

this.nu_femenino = new Ext.form.NumberField({
	fieldLabel:'Total Femenino',
	name:'femenino',
	value:this.OBJ.nu_femenino,
	//allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 18,
	//autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 18},
});

this.mo_sueldo = new Ext.form.NumberField({
	fieldLabel:'Total Sueldo',
	name:'sueldo',
	value:this.OBJ.mo_escala_salarial,
	//allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 22,
	//autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 22},
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!escalasalarialEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        escalasalarialEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/escalasalarial/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/escalasalarial/guardar') }}/{!! $data->id !!}',
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
                 escalasalarialLista.main.store_lista.load();
                 escalasalarialEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        escalasalarialEditar.main.winformPanel_.close();
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
		this.id_tab_tipo_empleado,
		this.de_grupo,
		this.de_escala_salarial,
		this.nu_masculino,
		this.nu_femenino,
		this.mo_sueldo
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Escala de Sueldos',
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
			@if( in_array( array( 'de_privilegio' => 'libro.escalasalarial.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
escalasalarialLista.main.mascara.hide();
},
getStoreCO_TIPO_PERSONAL:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/empleado/tipo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_tipo_empleado'}
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
Ext.onReady(escalasalarialEditar.main.init, escalasalarialEditar.main);
</script>
