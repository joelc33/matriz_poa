<?php         

if($co_proyecto_vinculos!=''||$co_proyecto_vinculos!=null){
	$sql = "SELECT * FROM t32_proyecto_vinculos WHERE co_proyecto_vinculos=".$co_proyecto_vinculos;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data2 = json_encode(array(
			"co_proyecto_vinculos"     => trim($row["co_proyecto_vinculos"]),
			"id_proyecto"     => trim($row["id_proyecto"]),
			"co_objetivo_historico"     => trim($row["id_obj_historico"]),
			"co_objetivo_nacional"     => trim($row["id_obj_nacional"]),
			"co_objetivo_estrategico"     => trim($row["id_ob_estrategico"]),
			"co_objetivo_general"     => trim($row["id_obj_general"]),
			"co_area_estrategica"     => trim($row["co_area_estrategica"]),
			"co_ambito_zulia"     => trim($row["co_ambito_estado"]),
			"co_objetivo_zulia"     => trim($row["co_objetivo_estado"]),
			"co_macroproblema"     => trim($row["co_macroproblema"]),
			"co_nodo"     => trim($row["co_nodo"]),
		));
	}
}else{
	$data2 = json_encode(array(
		"co_proyecto_vinculos"     => "",
		"id_proyecto"     => "",
		"co_objetivo_historico"     => "",
		"co_objetivo_nacional"     => "",
		"co_objetivo_estrategico"     => "",
		"co_objetivo_general"     => "",
		"co_area_estrategica"     => "",
		"co_ambito_zulia"     => "",
		"co_objetivo_zulia"     => "",
		"co_macroproblema"     => "",
		"co_nodo"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("tabuladorDos");
tabuladorDos.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data2 ?>'});

//<Stores de fk>
this.storeCO_OBJETIVO_HISTORICO = this.getStoreCO_OBJETIVO_HISTORICO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_OBJETIVO_NACIONAL = this.getStoreCO_OBJETIVO_NACIONAL();
//<Stores de fk>
//<Stores de fk>
this.storeCO_OBJETIVO_ESTRATEGICO = this.getStoreCO_OBJETIVO_ESTRATEGICO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_OBJETIVO_GENERAL = this.getStoreCO_OBJETIVO_GENERAL();
//<Stores de fk>
//<Stores de fk>
this.storeCO_AREA_ESTRATEGICA = this.getStoreCO_AREA_ESTRATEGICA();
//<Stores de fk>
//<Stores de fk>
this.storeCO_AMBITO_ZULIA = this.getStoreCO_AMBITO_ZULIA();
//<Stores de fk>
//<Stores de fk>
this.storeCO_OBJETIVO_ZULIA = this.getStoreCO_OBJETIVO_ZULIA();
//<Stores de fk>
//<Stores de fk>
this.storeCO_MACROPROBLEMA = this.getStoreCO_MACROPROBLEMA();
//<Stores de fk>
//<Stores de fk>
this.storeCO_NODO = this.getStoreCO_NODO();
//<Stores de fk>

//<ClavePrimaria>
this.co_proyecto_vinculos = new Ext.form.Hidden({
	name:'co_proyecto_vinculos',
	value:this.OBJ.co_proyecto_vinculos
});
//</ClavePrimaria>

this.co_objetivo_historico = new Ext.form.ComboBox({
	fieldLabel:'2.1. OBJETIVO HISTÓRICO',
	store: this.storeCO_OBJETIVO_HISTORICO,
	typeAhead: true,
	valueField: 'co_objetivo_historico',
	displayField:'tx_objetivo_historico',
	hiddenName:'co_objetivo_historico',
	//readOnly:(this.OBJ.co_objetivo_historico!='')?true:false,
	//style:(this.OBJ.co_objetivo_historico!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Objetivo Historico',
	selectOnFocus: true,
	mode: 'local',
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_objetivo_historico}</div></div></tpl>'),
	resizable:true,
	//allowBlank:false,
	//listWidth: '800',
	listeners:{
            change: function(){
                tabuladorDos.main.storeCO_OBJETIVO_NACIONAL.load({
                    params: {co_objetivo_historico:this.getValue()}
                })
            }
        }
});

this.storeCO_OBJETIVO_HISTORICO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_objetivo_historico,
	value:  this.OBJ.co_objetivo_historico,
	objStore: this.storeCO_OBJETIVO_HISTORICO
});

