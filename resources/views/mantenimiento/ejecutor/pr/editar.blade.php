<script type="text/javascript">
Ext.ns("ejecutorprEditar");
ejecutorprEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.storeCO_SECTORES = this.getStoreCO_SECTORES();

this.storeID_TAB_AC_PREDEFINIDA = this.getStoreID_TAB_AC_PREDEFINIDA();

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>
this.id_tab_ejecutores = new Ext.form.Hidden({
	name:'id_tab_ejecutores',
	value:this.OBJ.id_tab_ejecutores
});

this.id_tab_sectores = new Ext.form.ComboBox({
	fieldLabel:'Sector',
	store: this.storeCO_SECTORES,
	typeAhead: true,
	valueField: 'id',
	displayField:'nu_descripcion',
	hiddenName:'sector',
	//readOnly:(this.OBJ.id_tab_tipo_recurso!='')?true:false,
	//style:(this.main.OBJ.id_tab_tipo_recurso!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione sector...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{nu_descripcion}</div></div></tpl>'),
	resizable:true,
	allowBlank:false,
        listeners: {
        change: function() {
            ejecutorprEditar.main.storeID_TAB_AC_PREDEFINIDA.load({
                params: {
                    co_sector: this.getValue()
                }
            });
        },
        beforeselect: function() {
            ejecutorprEditar.main.id_tab_ac_predefinida.clearValue();
        }
    }
});
this.storeCO_SECTORES.load();

this.id_tab_ac_predefinida = new Ext.form.ComboBox({
	fieldLabel:'Programa',
	store: this.storeID_TAB_AC_PREDEFINIDA,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_nombre',
	hiddenName:'id_tab_ac_predefinida',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione el programa',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!ejecutorprEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        ejecutorprEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/ejecutor/pr/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/ejecutor/pr/guardar') }}/{!! $data->id !!}',
	@endif
		waitMsg: 'Enviando datos, por favor espere..',
		waitTitle:'Enviando',
            failure: function(form, action) {
                     Ext.MessageBox.show({
                         title: 'Mensaje',
                         msg: action.result.msg,
                         closable: false,
                         icon: Ext.MessageBox.INFO,
                         resizable: false,
			 animEl: document.body,
                         buttons: Ext.MessageBox.OK
                     });
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
                 ejecutorprLista.main.store_lista.load();
                 ejecutorprEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        ejecutorprEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	labelWidth: 80,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
		this.id_tab_ejecutores,
		this.id_tab_sectores,
                this.id_tab_ac_predefinida
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Programa',
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
        this.guardar,
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
ejecutorprLista.main.mascara.hide();
},
getStoreCO_SECTORES:function(){
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
,
getStoreID_TAB_AC_PREDEFINIDA:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/ejecutor/ac/predefinida') }}',
        root:'data',
        fields:[
            {name: 'id'},						
            {name: 'de_nombre',
            convert: function(v, r) {
                            return r.nu_original + ' - ' + r.de_nombre;
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
Ext.onReady(ejecutorprEditar.main.init, ejecutorprEditar.main);
</script>
