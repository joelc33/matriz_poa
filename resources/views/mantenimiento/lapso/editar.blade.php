<script type="text/javascript">
Ext.ns("lapsoEditar");
lapsoEditar.main = {
init:function(){

//<Stores de fk>
this.storeCO_EF = this.getStoreCO_EF();
//<Stores de fk>
//<Stores de fk>
this.storeCO_PERIODO = this.getStoreCO_PERIODO();

this.storeCO_TIPO_PERIODO = this.getStoreCO_TIPO_PERIODO();
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
	hiddenName:'ejercicio',
	readOnly:(this.OBJ.id_tab_ejercicio_fiscal)?true:false,
	style:(this.OBJ.id_tab_ejercicio_fiscal)?'background:#c9c9c9;':'',
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

this.id_tab_ejercicio_fiscal.on('beforeselect',function(cmb,record,index){
        	this.id_tab_periodo.clearValue();
                this.id_tab_tipo_periodo.clearValue();
},this);

this.id_tab_periodo = new Ext.form.ComboBox({
	fieldLabel:'Tipo',
	store: this.storeCO_PERIODO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_periodo',
	hiddenName:'periodo',
	readOnly:(this.OBJ.id_tab_periodo)?true:false,
	style:(this.OBJ.id_tab_periodo)?'background:#c9c9c9;':'',
//	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Tipo...',
	selectOnFocus: true,
	mode: 'local',
	width:100,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_periodo}</div></div></tpl>'),
	resizable:true,
	allowBlank:false,
	listeners:{
            change: function(){
                lapsoEditar.main.storeCO_TIPO_PERIODO.load({
                    params: {periodo:this.getValue(),anio:lapsoEditar.main.id_tab_ejercicio_fiscal.getValue(), _token:'{{ csrf_token() }}'}
                })
            }
        }         
});
this.storeCO_PERIODO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_periodo,
	value:  this.OBJ.id_tab_periodo,
	objStore: this.storeCO_PERIODO
});

this.id_tab_periodo.on('beforeselect',function(cmb,record,index){
        	this.id_tab_tipo_periodo.clearValue();
},this);


this.id_tab_tipo_periodo = new Ext.form.ComboBox({
	fieldLabel:'Descripción',
	store: this.storeCO_TIPO_PERIODO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_tipo_periodo',
	hiddenName:'tipo_periodo',
	readOnly:(this.OBJ.id_tab_tipo_periodo)?true:false,
	style:(this.OBJ.id_tab_tipo_periodo)?'background:#c9c9c9;':'',        
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Tipo...',
	selectOnFocus: true,
	mode: 'local',
	width:100,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_tipo_periodo}</div></div></tpl>'),
	resizable:true,
	allowBlank:false       
});

if(this.OBJ.id_tab_periodo){

this.storeCO_TIPO_PERIODO.load({
    params: {periodo:this.OBJ.id_tab_periodo, _token:'{{ csrf_token() }}'}
});    
}

	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_tipo_periodo,
	value:  this.OBJ.id_tab_tipo_periodo,
	objStore: this.storeCO_TIPO_PERIODO
});

this.fe_inicio = new Ext.form.DateField({
	fieldLabel:'Fecha Inicio',
	name:'fecha_inicio',
	value:this.OBJ.fe_inicio,
	allowBlank:false,
	width:100
});

this.fe_fin = new Ext.form.DateField({
	fieldLabel:'Fecha Cierre',
	name:'fecha_cierre',
	value:this.OBJ.fe_fin,
	allowBlank:false,
	width:100
});

this.de_lapso = new Ext.form.Hidden({
	fieldLabel:'Descripcion',
	name:'descripcion',
	value:this.OBJ.de_lapso,
	width:200,
	maxLength: 600,
	allowBlank:false
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!lapsoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        lapsoEditar.main.de_lapso.setValue(lapsoEditar.main.id_tab_tipo_periodo.getRawValue());       
        
        lapsoEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/lapso/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/lapso/guardar') }}/{!! $data->id !!}',
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
                 lapsoLista.main.store_lista.load();
                 lapsoEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        lapsoEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:400,
	labelWidth: 120,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
                this.de_lapso,
		this.id_tab_ejercicio_fiscal,
    this.id_tab_periodo,
		this.id_tab_tipo_periodo,
		this.fe_inicio,
    this.fe_fin
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Cronograma de Seguimiento',
    modal:true,
    constrain:true,
width:414,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'lapso.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
lapsoLista.main.mascara.hide();
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
getStoreCO_PERIODO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/periodo') }}',
        root:'data',
        fields:[
            {name: 'id'},
            {name: 'de_periodo'}
            ],
            listeners : {
                exception : function(proxy, response, operation) {
                    Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                }
            }
    });
    return this.store;
},
getStoreCO_TIPO_PERIODO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/periodo/tipo') }}',
        root:'data',
        fields:[
            {name: 'id'},
            {name: 'de_tipo_periodo'}
            ]
    });
    return this.store;
}
};
Ext.onReady(lapsoEditar.main.init, lapsoEditar.main);
</script>