if(this.OBJ.co_objetivo_historico){
	this.storeCO_OBJETIVO_NACIONAL.load({
		params: {co_objetivo_historico:this.OBJ.co_objetivo_historico},
		callback: function(){tabuladorDos.main.co_objetivo_nacional.setValue(tabuladorDos.main.OBJ.co_objetivo_nacional);}
	});
}

this.co_objetivo_historico.on('beforeselect',function(cmb,record,index){
        	this.co_objetivo_nacional.clearValue();
        	this.co_objetivo_estrategico.clearValue();
        	this.co_objetivo_general.clearValue();
},this);

this.co_objetivo_nacional = new Ext.form.ComboBox({
	fieldLabel:'2.2. OBJETIVO NACIONAL',
	store: this.storeCO_OBJETIVO_NACIONAL,
	typeAhead: true,
	valueField: 'co_objetivo_nacional',
	displayField:'tx_objetivo_nacional',
	hiddenName:'co_objetivo_nacional',
	//readOnly:(this.OBJ.co_objetivo_nacional!='')?true:false,
	//style:(this.OBJ.co_objetivo_nacional!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Objetivo Nacional',
	selectOnFocus: true,
	mode: 'local',
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_objetivo_nacional}</div></div></tpl>'),
	resizable:true,
	//allowBlank:false,
	//listWidth: '800',
	listeners:{
            change: function(){
                tabuladorDos.main.storeCO_OBJETIVO_ESTRATEGICO.load({
                    params: {co_objetivo_historico:tabuladorDos.main.co_objetivo_historico.getValue(),co_objetivo_nacional:this.getValue()}
                })
            }
        }
});

if(this.OBJ.co_objetivo_nacional){
	this.storeCO_OBJETIVO_ESTRATEGICO.load({
		params: {co_objetivo_historico:this.OBJ.co_objetivo_historico, co_objetivo_nacional:this.OBJ.co_objetivo_nacional},
		callback: function(){tabuladorDos.main.co_objetivo_estrategico.setValue(tabuladorDos.main.OBJ.co_objetivo_estrategico);}
	});
}

this.co_objetivo_nacional.on('beforeselect',function(cmb,record,index){
        	this.co_objetivo_estrategico.clearValue();
        	this.co_objetivo_general.clearValue();
},this);

this.co_objetivo_estrategico = new Ext.form.ComboBox({
	fieldLabel:'2.3. OBJETIVO ESTRATÉGICO',
	store: this.storeCO_OBJETIVO_ESTRATEGICO,
	typeAhead: true,
	valueField: 'co_objetivo_estrategico',
	displayField:'tx_objetivo_estrategico',
	hiddenName:'co_objetivo_estrategico',
	//readOnly:(this.OBJ.co_objetivo_estrategico!='')?true:false,
	//style:(this.OBJ.co_objetivo_estrategico!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Objetivo Estrategico',
	selectOnFocus: true,
	mode: 'local',
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_objetivo_estrategico}</div></div></tpl>'),
	resizable:true,
	//allowBlank:false,
	//listWidth: '800',
	listeners:{
            change: function(){
                tabuladorDos.main.storeCO_OBJETIVO_GENERAL.load({
                    params: {co_objetivo_historico:tabuladorDos.main.co_objetivo_historico.getValue(),co_objetivo_nacional:tabuladorDos.main.co_objetivo_nacional.getValue(),co_objetivo_estrategico:this.getValue()}
                })
            }
        }
});

if(this.OBJ.co_objetivo_estrategico){
	this.storeCO_OBJETIVO_GENERAL.load({
		params: {co_objetivo_historico:this.OBJ.co_objetivo_historico, co_objetivo_nacional:this.OBJ.co_objetivo_nacional, co_objetivo_estrategico:this.OBJ.co_objetivo_estrategico},
		callback: function(){tabuladorDos.main.co_objetivo_general.setValue(tabuladorDos.main.OBJ.co_objetivo_general);}
	});
}

