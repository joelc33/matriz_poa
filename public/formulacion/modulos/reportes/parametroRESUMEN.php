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
Ext.ns('parametroRESUMEN');
parametroRESUMEN.main = {
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
                title: 'RESUMEN - PROYECTOS',
		items:[
			this.fieldset1		
			],
                buttonAlign:'center',
                buttons:[
<?php if( in_array( array( 'de_privilegio' => 'ff.ejecutor', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
                {
                    text:'REPORTE por Ejecutor',  // Generar la impresión en pdf
                    iconCls:'icon-pdf',
                    handler: this.onImprimir
                },
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ff.todos', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
                {
                    text:'REPORTE Todos',  // Generar la impresión en pdf
                    iconCls:'icon-pdf',
                    handler: this.onImprimir1
                },
<?php } ?>
		{
                    text:'Limpiar',  // Limpiar campos del formulario
                    iconCls:'icon-limpiar',
                    handler: this.onLimpiar
                }
            ]
	});

this.formpanel.render('parametroRESUMEN');
},
onImprimir : function() {
if(!parametroRESUMEN.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}
   //window.open('formulacion/modulos/reportes/proyResumen.php?'+parametroRESUMEN.main.formpanel.getForm().getValues(true));
	bajar.load({
		url: 'formulacion/modulos/reportes/proyResumen.php?'+parametroRESUMEN.main.formpanel.getForm().getValues(true)
	});
},
onImprimir1 : function() {
   //window.open('formulacion/modulos/reportes/proyResumen.php');
	bajar.load({
		url: 'formulacion/modulos/reportes/proyResumen.php'
	});
},
onLimpiar: function(){
    parametroRESUMEN.main.formpanel.getForm().reset();
    parametroRESUMEN.main.store_lista.baseParams={};
    parametroRESUMEN.main.store_lista.removeAll();
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
Ext.onReady(parametroRESUMEN.main.init, parametroRESUMEN.main);
</script>
<div id="parametroRESUMEN"></div>
