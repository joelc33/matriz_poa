<?php      
	$data = json_encode(array(
		"co_proyecto_localizacion_comunal"     => "",
		"id_proyecto"     => $_POST['id_proyecto'],
		"tx_codigo_comuna"     => "",
		"tx_agregacion_comunal"     => "",
		"co_estado"     => 23,
		"co_municipio"     => "",
		"co_parroquia"     => "",
	));
?>
<script type="text/javascript">
Ext.ns("comunaEditar");
comunaEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

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
	value:27
});

this.tx_codigo_comuna = new Ext.form.TextField({
	fieldLabel:'CÓDIGO DE COMUNA',
	name:'tx_codigo_comuna',
	value:this.OBJ.tx_codigo_comuna,
	width:200,
	allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_agregacion_comunal = new Ext.form.TextField({
	fieldLabel:'AGREGACIÓN COMUNAL',
	name:'tx_agregacion_comunal',
	value:this.OBJ.tx_agregacion_comunal,
	width:300,
	allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

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
                comunaEditar.main.storeCO_MUNICIPIO.load({
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
		callback: function(){comunaEditar.main.co_municipio.setValue(comunaEditar.main.OBJ.co_municipio);}
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
                comunaEditar.main.storeCO_PARROQUIA.load({
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
        if(!comunaEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        comunaEditar.main.formPanel_.getForm().submit({
	    /*params : {
	    tx_estado:comunaEditar.main.co_estado.getRawValue(),
	    tx_municipio:comunaEditar.main.co_municipio.getRawValue(),
	    tx_parroquia:comunaEditar.main.co_parroquia.getRawValue()
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
                 tabuladorTres.main.store_lista_comuna.load();
                 comunaEditar.main.winformPanel_.hide();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        comunaEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	labelWidth: 150,
	autoHeight:true,  
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this.id_proyecto,
		this.tx_codigo_comuna,
		this.tx_agregacion_comunal,
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
Ext.onReady(comunaEditar.main.init, comunaEditar.main);
</script>
