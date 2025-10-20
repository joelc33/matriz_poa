<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT * FROM t69_metas_ac
	where co_metas=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_metas"     => trim($row["co_metas"]),
			"co_ac_acc_espec"     => trim($row["co_ac_acc_espec"]),
			"id_accion_centralizada"     => trim($row["id_accion_centralizada"]),
			"codigo"     => trim($row["codigo"]),
			"nb_actividad"     => trim($row["nb_meta"]),
			"co_unidades_medida"     => trim($row["co_unidades_medida"]),
			"pr_anual"     => trim($row["tx_prog_anual"]),
			"fecha_inicio"     => trim($row["fecha_inicio"]),
			"fecha_culminacion"     => trim($row["fecha_fin"]),
			"nb_responsable"     => trim($row["nb_responsable"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_metas"     => decode($_POST['co_metas']),
		"co_ac_acc_espec"     => decode($_POST['co_proyecto_acc_espec']),
		"id_accion_centralizada"     => decode($_POST['id_accion_centralizada']),
	));
}
?>
<script type="text/javascript">
Ext.ns("metaEditar");
metaEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeCO_UNIDADES_MEDIDA = this.getStoreCO_UNIDADES_MEDIDA();
//<Stores de fk>

//<ClavePrimaria>
this.co_metas = new Ext.form.Hidden({
    name:'co_metas',
    value:this.OBJ.co_metas});
//</ClavePrimaria>
this.co_ac_acc_espec = new Ext.form.Hidden({
    name:'co_ac_acc_espec',
    value:this.OBJ.co_ac_acc_espec});
this.id_accion_centralizada = new Ext.form.Hidden({
    name:'id_accion_centralizada',
    value:this.OBJ.id_accion_centralizada});

this.nb_actividad = new Ext.form.TextField({
	fieldLabel:'NOMBRE DE LA ACTIVIDAD',
	name:'nb_actividad',
	value:this.OBJ.nb_actividad,
	width:400,
	maxLength: 250,
	allowBlank:false,
	readOnly:true,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.co_unidades_medida = new Ext.form.ComboBox({
	fieldLabel:'UNIDAD DE MEDIDA',
	store: this.storeCO_UNIDADES_MEDIDA,
	typeAhead: true,
	valueField: 'co_unidades_medida',
	displayField:'tx_unidades_medida',
	hiddenName:'co_unidades_medida',
	//readOnly:(this.OBJ.co_unidades_medida!='')?true:false,
	//style:(this.OBJ.co_unidades_medida!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Unidades',
	selectOnFocus: true,
	mode: 'local',
	readOnly:true,
	width:400,
	resizable:true,
	allowBlank:false
});

this.storeCO_UNIDADES_MEDIDA.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_unidades_medida,
	value:  this.OBJ.co_unidades_medida,
	objStore: this.storeCO_UNIDADES_MEDIDA
});

this.pr_anual = new Ext.form.NumberField({
	fieldLabel:'PROGRAMADO ANUAL',
	name:'pr_anual',
	value:this.OBJ.pr_anual,
	allowBlank:false,
	width:200,
	maxLength: 8,
	emptyText: '0',
	decimalPrecision: 0,
 	minValue : 0,
 	maxValue : 99999999,
	msgTarget : 'Rango Entre 0 y 9',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
	allowDecimals: false,
	readOnly:true,
	allowNegative: false
});

this.fecha_inicio = new Ext.form.DateField({
	fieldLabel:'FECHA DE INICIO',
	name:'fecha_inicio',
	//value:(this.OBJ.fecha_inicio)?this.OBJ.fecha_inicio:'<?= date('d-m-Y',strtotime($fechaI)); ?>',
	value:this.OBJ.fecha_inicio,
	allowBlank:false,
	readOnly:true,
	width:100,
	/*minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',*/
});

this.fecha_culminacion = new Ext.form.DateField({
	fieldLabel:'FECHA DE CULMINACIÓN',
	name:'fecha_culminacion',
	//value:(this.OBJ.fecha_culminacion)?this.OBJ.fecha_culminacion:'<?= date('d-m-Y',strtotime($fechaF)); ?>',
	value:this.OBJ.fecha_culminacion,
	allowBlank:false,
	readOnly:true,
	width:100,
	/*minValue:'<?php echo $fechaI; ?>',
	maxValue:'<?php echo $fechaF; ?>',*/
});

