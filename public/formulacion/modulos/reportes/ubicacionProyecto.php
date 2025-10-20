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
Ext.ns('parametroUbicacionPR');
parametroUbicacionPR.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeCO_MUNICIPIO = this.getStoreCO_MUNICIPIO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_PARROQUIA = this.getStoreCO_PARROQUIA();
//<Stores de fk>

this.co_municipio = new Ext.form.ComboBox({
	fieldLabel:'MUNICIPIO',
	store: this.storeCO_MUNICIPIO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_municipio',
	hiddenName:'id_tab_municipio',
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
                parametroUbicacionPR.main.storeCO_PARROQUIA.load({
                    params: {id_tab_municipio:this.getValue()}
                })
            }
        }
});

this.storeCO_MUNICIPIO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_municipio,
	value:  this.OBJ.co_municipio,
	objStore: this.storeCO_MUNICIPIO
});

this.co_municipio.on('beforeselect',function(cmb,record,index){
        	this.co_parroquia.clearValue();
},this);

this.co_parroquia = new Ext.form.ComboBox({
	fieldLabel:'PARROQUIA',
	store: this.storeCO_PARROQUIA,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_parroquia',
	hiddenName:'id_tab_parroquia',
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


this.fieldset1 = new Ext.form.FieldSet({
        title: 'Seleccione Parametros',
        items:[
		this.co_municipio,
		//this.co_parroquia
		]
});

          this.formpanel = new Ext.form.FormPanel({
		bodyStyle: 'padding:10px',
		autoWidth:true,
		autoHeight:true,
                border:false,
                id: 'forma', 
    		labelWidth: 210,
		iconCls:'icon-mapa',             
                title: 'UBICACION - PROYECTOS',
		items:[
			this.fieldset1		
			],
                buttonAlign:'left',
                buttons:[
<?php if( in_array( array( 'de_privilegio' => 'ubicacion.proyecto.municipio', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
                {
                    text:'REPORTE por Municipio',  // Generar la impresión en pdf
                    iconCls:'icon-pdf',
                    handler: this.onImprimir
                },
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ubicacion.proyecto.todos', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
                {
                    text:'REPORTE Todos',  // Generar la impresión en pdf
                    iconCls:'icon-pdf',
                    handler: this.onImprimir1
                },
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ubicacion.proyecto.exportar.municipio', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
		{text:'Exportar por Municipio', iconCls:'icon-excel',handler: this.onExportar1},
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ubicacion.proyecto.exportar.todo', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
		{text:'Exportar Todos', iconCls:'icon-excel',handler: this.onExportar2},
<?php } ?>
		{
                    text:'Limpiar',  // Limpiar campos del formulario
                    iconCls:'icon-limpiar',
                    handler: this.onLimpiar
                }
            ]
	});

this.formpanel.render('parametroUbicacionPR');
},
onImprimir : function() {
if(!parametroUbicacionPR.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}
   //window.open('formulacion/modulos/reportes/proyResumen.php?'+parametroUbicacionPR.main.formpanel.getForm().getValues(true));
	bajar.load({
		url: 'formulacion/modulos/reportes/ormPDF.php/reporte/ubicacion?'+parametroUbicacionPR.main.formpanel.getForm().getValues(true)
	});
},
onImprimir1 : function() {
   //window.open('formulacion/modulos/reportes/proyResumen.php');
	bajar.load({
		url: 'formulacion/modulos/reportes/ormPDF.php/reporte/ubicacion/todo'
	});
},
onExportar1 : function() {
if(!parametroUbicacionPR.main.formpanel.getForm().isValid()){
    Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
    return false;
}
	bajar.load({
		url: 'formulacion/modulos/reportes/orm.php/exportar/ubicacion?'+parametroUbicacionPR.main.formpanel.getForm().getValues(true)
	});
},
onExportar2 : function() {
   //window.open('formulacion/modulos/reportes/proyResumen.php');
	bajar.load({
		url: 'formulacion/modulos/reportes/orm.php/exportar/ubicacion/todo'
	});
},
onLimpiar: function(){
    parametroUbicacionPR.main.formpanel.getForm().reset();
},
getStoreCO_MUNICIPIO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/reportes/orm.php/municipio',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_municipio'}
            ]
    });
    return this.store;
},
getStoreCO_PARROQUIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/reportes/orm.php/parroquia',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_parroquia'}
            ]
    });
    return this.store;
}
}
Ext.onReady(parametroUbicacionPR.main.init, parametroUbicacionPR.main);
</script>
<div id="parametroUbicacionPR"></div>
