<?php
if($co_proyecto_financiamiento!=''||$co_proyecto_financiamiento!=null){
	$sql = "SELECT * FROM t63_proyecto_financiamiento WHERE co_proyecto_financiamiento=".$co_proyecto_financiamiento;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data9 = json_encode(array(
			"co_proyecto_financiamiento"     => trim($row["co_proyecto_financiamiento"]),
			"id_proyecto"     => trim($row["id_proyecto"]),
			"in_financiamiento"     => trim($row["in_financiamiento"]),
			"in_tipo_financiamiento"     => trim($row["in_tipo_financiamiento"]),
			"mo_parcial"     => trim($row["mo_parcial"]),
			"co_tipo_fondo"     => trim($row["co_tipo_fondo"]),
			"tx_justificacion"     => trim($row["tx_justificacion"]),
			"mo_financiar"     => trim($row["mo_financiar"]),
		));
	}
}else{
	$data9 = json_encode(array(
		"co_proyecto_financiamiento"     => "",
		"id_proyecto"     => $id_proyecto,
		"in_financiamiento"     => "",
		"in_tipo_financiamiento"     => "",
		"mo_parcial"     => "",
		"co_tipo_fondo"     => "",
		"tx_justificacion"     => "",
		"mo_financiar"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("tabuladorNueveDos");
tabuladorNueveDos.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data9 ?>'});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista_final = this.getListaFinal();

Ext.ux.grid.GroupSummary.Calculations['moFondo'] = function(v, record, field){
	v+=parseFloat(record.data.mo_fondo);
	return v;
};
this.summary = new Ext.ux.grid.GroupSummary();

//Grid principal
this.gridPanelFinal_ = new Ext.grid.GridPanel({
    store: this.store_lista_final,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_distribucion',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_distribucion'},
    {header: 'RECURSOS SOLICITADOS', width:300,  menuDisabled:true, sortable: true, summaryRenderer: function(v, params, data){return '<b>SUB-TOTAL</b>';}, dataIndex: 'tx_tipo_fondo'},
    {header: 'MONTO PROGRAMADO DEL EJERCICIO A FORMULAR', width:300,  menuDisabled:true, sortable: true, summaryType: 'moFondo', renderer: formatoNumero, dataIndex: 'mo_fondo'},
    {header: 'RECURSOS SOLICITADOS', summaryType: 'sum',summaryRenderer: function(v, params, data){return 'Total';},autoWidth: true, sortable: true,groupable: false,  dataIndex: 'tx_tipo_recurso'},
    ],
    view: new Ext.grid.GroupingView({
        groupTextTpl: '{text}',
        forceFit: true,
        showGroupName: false,
        enableNoGroups: false,
	enableGroupingMenu: false,
        hideGroupedColumn: true
    }),
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    plugins: this.summary,
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista_final,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.panelDatos92 = new Ext.Panel({
    title: '9.2. ASIGNACIÓN POR FUENTE DE FINANCIAMIENTO EN BOLIVARES (DESEMBOLSOS) Bs.',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.gridPanelFinal_
	]
});

//Cargar el grid
this.store_lista_final.baseParams.paginar = 'si';
this.store_lista_final.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista_final.load();
this.store_lista_final.on('beforeload',function(){
panel_detalle.collapse();
});
},
getListaFinal: function(){
this.Store = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url:'formulacion/modulos/accionDistribucion/funcion.php?op=7',
            method: 'POST'
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'total'
        },
        [
	    {name: 'co_proyecto_distribucion'},
	    {name: 'tx_codigo_recurso'},
	    {name: 'id_proyecto'},
	    {name: 'tx_tipo_fondo'},
	    {name: 'mo_fondo'},
	    {name: 'edo_reg'},
	    {name: 'tx_tipo_recurso'},
        ]),
        sortInfo:{
            field: 'tx_codigo_recurso',
            direction: "ASC"
        },
        groupField:'tx_tipo_recurso'

});
return this.Store;
}
};
Ext.onReady(tabuladorNueveDos.main.init, tabuladorNueveDos.main);
</script>
<script type="text/javascript">
Ext.ns("tabuladorNueve");
tabuladorNueve.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data9 ?>'});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista_financiamiento = this.getListaFinanciamiento();

//<Stores de fk>
this.storeCO_TIPO_FONDO = this.getStoreCO_TIPO_FONDO();
//<Stores de fk>

