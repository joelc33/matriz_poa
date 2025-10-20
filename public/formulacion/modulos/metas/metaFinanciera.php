<?php
	$data = json_encode(array(
		"id_proyecto"     => $_GET['id_proyecto'],
		"co_estado"     => 23,
		"co_municipio"     => "",
		"co_parroquia"     => "",
		"ae"     => $_GET['ae'],
	));
?>
<script type="text/javascript">
Ext.ns("detalleMetaEditar");
detalleMetaEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

this.Registro;

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

//<ClavePrimaria>
this.id_proyecto = new Ext.form.Hidden({
	name:'id_proyecto',
	value:this.OBJ.id_proyecto
});
//</ClavePrimaria>

this.co_estado = new Ext.form.ComboBox({
	fieldLabel:'ESTADO',
	store: this.storeCO_ESTADO,
	typeAhead: true,
	valueField: 'co_estado',
	displayField:'tx_estado',
	hiddenName:'co_estado',
	//readOnly:(this.OBJ.co_estado!='')?true:false,
	//style:(this.OBJ.co_estado!='')?'background:#c9c9c9;':'',
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
                detalleMetaEditar.main.storeCO_MUNICIPIO.load({
                    params: {co_estado:this.getValue()}
                })
            }
        }
});

this.storeCO_ESTADO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_estado,
	value:  this.OBJ.co_estado,
	objStore: this.storeCO_ESTADO
});

if(this.OBJ.co_estado){
	this.storeCO_MUNICIPIO.load({
		params: {co_estado:this.OBJ.co_estado},
		callback: function(){detalleMetaEditar.main.co_municipio.setValue(detalleMetaEditar.main.OBJ.co_municipio);}
	});
}

this.co_estado.on('beforeselect',function(cmb,record,index){
        	this.co_municipio.clearValue();
},this);

this.co_municipio = new Ext.form.ComboBox({
	fieldLabel:'MUNICIPIO',
	store: this.storeCO_MUNICIPIO,
	typeAhead: true,
	valueField: 'co_municipio',
	displayField:'tx_municipio',
	hiddenName:'co_municipio',
	//readOnly:(this.OBJ.co_municipio!='')?true:false,
	//style:(this.OBJ.co_municipio!='')?'background:#c9c9c9;':'',
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
                detalleMetaEditar.main.storeCO_PARROQUIA.load({
                    params: {co_municipio:this.getValue()}
                })
            }
        }
});

this.co_municipio.on('beforeselect',function(cmb,record,index){
        	this.co_parroquia.clearValue();
},this);

this.co_parroquia = new Ext.form.ComboBox({
	fieldLabel:'PARROQUIA',
	store: this.storeCO_PARROQUIA,
	typeAhead: true,
	valueField: 'co_parroquia',
	displayField:'tx_parroquia',
	hiddenName:'co_parroquia',
	//readOnly:(this.OBJ.co_parroquia!='')?true:false,
	//style:(this.OBJ.co_parroquia!='')?'background:#c9c9c9;':'',
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
	hiddenName:'co_partida',
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

this.storeCO_PARTIDA.load({
		params: {id_proyecto:this.OBJ.id_proyecto}
	});
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_partida,
	value:  this.OBJ.co_partida,
	objStore: this.storeCO_PARTIDA
});

this.co_fuente_financiamiento = new Ext.form.ComboBox({
	fieldLabel:'FUENTE DE FINANCIAMIENTO',
	store: this.storeCO_FUENTE_FINANCIAMIENTO,
	typeAhead: true,
	valueField: 'co_fuente_financiamiento',
	displayField:'tx_fuente_financiamiento',
	hiddenName:'co_fuente_financiamiento',
	//readOnly:(this.OBJ.co_fuente_financiamiento!='')?true:false,
	//style:(this.OBJ.co_fuente_financiamiento!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Fuente de Fianciamiento',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_fuente_financiamiento}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});

this.storeCO_FUENTE_FINANCIAMIENTO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_fuente_financiamiento,
	value:  this.OBJ.co_fuente_financiamiento,
	objStore: this.storeCO_FUENTE_FINANCIAMIENTO
});

