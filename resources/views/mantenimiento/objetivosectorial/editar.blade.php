<script type="text/javascript">
Ext.ns("objetivosectorialEditar");
objetivosectorialEditar.main = {
init:function(){

//<Stores de fk>
this.storeCO_SECTOR = this.getStoreCO_SECTOR();
//<Stores de fk>

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.id_tab_ejercicio_fiscal = new Ext.form.Hidden({
	name:'id_tab_ejercicio_fiscal',
	value: this.OBJ.id_tab_ejercicio_fiscal
});

this.id_tab_sectores = new Ext.form.ComboBox({
	fieldLabel:'Sector',
	store: this.storeCO_SECTOR,
	typeAhead: true,
	valueField: 'id',
	displayField:'nu_descripcion',
	hiddenName:'id_tab_sectores',
	readOnly:(this.OBJ.id_tab_sectores!='')?true:false,
	style:(this.OBJ.id_tab_sectores!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Sector...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{nu_descripcion}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});
this.storeCO_SECTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_sectores,
	value:  this.OBJ.id_tab_sectores,
	objStore: this.storeCO_SECTOR
});

this.de_objetivo_sectorial = new Ext.form.TextArea({
	fieldLabel:'Descripcion',
	name:'objetivo',
	value:this.OBJ.de_objetivo_sectorial,
	width:400,
	height:200,
	allowBlank:false,
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

        if(!objetivosectorialEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        objetivosectorialEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/objetivosectorial/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/objetivosectorial/guardar') }}/{!! $data->id !!}',
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
                 objetivosectorialLista.main.store_lista.load();
                 objetivosectorialEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        objetivosectorialEditar.main.winformPanel_.close();
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
		this.id_tab_sectores,
		this.de_objetivo_sectorial
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Objetivo Sectorial',
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
			@if( in_array( array( 'de_privilegio' => 'libro.objetivosectorial.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
objetivosectorialLista.main.mascara.hide();
},
getStoreCO_SECTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/poa/sector') }}',
        root:'data',
        fields:[
            {name: 'id'},
						{name: 'nu_descripcion'},
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
Ext.onReady(objetivosectorialEditar.main.init, objetivosectorialEditar.main);
</script>