//<ClavePrimaria>
this.co_proyecto_financiamiento = new Ext.form.Hidden({
	name:'co_proyecto_financiamiento',
	value:this.OBJ.co_proyecto_financiamiento
});
//</ClavePrimaria>

this.in_financiamiento = new Ext.form.ComboBox({
	fieldLabel:'9.1.1.1. Cuenta con Financiamiento?',
	typeAhead: true,
	valueField: 'in_financiamiento',
	displayField:'in_financiamiento',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	//emptyText:'Seleccione...',
	selectOnFocus: true,
	mode: 'local',
	width:100,
	readOnly:<?php echo $deshabilitado ?>,
	resizable:true,
	//allowBlank:false,
	name:'in_financiamiento',
	value:this.OBJ.in_financiamiento,
        store:	new Ext.data.SimpleStore({
        	fields : ['in_financiamiento'],
        	data : [['SI'],['NO']]
        })
});

this.in_financiamiento.on('select',function(cmb,record,index){
        if(tabuladorNueve.main.in_financiamiento.getValue()=='NO'){
		this.co_tipo_fondo.enable();
        }else{
		this.co_tipo_fondo.disable();
		this.co_tipo_fondo.clearValue();
	}
},this);

this.in_tipo_financiamiento = new Ext.form.ComboBox({
	fieldLabel:'9.1.1.2. El Financiamiento es Total o Parcial? ',
	typeAhead: true,
	valueField: 'in_tipo_financiamiento',
	displayField:'in_tipo_financiamiento',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	//emptyText:'Seleccione...',
	selectOnFocus: true,
	mode: 'local',
	width:100,
	readOnly:<?php echo $deshabilitado ?>,
	resizable:true,
	//allowBlank:false,
	name:'in_tipo_financiamiento',
	value:this.OBJ.in_tipo_financiamiento,
        store:	new Ext.data.SimpleStore({
        	fields : ['in_tipo_financiamiento'],
        	data : [['TOTAL'],['PARCIAL']]
        })
});

this.in_tipo_financiamiento.on('select',function(cmb,record,index){
        if(tabuladorNueve.main.in_tipo_financiamiento.getValue()=='PARCIAL'){
		this.mo_parcial.enable();
        }else{
		this.mo_parcial.disable();
		this.mo_parcial.clearValue();
	}
},this);

this.mo_parcial = new Ext.form.NumberField({
	fieldLabel:'Si es Parcial - Indique el Monto Financiado Bs.',
	name:'mo_parcial',
	value:this.OBJ.mo_parcial,
	//allowBlank:false,
	width:200,
	readOnly:<?php echo $deshabilitado ?>,
	minLength : 1,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	blankText: '0.00',
	decimalPrecision: 2,
	allowNegative: false,
   	//style: 'text-align: right',
	emptyText: '0.00',
	decimalPrecision: 0,
	allowDecimals: false
});

this.mo_parcial.disable();

if(tabuladorNueve.main.in_tipo_financiamiento.getValue()=='PARCIAL'){
	this.mo_parcial.enable();
}

this.co_tipo_fondo = new Ext.form.ComboBox({
	fieldLabel:'9.1.1.3.  Si la respuesta es NO o carece parcialmente de financiamiento, ¿Cuál fuente propone para financiar el proyecto?',
	store: this.storeCO_TIPO_FONDO,
	typeAhead: true,
	valueField: 'co_tipo_fondo',
	displayField:'tx_tipo_fondo',
	hiddenName:'co_tipo_fondo',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Recurso...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	readOnly:<?php echo $deshabilitado ?>,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_tipo_fondo}</div></div></tpl>'),
	//listWidth:'600',
	resizable:true,
	//allowBlank:false
});
this.storeCO_TIPO_FONDO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_tipo_fondo,
	value:  this.OBJ.co_tipo_fondo,
	objStore: this.storeCO_TIPO_FONDO
});

this.co_tipo_fondo.disable();

if(tabuladorNueve.main.in_financiamiento.getValue()=='SI'){
	this.co_tipo_fondo.enable();
}