this.mo_presupuesto = new Ext.form.NumberField({
	fieldLabel:'PRESUPUESTO BS.',
	name:'mo_presupuesto',
	value:this.OBJ.mo_presupuesto,
	allowBlank:false,
	width:200,
	minLength : 1,
	maxLength: 20,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 20},
	blankText: '0.00',
	//decimalPrecision: 2,
	allowNegative: false,
   	//style: 'text-align: right',
	emptyText: '0.00',
	decimalPrecision: 0,
	allowDecimals: false
});

this.guardar = new Ext.Button({
	text:'Agregar',
	iconCls: 'icon-guardar',
	handler:function(){
	if(detalleMetaEditar.main.formPanel_.form.isValid()){
		var e = new metaEditar.main.Registro({
			co_municipio:detalleMetaEditar.main.co_municipio.getValue(),
			tx_municipio:detalleMetaEditar.main.co_municipio.getRawValue(),
			co_parroquia:detalleMetaEditar.main.co_parroquia.getValue(),
	    		tx_parroquia:detalleMetaEditar.main.co_parroquia.getRawValue(),
			mo_presupuesto:detalleMetaEditar.main.mo_presupuesto.getValue(),
			co_partida:detalleMetaEditar.main.co_partida.getValue(),
			co_fuente_financiamiento:detalleMetaEditar.main.co_fuente_financiamiento.getValue(),
	    		tx_fuente_financiamiento:detalleMetaEditar.main.co_fuente_financiamiento.getRawValue()
		});
		var cant = metaEditar.main.store_lista.getCount();
			(cant==0)?0:metaEditar.main.store_lista.getCount()+1;

			metaEditar.main.store_lista.insert(cant, e);
			metaEditar.main.gridPanel_.getView().refresh();
			detalleMetaEditar.main.winformPanel_.close();
	}else{
		Ext.Msg.show({
			title:'Mensaje',
			msg: 'Debe llenar los campos requeridos',
			buttons: Ext.Msg.OK,
			animEl: document.body,
			icon: Ext.MessageBox.INFO
		});
	}
	}
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        detalleMetaEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	frame:false,
	border:false,
	width:600,
	autoHeight:true,
	autoScroll:true,
	labelWidth: 180,
	bodyStyle:'padding:10px;',
	items:[
		this.co_estado,
		this.co_municipio,
		this.co_parroquia,
		this.mo_presupuesto,
		this.co_partida,
		this.co_fuente_financiamiento
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Metas Financieras',
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
metaLista.main.mascara.hide();
},
getStoreCO_ESTADO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=11',
        root:'data',
        fields:[
            {name: 'co_estado'},{name: 'tx_estado'}
            ]
    });
    return this.store;
},
getStoreCO_MUNICIPIO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/metas/funcion.php?op=6',
        root:'data',
        fields:[
            {name: 'co_municipio'},{name: 'tx_municipio'}
            ]
    });
    return this.store;
},
getStoreCO_PARROQUIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/metas/funcion.php?op=7',
        root:'data',
        fields:[
            {name: 'co_parroquia'},{name: 'tx_parroquia'}
            ]
    });
    return this.store;
},
getStoreCO_PARTIDA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/metas/orm.php',
        root:'data',
	baseParams:{
		op:3,
		ae:this.OBJ.ae
	},
        fields:[
            {name: 'co_partida'}
            ]
    });
    return this.store;
},
getStoreCO_FUENTE_FINANCIAMIENTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/metas/funcion.php?op=5',
        root:'data',
        fields:[
            {name: 'co_fuente_financiamiento'},{name: 'tx_fuente_financiamiento'}
            ]
    });
    return this.store;
}
};
Ext.onReady(detalleMetaEditar.main.init, detalleMetaEditar.main);
</script>