this.co_objetivo_estrategico.on('beforeselect',function(cmb,record,index){
        	this.co_objetivo_general.clearValue();
},this);

this.co_objetivo_general = new Ext.form.ComboBox({
	fieldLabel:'2.4. OBJETIVO GENERAL',
	store: this.storeCO_OBJETIVO_GENERAL,
	typeAhead: true,
	valueField: 'co_objetivo_general',
	displayField:'tx_objetivo_general',
	hiddenName:'co_objetivo_general',
	//readOnly:(this.OBJ.co_objetivo_general!='')?true:false,
	//style:(this.OBJ.co_objetivo_general!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Objetivo General',
	selectOnFocus: true,
	mode: 'local',
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_objetivo_general}</div></div></tpl>'),
	resizable:true,
	//listWidth: '800',
	//allowBlank:false
});

this.co_area_estrategica = new Ext.form.ComboBox({
	fieldLabel:'2.5.1. AREA ESTRATEGICA',
	store: this.storeCO_AREA_ESTRATEGICA,
	typeAhead: true,
	valueField: 'co_area_estrategica',
	displayField:'tx_area_estrategica',
	hiddenName:'co_area_estrategica',
	//readOnly:(this.OBJ.co_area_estrategica!='')?true:false,
	//style:(this.OBJ.co_area_estrategica!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Area Estrategica',
	selectOnFocus: true,
	mode: 'local',
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_area_estrategica}</div></div></tpl>'),
	resizable:true,
	style:'background-color:#c9c9c9;',
	//allowBlank:false,
	//listWidth: '800',
	listeners:{
            change: function(){
                tabuladorDos.main.storeCO_AMBITO_ZULIA.load({
                    params: {co_area_estrategica:this.getValue()}
                })
            }
        }
});

//tabuladorDos.main.co_area_estrategica.disable();

this.storeCO_AREA_ESTRATEGICA.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_area_estrategica,
	value:  this.OBJ.co_area_estrategica,
	objStore: this.storeCO_AREA_ESTRATEGICA
});

if(this.OBJ.co_area_estrategica){
	this.storeCO_AMBITO_ZULIA.load({
		params: {co_area_estrategica:this.OBJ.co_area_estrategica},
		callback: function(){tabuladorDos.main.co_ambito_zulia.setValue(tabuladorDos.main.OBJ.co_ambito_zulia);}
	});
}

this.co_area_estrategica.on('beforeselect',function(cmb,record,index){
        	this.co_ambito_zulia.clearValue();
        	this.co_objetivo_zulia.clearValue();
        	this.co_macroproblema.clearValue();
        	this.co_nodo.clearValue();
},this);

this.co_ambito_zulia = new Ext.form.ComboBox({
	fieldLabel:'2.5.2. AMBITO',
	store: this.storeCO_AMBITO_ZULIA,
	typeAhead: true,
	valueField: 'co_ambito_zulia',
	displayField:'tx_ambito_zulia',
	hiddenName:'co_ambito_zulia',
	//readOnly:(this.OBJ.co_ambito_zulia!='')?true:false,
	//style:(this.OBJ.co_ambito_zulia!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Ambito',
	selectOnFocus: true,
	mode: 'local',
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_ambito_zulia}</div></div></tpl>'),
	resizable:true,
	style:'background-color:#c9c9c9;',
	//allowBlank:false,
	//listWidth: '800',
	listeners:{
            change: function(){
                tabuladorDos.main.storeCO_OBJETIVO_ZULIA.load({
                    params: {co_ambito_zulia:this.getValue()}
                }),
                tabuladorDos.main.storeCO_MACROPROBLEMA.load({
                    params: {co_ambito_zulia:this.getValue()}
                }),
                tabuladorDos.main.storeCO_NODO.load({
                    params: {co_ambito_zulia:this.getValue()}
                })                        
            }
        }
});

//tabuladorDos.main.co_ambito_zulia.disable();

if(this.OBJ.co_ambito_zulia){
	this.storeCO_OBJETIVO_ZULIA.load({
		params: {co_ambito_zulia:this.OBJ.co_ambito_zulia},
		callback: function(){tabuladorDos.main.co_objetivo_zulia.setValue(tabuladorDos.main.OBJ.co_objetivo_zulia);}
	});
}

