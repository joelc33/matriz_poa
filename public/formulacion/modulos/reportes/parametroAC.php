<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}
include("../../configuracion/ConexionComun.php");

$id_ejecutor = $_SESSION['id_ejecutor'];

$data = json_encode(array(
	"id_ejecutor"     => $id_ejecutor,
));
?>
<script type="text/javascript">
Ext.ns('parametroAC');
parametroAC.main = {
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
	allowBlank:false,
        listeners:{
            keyup: function() {
               this.store.filter('tx_ejecutor', this.getRawValue(), true, false);
            },
            beforequery: function(queryEvent) {
            queryEvent.combo.onLoad();
            return false; 
            }
        }
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
                title: 'PLAN OPERATIVO ANUAL - ACCIONES CENTRALIZADAS',
		items:[
			this.fieldset1		
			],
                buttonAlign:'left',
                buttons:[
<?php if( in_array( array( 'de_privilegio' => 'ac.poa.ejecutor', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
                {
                    text:'POA por Ejecutor',  // Generar la impresión en pdf
                    iconCls:'icon-pdf',
                    handler: this.onImprimirFormato
                },
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ac.poa.partida', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
		{text:'Resumen Partidas', iconCls:'icon-pdf',handler: this.onImprimir2},
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ac.poa.todos', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
		{text:'POA Todos', iconCls:'icon-pdf',handler: this.onImprimir1},
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ac.poa.exportar.partida', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
		{text:'Exportar Partidas', iconCls:'icon-excel',handler: this.onExportar1},
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ac.poa.exportar.todo', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
		{text:'Exportar Todo', iconCls:'icon-excel',handler: this.onExportar2},
<?php } ?>
                /*{
                    text:'Formato',  // Generar la impresión en pdf
                    iconCls:'icon-pdf',
                    handler: this.onImprimirFormato
                },*/
		{
                    text:'Limpiar',  // Limpiar campos del formulario
                    iconCls:'icon-limpiar',
                    handler: this.onLimpiar
                }
            ]
	});

this.formpanel.render('parametroAC');
},
onImprimir : function() {
if(!parametroAC.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}
   //window.open('formulacion/modulos/reportes/aeAc.php?'+parametroAC.main.formpanel.getForm().getValues(true));
	bajar.load({
		url: 'formulacion/modulos/reportes/aeAc.php?'+parametroAC.main.formpanel.getForm().getValues(true)
	});
},
onImprimir1 : function() {
   //window.open('formulacion/modulos/reportes/aeAc.php');
	bajar.load({
		url: 'formulacion/modulos/reportes/aeAc.php'
	});
},
onExportar1 : function() {
	bajar.load({
		url: 'formulacion/modulos/reportes/orm.php/exportar/partida/ac?'+parametroAC.main.formpanel.getForm().getValues(true)
	});
},
onExportar2 : function() {
	bajar.load({
		url: 'formulacion/modulos/reportes/orm.php/exportar/partida/ac/todo'
	});
},
onImprimirFormato : function() {
	if(!parametroAC.main.formpanel.getForm().isValid()){
	    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
	    return false;
	}
	bajar.load({
		url: 'formulacion/modulos/reportes/acFormato.php?'+parametroAC.main.formpanel.getForm().getValues(true)
	});
},
onImprimir2 : function() {
if(!parametroAC.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}
   //window.open('formulacion/modulos/reportes/aeAc.php?'+parametroAC.main.formpanel.getForm().getValues(true));
	bajar.load({
		url: 'formulacion/modulos/reportes/resumenPartidaAC.php?'+parametroAC.main.formpanel.getForm().getValues(true)
	});
},
onLimpiar: function(){
    parametroAC.main.formpanel.getForm().reset();
    parametroAC.main.store_lista.baseParams={};
    parametroAC.main.store_lista.removeAll();
},
getStoreID_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/reportes/funcion.php?op=3',
        root:'data',
        fields:[
            {name: 'id_ejecutor'},{name: 'tx_ejecutor'}
            ],
            filter: function(filters, value) {
                Ext.data.Store.prototype.filter.apply(this, [
                    filters,
                    value ? new RegExp(String.escape(value), 'i') : value
                ]);
            }
    });
    return this.store;
}
}
Ext.onReady(parametroAC.main.init, parametroAC.main);
</script>
<div id="parametroAC"></div>