this.tx_justificacion = new Ext.form.TextArea({
	fieldLabel:'9.1.1.4. Justifique su propuesta de financiamiento',
	name:'tx_justificacion',
	value:this.OBJ.tx_justificacion,
	//allowBlank:false,
	width:400,
	readOnly:<?php echo $deshabilitado ?>,
	height:100,
	maxLength: 200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.mo_financiar = new Ext.form.NumberField({
	fieldLabel:'9.1.1.5. Espeficique  Monto a Financiar',
	name:'mo_financiar',
	value:this.OBJ.mo_financiar,
	//allowBlank:false,
	width:200,
	readOnly:<?php echo $deshabilitado ?>,
	minLength : 1,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	blankText: '0.00',
	decimalPrecision: 2,
	allowNegative: false,
   	//style: 'text-align: right',
	emptyText: '0.00',
	decimalPrecision: 0,
	allowDecimals: false
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:true,
    	title: '9.1.1. Indicar lo siguiente del Año a formular Año <?php echo $_SESSION["ejercicio_fiscal"]; ?>',
        items:[
		this.co_proyecto_financiamiento,
		this.in_financiamiento,
		this.in_tipo_financiamiento,
		this.mo_parcial,
		this.co_tipo_fondo,
		this.tx_justificacion,
		this.mo_financiar
		]
});

//Editar un registro
this.editarMonto= new Ext.Button({
    text:'Editar Monto',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = tabuladorNueve.main.gridPanelRecurso_.getSelectionModel().getSelected().get('co_proyecto_distribucion');
	tabuladorNueve.main.mascara.show();
        this.msg = Ext.get('formulario_ubicacion');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/accionDistribucion/editarMonto.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.editarMonto.disable();

Ext.ux.grid.GroupSummary.Calculations['moFondo'] = function(v, record, field){
	v+=parseFloat(record.data.mo_fondo);
	return v;
};
this.summary = new Ext.ux.grid.GroupSummary();

//Grid principal
var PanelEditable = Ext.grid;
//Grid principal
//this.gridPanelRecurso_ = new Ext.grid.GridPanel({
this.gridPanelRecurso_ = new PanelEditable.EditorGridPanel({
    title: 'RECURSOS (Doble Click para Editar)',
    store: this.store_lista_financiamiento,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    /*tbar:[
        this.editarMonto
    ],*/
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_distribucion',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_distribucion'},
    {header: 'RECURSOS SOLICITADOS', width:300,  menuDisabled:true, sortable: true, /*summaryRenderer: function(v, params, data){return '<b>TOTALES</b>';},*/ dataIndex: 'tx_tipo_fondo'},
    {header: 'MONTO PROGRAMADO DEL EJERCICIO A FORMULAR', width:300,  menuDisabled:true, sortable: true, /*summaryType: 'moFondo',*/ renderer: formatoNumero, dataIndex: 'mo_fondo'
<?php if($co_estatus==1){?>
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ff.editar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	,editor: new Ext.form.NumberField({
		allowBlank: false,
		allowNegative: false,
		style: 'text-align:left',
		maxLength: 12,
		autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	})
<?php } ?>
<?php }?>
},
    {header: 'RECURSOS SOLICITADOS', summaryType: 'sum',summaryRenderer: function(v, params, data){return 'Total';},autoWidth: true, sortable: true,groupable: false,  dataIndex: 'tx_tipo_recurso'},
    ],
    view: new Ext.grid.GroupingView({
        groupTextTpl: '{text}',
        forceFit: true,
        showGroupName: false,
        enableNoGroups: false,
	enableGroupingMenu: false,
        hideGroupedColumn: true
    }),
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    plugins: this.summary,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){tabuladorNueve.main.editarMonto.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista_financiamiento,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanelRecurso_.on('afteredit', this.afterEdit, this );

this.fieldset2 = new Ext.form.FieldSet({
	autoWidth:true,
    	title: '9.1.2. En cualquiera de los dos casos anteriores (Total/Parcial), especificar la fuente de financiamiento para el monto a financiar',
        items:[
		this.gridPanelRecurso_
		]
});

this.panelDatos91 = new Ext.Panel({
    title: '9.1. FUENTES DE FINANCIAMIENTO POR PROYECTO',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
	this.fieldset1,
	this.fieldset2
	]
});

this.cuadrar = new Ext.Button({
    text:'"Cerrar si Cuadra"',
    iconCls: 'icon-guardar',
    handler:function(){
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea cerrar Proyecto: <b><?php echo $id_proyecto;?></b>?<br><b>Nota:</b> No se podran modificar los datos.', function(boton){
	if(boton=="yes"){

        Ext.Ajax.request({
            method:'POST',
            /*url:'formulacion/modulos/proyecto/orm.php/cerrar',*/
						url: 'proyecto/cerrar',
            params:{
                codigo: <?php echo $codigo ?>,
                proyecto: '<?php echo $id_proyecto ?>'
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
                    //Ext.Msg.alert("Notificación",obj.msg);
		    Ext.MessageBox.show({
			       title: 'Notificación',
			       msg: obj.msg,
			       buttons: Ext.MessageBox.OK,
			       icon: Ext.MessageBox.INFO
		    });
			opcionPlanificador.main.store_lista.load();
			var direccionar = Ext.get('contenedoreditarProyecto<?php echo $codigo ?>');
			direccionar.load({ url: 'formulacion/modulos/proyecto/editarProyecto.php', scripts: true, text: 'Cargando...',              params:'codigo='+obj.c});
                }else{
			var errores = '';
			for(datos in obj.msg){
				errores += obj.msg[datos] + '<br>';
			}
                    //Ext.Msg.alert("Notificación", errores);
		    Ext.MessageBox.show({
			       title: 'Notificación',
			       msg: errores,
			       buttons: Ext.MessageBox.OK,
			       icon: Ext.MessageBox.WARNING
		    });
                }
            }});

	}});

    }
});

