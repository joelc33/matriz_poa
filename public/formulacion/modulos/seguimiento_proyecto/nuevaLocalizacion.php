<?php        
	$data = json_encode(array(
		"id_proyecto"     => $_POST['id_proyecto'],
		"co_estado"     => 23,
		"co_municipio"     => "",
		"co_parroquia"     => "",
		"co_pais"     => 1,
	));
?>
<script type="text/javascript">
Ext.ns("localizacionEditar");
localizacionEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeCO_PAIS = this.getStoreCO_PAIS();
//<Stores de fk>
//<Stores de fk>
this.storeCO_ESTADO = this.getStoreCO_ESTADO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_MUNICIPIO = this.getStoreCO_MUNICIPIO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_PARROQUIA = this.getStoreCO_PARROQUIA();
//<Stores de fk>

//<ClavePrimaria>
this.id_proyecto = new Ext.form.Hidden({
    name:'id_proyecto',
    value:this.OBJ.id_proyecto});
//</ClavePrimaria>
this.op = new Ext.form.Hidden({
	name:'op',
	value:25
});

this.co_pais = new Ext.form.ComboBox({
	fieldLabel:'PAIS',
	store: this.storeCO_PAIS,
	typeAhead: true,
	valueField: 'co_pais',
	displayField:'tx_pais',
	hiddenName:'co_pais',
	//readOnly:(this.OBJ.co_pais!='')?true:false,
	//style:(this.OBJ.co_pais!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Pais',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	resizable:true,
	allowBlank:false,
	listeners:{
            change: function(){
                localizacionEditar.main.storeCO_ESTADO.load({
                    params: {co_pais:this.getValue()}
                })
            }
        }
});

this.storeCO_PAIS.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_pais,
	value:  this.OBJ.co_pais,
	objStore: this.storeCO_PAIS
});

if(this.OBJ.co_pais){
	this.storeCO_ESTADO.load({
		params: {co_pais:this.OBJ.co_pais},
		callback: function(){localizacionEditar.main.co_estado.setValue(localizacionEditar.main.OBJ.co_estado);}
	});
}

this.co_pais.on('beforeselect',function(cmb,record,index){
        	this.co_estado.clearValue();
        	this.co_municipio.clearValue();
        	this.co_parroquia.clearValue();
},this);

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
                localizacionEditar.main.storeCO_MUNICIPIO.load({
                    params: {co_estado:this.getValue()}
                })
            }
        }
});

if(this.OBJ.co_estado){
	this.storeCO_MUNICIPIO.load({
		params: {co_estado:this.OBJ.co_estado},
		callback: function(){localizacionEditar.main.co_municipio.setValue(localizacionEditar.main.OBJ.co_municipio);}
	});
}

this.co_estado.on('beforeselect',function(cmb,record,index){
        	this.co_municipio.clearValue();
        	this.co_parroquia.clearValue();
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
                localizacionEditar.main.storeCO_PARROQUIA.load({
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

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!localizacionEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        localizacionEditar.main.formPanel_.getForm().submit({
	    /*params : {
	    tx_estado:localizacionEditar.main.co_estado.getRawValue(),
	    tx_municipio:localizacionEditar.main.co_municipio.getRawValue(),
	    tx_parroquia:localizacionEditar.main.co_parroquia.getRawValue(),
	    tx_pais:localizacionEditar.main.co_pais.getRawValue()
	    },*/
            method:'POST',
            url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
            waitMsg: 'Enviando datos, por favor espere..',
            waitTitle:'Enviando',
            failure: function(form, action) {
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
                 tabuladorTres.main.store_lista_ubicacion.load();
                 localizacionEditar.main.winformPanel_.hide();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        localizacionEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	autoHeight:true,  
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this.id_proyecto,
		this.co_pais,
		this.co_estado,
		this.co_municipio,
		this.co_parroquia,
		this.op
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Ubicacion',
    modal:true,
    constrain:true,border:false,
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
tabuladorTres.main.mascara.hide();
},
getStoreCO_PAIS:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 21
	},
        fields:[
            {name: 'co_pais'},{name: 'tx_pais'}
            ]
    });
    return this.store;
},
getStoreCO_ESTADO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 22
	},
        fields:[
            {name: 'co_estado'},{name: 'tx_estado'}
            ]
    });
    return this.store;
},
getStoreCO_MUNICIPIO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 23
	},
        fields:[
            {name: 'co_municipio'},{name: 'tx_municipio'}
            ]
    });
    return this.store;
},
getStoreCO_PARROQUIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 24
	},
        fields:[
            {name: 'co_parroquia'},{name: 'tx_parroquia'}
            ]
    });
    return this.store;
}
};
Ext.onReady(localizacionEditar.main.init, localizacionEditar.main);
</script>