if(this.OBJ.co_ambito_zulia){
	this.storeCO_MACROPROBLEMA.load({
		params: {co_ambito_zulia:this.OBJ.co_ambito_zulia},
		callback: function(){tabuladorDos.main.co_macroproblema.setValue(tabuladorDos.main.OBJ.co_macroproblema);}
	});
}

if(this.OBJ.co_ambito_zulia){
	this.storeCO_NODO.load({
		params: {co_ambito_zulia:this.OBJ.co_ambito_zulia},
		callback: function(){tabuladorDos.main.co_nodo.setValue(tabuladorDos.main.OBJ.co_nodo);}
	});
}

this.co_ambito_zulia.on('beforeselect',function(cmb,record,index){
        	this.co_objetivo_zulia.clearValue();
        	this.co_macroproblema.clearValue();
        	this.co_nodo.clearValue();
},this);

this.co_objetivo_zulia = new Ext.form.ComboBox({
	fieldLabel:'2.5.4. OBJETIVO ESTRATEGICO',
	store: this.storeCO_OBJETIVO_ZULIA,
	typeAhead: true,
	valueField: 'co_objetivo_zulia',
	displayField:'tx_objetivo_zulia',
	hiddenName:'co_objetivo_zulia',
	//readOnly:(this.OBJ.co_objetivo_zulia!='')?true:false,
	//style:(this.OBJ.co_objetivo_zulia!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Objetivo',
	selectOnFocus: true,
	mode: 'local',
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_objetivo_zulia}</div></div></tpl>'),
	resizable:true,
	style:'background-color:#c9c9c9;',
	//listWidth: '800',
	//allowBlank:false
});

//tabuladorDos.main.co_objetivo_zulia.disable();

this.co_macroproblema = new Ext.form.ComboBox({
	fieldLabel:'2.5.5. PROBLEMA',
	store: this.storeCO_MACROPROBLEMA,
	typeAhead: true,
	valueField: 'co_macroproblema',
	displayField:'tx_macroproblema',
	hiddenName:'co_macroproblema',
	//readOnly:(this.OBJ.co_macroproblema!='')?true:false,
	//style:(this.OBJ.co_macroproblema!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Macro problema',
	selectOnFocus: true,
	mode: 'local',
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_macroproblema}</div></div></tpl>'),
	resizable:true,
	style:'background-color:#c9c9c9;',
	//allowBlank:false,
	//listWidth: '800',
//	listeners:{
//            change: function(){
//                tabuladorDos.main.storeCO_NODO.load({
//                    params: {co_macroproblema:this.getValue()}
//                })
//            }
//        }
});

/*this.storeCO_MACROPROBLEMA.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_macroproblema,
	value:  this.OBJ.co_macroproblema,
	objStore: this.storeCO_MACROPROBLEMA
});*/

//tabuladorDos.main.co_macroproblema.disable();

//if(this.OBJ.co_macroproblema){
//	this.storeCO_NODO.load({
//		params: {co_macroproblema:this.OBJ.co_macroproblema},
//		callback: function(){tabuladorDos.main.co_nodo.setValue(tabuladorDos.main.OBJ.co_nodo);}
//	});
//}

//this.co_macroproblema.on('beforeselect',function(cmb,record,index){
//        	this.co_nodo.clearValue();
//},this);


this.co_nodo = new Ext.form.ComboBox({
	fieldLabel:'2.5.3. LÍNEA MATRIZ',
	store: this.storeCO_NODO,
	typeAhead: true,
	valueField: 'co_nodo',
	displayField:'tx_nodo',
	hiddenName:'co_nodo[]',
	//readOnly:(this.OBJ.co_macroproblema!='')?true:false,
	//style:(this.OBJ.co_macroproblema!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Línea Matriz',
	selectOnFocus: true,
	mode: 'local',
	width:500,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_nodo}</div></div></tpl>'),
	resizable:true,
	style:'background-color:#c9c9c9;',
});