<?php if($co_estatus==3){?>
this.cuadrar.disable();
<?php }?>

this.panelDatos = new Ext.TabPanel({
    activeTab:0,
    enableTabScroll:true,
    deferredRender: false,
    title: '9. DISTRIBUCIÓN DEL PROYECTO POR FUENTE DE FINANCIAMIENTO',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[this.panelDatos91,tabuladorNueveDos.main.panelDatos92],
	tbar:[
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ff.cerrar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
		'->',this.cuadrar
<?php } ?>
	]
});
//Cargar el grid
this.store_lista_financiamiento.baseParams.paginar = 'si';
this.store_lista_financiamiento.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista_financiamiento.load();
this.store_lista_financiamiento.on('beforeload',function(){
panel_detalle.collapse();
});
this.store_lista_financiamiento.on('load',function(){
tabuladorNueve.main.editarMonto.disable();
});
},
getStoreCO_TIPO_FONDO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/accionDistribucion/funcion.php?op=4',
        root:'data',
        fields:[
            {name: 'co_tipo_fondo'},{name: 'tx_tipo_fondo'}
            ]
    });
    return this.store;
},
/*getListaFinanciamiento: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/accionDistribucion/funcion.php?op=5',
    root:'data',
    fields:[
    {name: 'co_proyecto_distribucion'},
    {name: 'id_proyecto'},
    {name: 'tx_tipo_fondo'},
    {name: 'mo_fondo'},
    {name: 'edo_reg'},
    {name: 'tx_tipo_recurso'},
           ]
    });
    return this.store;
},*/
getListaFinanciamiento: function(){
this.Store = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url:'formulacion/modulos/accionDistribucion/funcion.php?op=5',
            method: 'POST'
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'total'
        },
        [
	    {name: 'co_proyecto_distribucion', type: 'number'},
	    {name: 'tx_codigo_recurso'},
	    {name: 'id_proyecto'},
	    {name: 'tx_tipo_fondo'},
	    {name: 'mo_fondo', type: 'number'},
	    {name: 'edo_reg'},
	    {name: 'tx_tipo_recurso'},
        ]),
        sortInfo:{
            field: 'tx_codigo_recurso',
            direction: "ASC"
        },
        groupField:'tx_tipo_recurso'

});
return this.Store;
},
afterEdit : function(e){
	var recordsToSend = [];

	recordsToSend = Ext.encode(e.record.data);

	this.gridPanelRecurso_.el.mask("Guardando...","x-mask-loading");
	Ext.Ajax.request({
		scope	: this,
		url	: 'formulacion/modulos/accionDistribucion/funcion.php?op=6',
		params	: {variables:recordsToSend},
		success	: this.onSuccess
	});
},
onSuccess	: function(response,options){
	this.gridPanelRecurso_.el.unmask();
	this.gridPanelRecurso_.getStore().commitChanges();
}
};
Ext.onReady(tabuladorNueve.main.init, tabuladorNueve.main);
</script>
