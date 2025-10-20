<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}
?>
<script type="text/javascript">
Ext.ns("consultarSAC");
consultarSAC.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

this.id_proyecto = new Ext.form.TextField({
	fieldLabel:'Código',
	name:'id_proyecto',
	value:'',
	width:150,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 16},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.codigoProyecto = new Ext.form.CompositeField({
fieldLabel: 'Código',
items: [
	this.id_proyecto,
             {
                   xtype: 'displayfield',
                   value: '&nbsp;&nbsp;&nbsp; <b>**Indique codigo de la Accion Centralizada</b>',
                   width: 400
             },
	]
});

/**
* <Form Principal que carga el Filtro>
*/
this.formFiltroPrincipal = new Ext.form.FormPanel({
	title: 'Consultar Accion Centralizada',
	iconCls:'icon-buscar',
    collapsible: true,
    titleCollapse: true,
    autoWidth:true,
    autoHeight:true,
    border:true,
    labelWidth: 110,
    padding:'10px',
    items: [
	this.codigoProyecto,
    ],
    buttonAlign:'left',
    buttons:[
        {
            text:'Consultar',
            iconCls:'icon-buscar',
            handler:function(){
		var campo = consultarSAC.main.formFiltroPrincipal.getForm().getValues();
		var swfiltrar = false;
		for(campName in campo){
		    if(campo[campName]!=''){
			swfiltrar = true;
		    }
		}
		if(swfiltrar==true){
			var msgConsulta = Ext.get('consultarAccionSAC');
			msgConsulta.load({ url: 'formulacion/modulos/seguimiento_ac_actividad/resultado.php', scripts: true, text: 'Consultando Proyecto...',params:{BuscarBy:'true', id_proyecto:consultarSAC.main.id_proyecto.getValue()}});
			consultarSAC.main.formFiltroPrincipal.body.highlight('#c3daf9', {block:true});
		}else{
		    Ext.MessageBox.show({
			       title: 'Notificación',
			       msg: 'Debe ingresar un parametro de busqueda',
			       buttons: Ext.MessageBox.OK,
			       icon: Ext.MessageBox.WARNING
		    });
		}
            }
        },
        {
		text:'Cerrar Pestaña',
		iconCls:'icon-cancelar',
		tooltip: 'Cerrar esta Pestaña',
		handler:function(){
			this.panelCambio = Ext.getCmp('tabpanel');
			this.panelCambio.remove('39');
		}
        }
    ]
});

this.panel = new Ext.Panel({
	layout: "fit",
	border: false,
	padding	: 5,
	items: [this.formFiltroPrincipal]
});
this.panel.render("contenedorconsultarSAC");
}
};
Ext.onReady(consultarSAC.main.init, consultarSAC.main);
</script>
<div id="contenedorconsultarSAC"></div>
<div id="consultarAccionSAC" ></div>