//this.co_nodo = new Ext.ux.form.SuperBoxSelect({
//	fieldLabel:'2.5.5. NUDOS CRITICOS',
//	store: this.storeCO_NODO,
//	typeAhead: true,
//	xtype:'superboxselect',
//	allowQueryAll : false,
//	valueField: 'co_nodo',
//	displayField:'tx_nodo',
//	hiddenName:'co_nodo[]',
//	//readOnly:(this.OBJ.co_nodo!='')?true:false,
//	//style:(this.OBJ.co_nodo!='')?'background:#c9c9c9;':'',
//	forceSelection:true,
//	resizable:true,
//	triggerAction: 'all',
//	emptyText:'Seleccione Nudo',
//	selectOnFocus: true,
//	mode: 'local',
//	width:500,
//	readOnly:<?php echo $deshabilitado ?>,
//	itemSelector: 'div.search-item',
//	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_nodo}</div></div></tpl>'),
//	hideOnSelect:false,
//	resizable:true,
//	style:'background-color:#c9c9c9;',
//	//listWidth: '750',
//	//allowBlank:false
//});

//tabuladorDos.main.co_nodo.disable();

this.fieldset2 = new Ext.form.FieldSet({
    	title: '2.5. OBJETIVOS DEL PLAN DE DESARROLLO DEL ZULIA (LÍNEA MATRIZ 2022-2025)',
	autoWidth:true,
        items:[
		this.co_area_estrategica,
		this.co_ambito_zulia,
                this.co_nodo,
		this.co_objetivo_zulia,
		this.co_macroproblema

		]
});

this.fieldset1 = new Ext.form.FieldSet({
    	title: '2. OBJETIVOS DEL PLAN DE LA PATRIA',
	autoWidth:true,
        items:[
		this.co_proyecto_vinculos,
		this.co_objetivo_historico,
		this.co_objetivo_nacional,
		this.co_objetivo_estrategico,
		this.co_objetivo_general,
		this.fieldset2
		]
});

this.panelDatos = new Ext.Panel({
    title: '2. VINCULACIÓN CON LOS PLANES',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.fieldset1
	]
});

},
getStoreCO_OBJETIVO_HISTORICO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=6',
        root:'data',
        fields:[
		{name: 'co_objetivo_historico'},
		{name: 'tx_objetivo_historico'}
            ]
    });
    return this.store;
},
getStoreCO_OBJETIVO_NACIONAL:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=7',
        root:'data',
        fields:[
		{name: 'co_objetivo_nacional'},
		{name: 'tx_objetivo_nacional'}
            ]
    });
    return this.store;
},
getStoreCO_OBJETIVO_ESTRATEGICO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=8',
        root:'data',
        fields:[
            {name: 'co_objetivo_estrategico'},{name: 'tx_objetivo_estrategico'}
            ]
    });
    return this.store;
},
getStoreCO_OBJETIVO_GENERAL:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=9',
        root:'data',
        fields:[
            {name: 'co_objetivo_general'},{name: 'tx_objetivo_general'}
            ]
    });
    return this.store;
},
getStoreCO_AREA_ESTRATEGICA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=26',
        root:'data',
        fields:[
            {name: 'co_area_estrategica'},{name: 'tx_area_estrategica'}
            ]
    });
    return this.store;
},
getStoreCO_AMBITO_ZULIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=22',
        root:'data',
        fields:[
            {name: 'co_ambito_zulia'},{name: 'tx_ambito_zulia'}
            ]
    });
    return this.store;
},
getStoreCO_OBJETIVO_ZULIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=23',
        root:'data',
        fields:[
            {name: 'co_objetivo_zulia'},{name: 'tx_objetivo_zulia'}
            ]
    });
    return this.store;
},
getStoreCO_MACROPROBLEMA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=24',
        root:'data',
        fields:[
            {name: 'co_macroproblema'},{name: 'tx_macroproblema'}
            ]
    });
    return this.store;
},
getStoreCO_NODO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=25',
        root:'data',
        fields:[
            {name: 'co_nodo'},{name: 'tx_nodo'}
            ]
    });
    return this.store;
}
};
Ext.onReady(tabuladorDos.main.init, tabuladorDos.main);
</script>
