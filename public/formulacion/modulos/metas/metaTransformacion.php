<?php
	$data = json_encode(array(
		"id_accion_centralizada"     => $_GET['id_accion_centralizada'],
		"co_ac_acc_espec"     => $_GET['co_ac_acc_espec'],
                "id_tab_t47_ac_accion_especifica"     => $_GET['id_tab_t47_ac_accion_especifica'],
		"co_estado"     => 23,
		"co_municipio"     => 11,
		"co_parroquia"     => "",
	));
?>
<script type="text/javascript">
Ext.ns("detalleMetaEditar");
detalleMetaEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

this.Registro;

//<Stores de fk>
this.storeCO_TRANSFORMACIONES = this.getStoreTRANSFORMACIONES();

this.storeCO_ALINEACION = this.getStoreALINEACION();

this.storeCO_IMPULSO = this.getStoreIMPULSO();

this.storeCO_FOCO = this.getStoreFOCO();
//<Stores de fk>

//<ClavePrimaria>
//</ClavePrimaria>

this.co_transformaciones = new Ext.form.ComboBox({
	fieldLabel:'Linea de Transformacion',
	store: this.storeCO_TRANSFORMACIONES,
	typeAhead: true,
	valueField: 'co_transformaciones',
	displayField:'tx_transformacion',
	hiddenName:'co_transformaciones',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione linea transformacion',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	allowBlank:false,
	listeners:{
            change: function(){
                detalleMetaEditar.main.storeCO_ALINEACION.load({
                    params: {co_transformaciones:this.getValue()}
                })
            },
        beforeselect: function() {
            detalleMetaEditar.main.co_alineacion.clearValue();
            detalleMetaEditar.main.co_impulso.clearValue();
            detalleMetaEditar.main.co_foco.clearValue();
        }
        }
});

this.co_alineacion = new Ext.form.ComboBox({
	fieldLabel:'Eje de Alineacion',
	store: this.storeCO_ALINEACION,
	typeAhead: true,
	valueField: 'co_alineacion',
	displayField:'tx_eje_alineacion',
	hiddenName:'co_alineacion',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione eje de alineacion',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	allowBlank:false,
	listeners:{
            change: function(){
                detalleMetaEditar.main.storeCO_IMPULSO.load({
                    params: {co_alineacion:this.getValue()}
                })
            },
        beforeselect: function() {
            detalleMetaEditar.main.co_impulso.clearValue();
            detalleMetaEditar.main.co_foco.clearValue();
        }
        }
});

this.co_impulso = new Ext.form.ComboBox({
	fieldLabel:'Linea de Impulso',
	store: this.storeCO_IMPULSO,
	typeAhead: true,
	valueField: 'co_impulso',
	displayField:'tx_linea_impulso',
	hiddenName:'co_impulso',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione linea de impulso',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	allowBlank:false,
	listeners:{
            change: function(){
                detalleMetaEditar.main.storeCO_FOCO.load({
                    params: {co_impulso:this.getValue()}
                })
            },
        beforeselect: function() {
            detalleMetaEditar.main.co_foco.clearValue();
        }
        }
});

this.co_foco = new Ext.form.ComboBox({
	fieldLabel:'Foco de Accion',
	store: this.storeCO_FOCO,
	typeAhead: true,
	valueField: 'co_foco',
	displayField:'tx_foco_accion',
	hiddenName:'co_foco',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione foco de accion',
	selectOnFocus: true,
	mode: 'local',
	width:300,
	allowBlank:false
});

this.storeCO_TRANSFORMACIONES.load();

this.guardar = new Ext.Button({
	text:'Agregar',
	iconCls: 'icon-guardar',
	handler:function(){
	if(detalleMetaEditar.main.formPanel_.form.isValid()){
            
            
            var index = metaEditar.main.store_lista_transformacion.findBy(function (user, id) {
                console.log(user.data.id_foco_accion);

                if (user.data.id_foco_accion === detalleMetaEditar.main.co_foco.getValue()) return true;
                else return false;
            });            
            console.log(index);
             if (index == -1) {
                 

                
		var e = new metaEditar.main.Registro_transformacion({
			id_transformacion:detalleMetaEditar.main.co_transformaciones.getValue(),
			id_eje_alineacion:detalleMetaEditar.main.co_alineacion.getValue(),
			id_linea_impulso:detalleMetaEditar.main.co_impulso.getValue(),
	    		id_foco_accion:detalleMetaEditar.main.co_foco.getValue(),
			tx_transformacion:detalleMetaEditar.main.co_transformaciones.getRawValue(),
			tx_eje_alineacion:detalleMetaEditar.main.co_alineacion.getRawValue(),
			tx_linea_impulso:detalleMetaEditar.main.co_impulso.getRawValue(),
	    		tx_foco_accion:detalleMetaEditar.main.co_foco.getRawValue()
		});                
                
 		var cant = metaEditar.main.store_lista_transformacion.getCount();
			(cant==0)?0:metaEditar.main.store_lista_transformacion.getCount()+1;

			metaEditar.main.store_lista_transformacion.insert(cant, e);
			metaEditar.main.gridPanelTrans_.getView().refresh();
			detalleMetaEditar.main.winformPanel_.close();
                        
                       
            }else{
                
 		Ext.Msg.show({
			title:'Mensaje',
			msg: 'El foco de accion ya se encuentra en la lista',
			buttons: Ext.Msg.OK,
			animEl: document.body,
			icon: Ext.MessageBox.INFO
		});               
    }

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
		this.co_transformaciones,
                this.co_alineacion,
                this.co_impulso,
                this.co_foco
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: 7 Grandes Transformaciones 2025-2031',
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
getStoreTRANSFORMACIONES:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/metas/funcion.php?op=16',
        root:'data',
        fields:[
            {name: 'co_transformaciones'},{name: 'tx_transformacion'}
            ]
    });
    return this.store;
},
getStoreALINEACION:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/metas/funcion.php?op=17',
        root:'data',
        fields:[
            {name: 'co_alineacion'},{name: 'tx_eje_alineacion'}
            ]
    });
    return this.store;
},
getStoreIMPULSO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/metas/funcion.php?op=18',
        root:'data',
        fields:[
            {name: 'co_impulso'},{name: 'tx_linea_impulso'}
            ]
    });
    return this.store;
},
getStoreFOCO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/metas/funcion.php?op=19',
        root:'data',
        fields:[
            {name: 'co_foco'},{name: 'tx_foco_accion'}
            ]
    });
    return this.store;
}
};
Ext.onReady(detalleMetaEditar.main.init, detalleMetaEditar.main);
</script>
