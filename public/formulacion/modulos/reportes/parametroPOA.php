<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}  
include("../../configuracion/ConexionComun.php");

$id_ejecutor = $_SESSION['id_ejecutor'];

$data = json_encode(array(
	"id_ejecutor"     => $id_ejecutor,
));
?>
<script type="text/javascript">
Ext.ns('reportePOA');
reportePOA.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeID_EJECUTOR = this.getStoreID_EJECUTOR();
//<Stores de fk>

this.id_ejecutor = new Ext.form.ComboBox({
	fieldLabel:'UNIDAD EJECUTORA',
	store: this.storeID_EJECUTOR,
	typeAhead: true,
	valueField: 'id_ejecutor',
	displayField:'tx_ejecutor',
	hiddenName:'id_ejecutor',
	<?php 
	if($_SESSION['co_rol']>2){
	echo "readOnly:true,style:'background:#c9c9c9;',";		 
	}else{}?>
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Unidad Ejecutora',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_ejecutor}</div></div></tpl>'),
	//listWidth:'600',
	resizable:true,
	allowBlank:false
});
this.storeID_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_ejecutor,
	value:  this.OBJ.id_ejecutor,
	objStore: this.storeID_EJECUTOR
});

this.fieldset1 = new Ext.form.FieldSet({
        title: 'Seleccione Parametros',
        items:[
		this.id_ejecutor
		]
});

          this.formpanel = new Ext.form.FormPanel({
		bodyStyle: 'padding:10px',
		autoWidth:true,
		autoHeight:true,
                border:false,
                id: 'forma', 
    		labelWidth: 210,
		iconCls:'icon-reporteest',             
                title: 'PLAN OPERATIVO ANUAL',
		items:[
			this.fieldset1		
			],
                buttonAlign:'center',
                buttons:[
                {
                    text:'Plan Operativo Anual',  // Generar la impresión en pdf
                    iconCls:'icon-pdf',
                    handler: this.onImprimir
                },
		{
                    text:'Limpiar',  // Limpiar campos del formulario
                    iconCls:'icon-limpiar',
                    handler: this.onLimpiar
                }
            ]
	});

this.formpanel.render('reportePOA');
},
onImprimir : function() {
if(!reportePOA.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}
   window.open('resumen.php?'+reportePOA.main.formpanel.getForm().getValues(true));
},
onLimpiar: function(){
    reportePOA.main.formpanel.getForm().reset();
    reportePOA.main.store_lista.baseParams={};
    reportePOA.main.store_lista.removeAll();
},
getStoreID_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/usuario/funcion.php?op=5',
        root:'data',
        fields:[
            {name: 'id_ejecutor'},{name: 'tx_ejecutor'}
            ]
    });
    return this.store;
}
}
Ext.onReady(reportePOA.main.init, reportePOA.main);
</script>
<div id="reportePOA"></div>
