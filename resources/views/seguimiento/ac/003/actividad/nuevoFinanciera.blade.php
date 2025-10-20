<script type="text/javascript">
Ext.ns("metafinancieraEditar");
metafinancieraEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<Stores de fk>
this.storeCO_ESTADO = this.getStoreCO_ESTADO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_MUNICIPIO = this.getStoreCO_MUNICIPIO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_PARROQUIA = this.getStoreCO_PARROQUIA();
//<Stores de fk>
//<Stores de fk>
this.storeCO_PARTIDA = this.getStoreCO_PARTIDA();
//<Stores de fk>
//<Stores de fk>
this.storeCO_FUENTE_FINANCIAMIENTO = this.getStoreCO_FUENTE_FINANCIAMIENTO();
//<Stores de fk>

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>
this.id_tab_meta_fisica = new Ext.form.Hidden({
	name:'meta_fisica',
	value:this.OBJ.id_tab_meta_fisica,
});

this.id_tab_estado = new Ext.form.ComboBox({
	fieldLabel:'ESTADO',
	store: this.storeCO_ESTADO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_estado',
	hiddenName:'estado',
	//readOnly:(this.OBJ.id_tab_estado!='')?true:false,
	//style:(this.OBJ.id_tab_estado!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Estado',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	resizable:true,
	allowBlank:false,
	listeners:{
            change: function(){
                metafinancieraEditar.main.storeCO_MUNICIPIO.load({
                    params: {estado:this.getValue(), _token:'{{ csrf_token() }}'}
                })
            }
        }
});

this.storeCO_ESTADO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_estado,
	value:  this.OBJ.id_tab_estado,
	objStore: this.storeCO_ESTADO
});

this.storeCO_ESTADO.on('load', function(){
  metafinancieraEditar.main.id_tab_estado.setValue(23);
  try{
          metafinancieraEditar.main.id_tab_estado.selectByValue(23);

    }
    catch(err){
    }
});

	this.storeCO_MUNICIPIO.load({
		params: {estado:23, _token:'{{ csrf_token() }}'},
		callback: function(){
			metafinancieraEditar.main.id_tab_municipio_detalle.setValue(metafinancieraEditar.main.OBJ.id_tab_municipio_detalle);
		}
	});

if(this.OBJ.id_tab_estado){
	this.storeCO_MUNICIPIO.load({
		params: {estado:this.OBJ.id_tab_estado, _token:'{{ csrf_token() }}'},
		callback: function(){
			metafinancieraEditar.main.id_tab_municipio_detalle.setValue(metafinancieraEditar.main.OBJ.id_tab_municipio_detalle);
		}
	});
}

this.id_tab_estado.on('beforeselect',function(cmb,record,index){
        	this.id_tab_municipio_detalle.clearValue();
},this);

this.id_tab_municipio_detalle = new Ext.form.ComboBox({
	fieldLabel:'MUNICIPIO',
	store: this.storeCO_MUNICIPIO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_municipio',
	hiddenName:'municipio',
	//readOnly:(this.OBJ.id_tab_municipio_detalle!='')?true:false,
	//style:(this.OBJ.id_tab_municipio_detalle!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Municipio',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	resizable:true,
	allowBlank:false,
	listeners:{
            change: function(){
                metafinancieraEditar.main.storeCO_PARROQUIA.load({
                    params: {id_tab_municipio:this.getValue(), _token:'{{ csrf_token() }}'}
                })
            }
        }
});

this.id_tab_municipio_detalle.on('beforeselect',function(cmb,record,index){
        	this.id_tab_parroquia_detalle.clearValue();
},this);

if(this.OBJ.id_tab_municipio_detalle){
	this.storeCO_PARROQUIA.load({
		params: {id_tab_municipio:this.OBJ.id_tab_municipio_detalle, _token:'{{ csrf_token() }}'},
		callback: function(){
			metafinancieraEditar.main.id_tab_parroquia_detalle.setValue(metafinancieraEditar.main.OBJ.id_tab_parroquia_detalle);
		}
	});
}

this.id_tab_parroquia_detalle = new Ext.form.ComboBox({
	fieldLabel:'PARROQUIA',
	store: this.storeCO_PARROQUIA,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_parroquia',
	hiddenName:'parroquia',
	//readOnly:(this.OBJ.id_tab_parroquia_detalle!='')?true:false,
	//style:(this.OBJ.id_tab_parroquia_detalle!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Parroquia',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	resizable:true,
	allowBlank:false
});