this.comFechaInCul = new Ext.form.CompositeField({
fieldLabel: 'FECHA DE INICIO',
items: [
	this.fecha_inicio,
             {
                   xtype: 'displayfield',
                   value: '&nbsp;&nbsp;&nbsp; FECHA DE CULMINACIÓN:',
                   width: 190
             },
	this.fecha_culminacion
	]
});

this.nb_responsable = new Ext.form.TextField({
	fieldLabel:'RESPONSABLE',
	name:'nb_responsable',
	value:this.OBJ.nb_responsable,
	width:400,
	maxLength: 250,
	allowBlank:false,
	readOnly:true,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:false,
	border:false,
        items:[
		this.nb_actividad,
		this.co_unidades_medida,
		this.pr_anual,
		this.comFechaInCul,
		this.nb_responsable
		]
});

this.panelDatos1 = new Ext.Panel({
    title: 'METAS FISICAS',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.fieldset1
	]
});

this.Registro = Ext.data.Record.create([
	{ name: 'co_metas_detalle', type: 'number'},
	{ name: 'co_metas', type: 'number'},
	{ name: 'co_municipio', type: 'number'},
	{ name: 'tx_municipio', type: 'string'},
	{ name: 'co_parroquia', type: 'number'},
	{ name: 'tx_parroquia', type: 'string'},
	{ name: 'mo_presupuesto', type: 'number'},
	{ name: 'co_partida', type: 'number'},
	{ name: 'co_fuente_financiamiento', type: 'number' },
	{ name: 'tx_fuente_financiamiento', type: 'string' },
]);

this.store_lista =  new Ext.data.GroupingStore({
	reader: new Ext.data.JsonReader({fields:metaEditar.main.Registro})
});

if(this.OBJ.co_metas!='')
{
	this.store_lista = new Ext.data.JsonStore({
		url:'formulacion/modulos/metas/funcion.php?op=10',
		root:'data',
		fields:
			[
				{ name: 'co_metas_detalle'},
				{ name: 'co_metas'},
				{ name: 'co_municipio'},
				{ name: 'tx_municipio'},
				{ name: 'co_parroquia'},
				{ name: 'tx_parroquia'},
				{ name: 'mo_presupuesto'},
				{ name: 'co_partida'},
				{ name: 'co_fuente_financiamiento'},
				{ name: 'tx_fuente_financiamiento'},
			]
		});
	this.store_lista.load({
		params: {co_metas:metaEditar.main.co_metas.getValue()},
	});
}

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    iconCls: 'icon-libro',border:true,
    store: this.store_lista,
    loadMask:true,
    autoHeight:true,
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_municipio',hidden:true, menuDisabled:true,dataIndex: 'co_municipio'},
    {header: 'co_parroquia',hidden:true, menuDisabled:true,dataIndex: 'co_parroquia'},
    {header: 'co_fuente_financiamiento',hidden:true, menuDisabled:true,dataIndex: 'co_fuente_financiamiento'},
    {header: 'MUNICIPIO', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_municipio'},
    {header: 'PARROQUIA', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_parroquia'},
    {header: 'PRESUPUESTO', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_presupuesto'},
    {header: 'PARTIDA', width:80,  menuDisabled:true, sortable: true,  dataIndex: 'co_partida'},
    {header: 'FUENTE DE FINANCIAMIENTO', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_fuente_financiamiento'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
});

this.panelDatos2 = new Ext.Panel({
    title: 'METAS FINANCIERAS',
    bodyStyle:'padding:5px;',
    height:300,
    autoScroll:true,
    items:[
	this.gridPanel_
	]
});

this.panel = new Ext.TabPanel({
    activeTab:0,
    height:300,
    enableTabScroll:true,
    deferredRender: false,
    items:[
	this.panelDatos1,
	this.panelDatos2
	]
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        metaEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	//fileUpload: true,
	width:700,
	autoHeight:true,  
	autoScroll:true,
	labelWidth: 180,
	border:false,
	bodyStyle:'padding:5px;',
	items:[
		this.co_metas,
		this.id_accion_centralizada,
		this.co_ac_acc_espec,
		this.panel
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Actividades',
    modal:true,
    constrain:true,
width:714,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ]
});
this.winformPanel_.show();
metaLista.main.mascara.hide();
},
getStoreCO_UNIDADES_MEDIDA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/proyecto/funcion.php?op=16',
        root:'data',
        fields:[
            {name: 'co_unidades_medida'},{name: 'tx_unidades_medida'}
            ]
    });
    return this.store;
}
};
Ext.onReady(metaEditar.main.init, metaEditar.main);
</script>
