<script type="text/javascript">
Ext.ns("distribucionmunicipioEditar");
distribucionmunicipioEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<Stores de fk>
this.storeCO_MUNICIPIO = this.getStoreCO_MUNICIPIO();
//<Stores de fk>

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.id_tab_municipio = new Ext.form.ComboBox({
	fieldLabel:'MUNICIPIO',
	store: this.storeCO_MUNICIPIO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_municipio',
	hiddenName:'municipio',
	//readOnly:(this.OBJ.id_tab_municipio!='')?true:false,
	//style:(this.main.OBJ.id_tab_municipio!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Municipio...',
	selectOnFocus: true,
	mode: 'local',
	width:200,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_municipio}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});
this.storeCO_MUNICIPIO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_municipio,
	value:  this.OBJ.id_tab_municipio,
	objStore: this.storeCO_MUNICIPIO
});

this.co_partida = new Ext.form.NumberField({
	fieldLabel:'PARTIDA',
	name:'partida',
	value:this.OBJ.co_partida,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 18},
});

this.nu_base_censo = new Ext.form.NumberField({
	fieldLabel:'PROYECCION DE POBLACION',
	name:'base_censo',
	value:this.OBJ.nu_base_censo,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.nu_factor_poblacion = new Ext.form.NumberField({
	fieldLabel:'FACTOR POBLACIONAL',
	name:'factor_poblacion',
	value:this.OBJ.nu_factor_poblacion,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18,
	decimalPrecision: 8,
	allowDecimals: true
});

this.cuatrocinco_ppi = new Ext.form.NumberField({
	fieldLabel:'45% PARTES IGUALES',
	name:'cuatrocinco_ppi',
	value:this.OBJ.cuatrocinco_ppi,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.cincocero_fpp = new Ext.form.NumberField({
	fieldLabel:'50% EN FUNCION DE LA POBLACION',
	name:'cincocero_fpp',
	value:this.OBJ.cincocero_fpp,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.superficie_km = new Ext.form.NumberField({
	fieldLabel:'SUPERF. KM2',
	name:'superficie_km',
	value:this.OBJ.superficie_km,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.superficie_factor = new Ext.form.NumberField({
	fieldLabel:'FACTOR SUPERFICIE',
	name:'superficie_factor',
	value:this.OBJ.superficie_factor,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18,
	decimalPrecision: 8,
	allowDecimals: true
});

this.extension_territorio = new Ext.form.NumberField({
	fieldLabel:'5% PROP. EXTENSION TERRITORIO',
	name:'extension_territorio',
	value:this.OBJ.extension_territorio,
	allowBlank:false,
	width:200,
	minLength : 0,
	maxLength: 18
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!distribucionmunicipioEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        distribucionmunicipioEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/distribucionmunicipio/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/distribucionmunicipio/guardar') }}/{!! $data->id !!}',
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
                 distribucionmunicipioLista.main.store_lista.load();
                 distribucionmunicipioEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        distribucionmunicipioEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	labelWidth: 220,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
		this.id_tab_municipio,
		this.co_partida,
		this.nu_base_censo,
		this.nu_factor_poblacion,
		this.cuatrocinco_ppi,
		this.cincocero_fpp,
		this.superficie_km,
		this.superficie_factor,
		this.extension_territorio
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Distribucion Municipios',
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
			@if( in_array( array( 'de_privilegio' => 'libro.distribucionmunicipio.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
distribucionmunicipioLista.main.mascara.hide();
},
getStoreCO_MUNICIPIO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/municipio') }}',
        root:'data',
        fields:[
            {name: 'id'},
						{name: 'de_municipio'}
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
Ext.onReady(distribucionmunicipioEditar.main.init, distribucionmunicipioEditar.main);
</script>
