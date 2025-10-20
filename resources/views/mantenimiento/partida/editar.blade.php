<script type="text/javascript">
Ext.ns("partidaEditar");
partidaEditar.main = {
init:function(){

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

this.id_tab_ejercicio_fiscal = new Ext.form.ComboBox({
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
	width:100,
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
});


this.co_partida = new Ext.form.NumberField({
	fieldLabel:'Codigo',
	name:'partida',
	value:this.OBJ.co_partida,
	allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 18,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 18},
});

this.ace_mov = new Ext.form.Checkbox({
	fieldLabel:'Hace Movimiento',
	name:'ace_mov',
	checked:(this.OBJ.ace_mov==true) ? true:false,
	allowBlank:false
});

this.tx_descripcion = new Ext.form.TextArea({
	fieldLabel:'Descripcion',
	name:'nombre',
	value:this.OBJ.tx_nombre,
	width:400,
	maxLength: 600,
	height:100,
	allowBlank:false
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!partidaEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        partidaEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/partida/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/partida/guardar') }}/{!! $data->id !!}',
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
                 partidaLista.main.store_lista.load();
                 partidaEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        partidaEditar.main.winformPanel_.close();
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
		this.co_partida,
		this.ace_mov,
		this.tx_descripcion
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Partidas',
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
			@if( in_array( array( 'de_privilegio' => 'partidas.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
partidaLista.main.mascara.hide();
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
}
};
Ext.onReady(partidaEditar.main.init, partidaEditar.main);
</script>