this.co_partida = new Ext.form.ComboBox({
	fieldLabel:'PARTIDA',
	store: this.storeCO_PARTIDA,
	typeAhead: true,
	valueField: 'co_partida',
	displayField:'co_partida',
	hiddenName:'partida',
	//readOnly:(this.OBJ.co_partida!='')?true:false,
	//style:(this.OBJ.co_partida!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Partida',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	resizable:true,
	allowBlank:false
});

/*this.storeCO_PARTIDA.load({
		params: {id_accion_centralizada:this.OBJ.id_accion_centralizada,co_ac_acc_espec:this.OBJ.co_ac_acc_espec}
	});*/
this.storeCO_PARTIDA.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_partida,
	value:  this.OBJ.co_partida,
	objStore: this.storeCO_PARTIDA
});

this.id_tab_fuente_financiamiento = new Ext.form.ComboBox({
	fieldLabel:'FUENTE DE FINANCIAMIENTO',
	store: this.storeCO_FUENTE_FINANCIAMIENTO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_tipo_fondo',
	hiddenName:'fuente_financiamiento',
	//readOnly:(this.OBJ.id_tab_fuente_financiamiento!='')?true:false,
	//style:(this.OBJ.id_tab_fuente_financiamiento!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Fuente de Fianciamiento',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_tipo_fondo}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});

this.storeCO_FUENTE_FINANCIAMIENTO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_fuente_financiamiento,
	value:  this.OBJ.id_tab_fuente_financiamiento,
	objStore: this.storeCO_FUENTE_FINANCIAMIENTO
});

this.mo_presupuesto = new Ext.form.NumberField({
	fieldLabel:'PRESUPUESTO MODIFICADO',
	name:'presupuesto',
	value:this.OBJ.mo_presupuesto,
	allowBlank:false,
	width:200,
	minLength : 1,
	maxLength: 20,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 20},
	blankText: '0.00'
});


this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!metafinancieraEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        
        if(metafinancieraEditar.main.mo_presupuesto.getValue()<=0){
            Ext.Msg.alert("Alerta","El monto del presupuesto modificado debe ser mayor a 0");
            return false;
        }        
        
        metafinancieraEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('ac/seguimiento/003/actividad/financiera/guardar') }}',
	@else
		url:'{{ URL::to('ac/seguimiento/003/actividad/financiera/guardar') }}/{!! $data->id !!}',
	@endif
		waitMsg: 'Enviando datos, por favor espere..',
		waitTitle:'Enviando',
            failure: function(form, action) {
//		var errores = '';
//		for(datos in action.result.msg){
//			errores += action.result.msg[datos] + '<br>';
//		}
                Ext.MessageBox.alert('Error en transacción', action.result.msg);
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
                 forma003ActividadLista.main.store_lista.load();
                 forma004ActividadEditar.main.store_lista.load();
                 metafinancieraEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        metafinancieraEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	labelWidth: 180,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
		this.id_tab_meta_fisica,
		this.id_tab_estado,
		this.id_tab_municipio_detalle,
		this.id_tab_parroquia_detalle,
		this.mo_presupuesto,
		this.co_partida,
		this.id_tab_fuente_financiamiento
	]
});

this.winformPanel_ = new Ext.Window({
    title:'META FINANCIERA',
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
//forma004ActividadEditar.main.mascara.hide();
},
getStoreCO_ESTADO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/estado') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_estado'}
            ]
    });
    return this.store;
},
getStoreCO_MUNICIPIO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/municipio/todo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_municipio'}
            ]
    });
    return this.store;
},
getStoreCO_PARROQUIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/parroquia/todo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_parroquia'}
            ]
    });
    return this.store;
},
getStoreCO_PARTIDA:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('ac/seguimiento/002/actividad/financiera/partida') }}',
        root:'data',
				baseParams:{
					id_tab_ac_ae_predefinida:{{ $data->id_tab_ac_ae_predefinida }},
					_token: '{{ csrf_token() }}',
				},
        fields:[
            {name: 'co_partida'}
            ]
    });
    return this.store;
},
getStoreCO_FUENTE_FINANCIAMIENTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('ac/seguimiento/002/actividad/financiera/fuentefinanciamiento') }}',
        root:'data',
        baseParams:{
                id_tab_ac_ae:{{ $data->id_tab_ac_ae }},
                _token: '{{ csrf_token() }}',
        },        
        fields:[
            {name: 'id'},{name: 'de_tipo_fondo'}
            ]
    });
    return this.store;
}
};
Ext.onReady(metafinancieraEditar.main.init, metafinancieraEditar.main);
</script>
