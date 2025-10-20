<script type="text/javascript">
Ext.ns("objetivoEditar");
objetivoEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.storeCO_EJERCICIO = this.getStoreCO_EJERCICIO();

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.tx_codigo = new Ext.form.TextField({
	fieldLabel:'Codigo',
	name:'codigo',
	value:this.OBJ.tx_codigo,
	width:100,
	maxLength: 10,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 10},
	//readOnly:(this.OBJ.tx_codigo!='')?true:false,
	//style:(this.OBJ.tx_codigo!='')?'background:#c9c9c9;':'',
});

this.co_objetivo_historico = new Ext.form.NumberField({
	fieldLabel:'Objetivo Historico',
	name:'objetivo_historico',
	value:this.OBJ.co_objetivo_historico,
	//allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 1},
});

this.co_objetivo_nacional = new Ext.form.TextField({
	fieldLabel:'Objetivo Nacional',
	name:'objetivo_nacional',
	value:this.OBJ.co_objetivo_nacional,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2}
});

this.co_objetivo_estrategico = new Ext.form.TextField({
	fieldLabel:'Objetivo Estrategico',
	name:'objetivo_estrategico',
	value:this.OBJ.co_objetivo_estrategico,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2}
});

this.co_objetivo_general = new Ext.form.TextField({
	fieldLabel:'Objetivo General',
	name:'objetivo_general',
	value:this.OBJ.co_objetivo_general,
	width:100,
	maxLength: 2,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 2}
});

this.nu_nivel = new Ext.form.NumberField({
	fieldLabel:'Nivel',
	name:'nivel',
	value:this.OBJ.nu_nivel,
	//allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 1},
});

this.tx_descripcion = new Ext.form.TextArea({
	fieldLabel:'Descripcion',
	name:'descripcion',
	value:this.OBJ.tx_descripcion,
	width:400,
	maxLength: 600,
	height:100,
});

this.id_tab_ejercicio_fiscal = new Ext.ux.form.SuperBoxSelect({
	fieldLabel:'Ejercicio Fiscal',
	store: this.storeCO_EJERCICIO,
	typeAhead: true,
	xtype:'superboxselect',
	allowQueryAll : false,
	valueField: 'id',
	displayField:'id',
	hiddenName:'id_tab_ejercicio_fiscal[]',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Ejercicio',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{id}</div></div></tpl>'),
	hideOnSelect:false,
	resizable:true,
});

this.storeCO_EJERCICIO.load({
	callback: function(){
		objetivoEditar.main.id_tab_ejercicio_fiscal.setValue(objetivoEditar.main.OBJ.id_tab_ejercicio_fiscal.replace('{','').replace('}',''));
	}
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!objetivoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        objetivoEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/objetivo/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/objetivo/guardar') }}/{!! $data->id !!}',
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
                 objetivoLista.main.store_lista.load();
                 objetivoEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        objetivoEditar.main.winformPanel_.close();
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
		this.tx_codigo,
		this.co_objetivo_historico,
		this.co_objetivo_nacional,
		this.co_objetivo_estrategico,
		this.co_objetivo_general,
		this.nu_nivel,
		this.tx_descripcion,
		this.id_tab_ejercicio_fiscal
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Objetivos',
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
			@if( in_array( array( 'de_privilegio' => 'objetivos.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
objetivoLista.main.mascara.hide();
},
getStoreCO_EJERCICIO:function(){
    this.store = new Ext.data.JsonStore({
		url:'{{ URL::to('ejercicio/lista') }}',
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
}
};
Ext.onReady(objetivoEditar.main.init, objetivoEditar.main);
</script>
