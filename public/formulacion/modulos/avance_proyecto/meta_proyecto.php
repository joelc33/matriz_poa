<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}  
include("../../configuracion/ConexionComun.php");

$id_proyecto = decode($_POST['id_proyecto']);
$codigo = decode($_POST['codigo']);
$data = json_encode(array(
	"co_proyecto_acc_espec"     => $codigo,
	"id_proyecto"     => $id_proyecto,
));

$comunes = new ConexionComun();
$sql = "SELECT total FROM proyecto_seguimiento.tab_proyecto_ae where id=".$codigo;
$resultado = $comunes->ObtenerFilasBySqlSelect($sql);
$resultadoReal = $resultado[0]['total'];

$datosEnunciado='Avance de Actividades';
$url_avance='formulacion/modulos/avance_proyecto/avance_lista.php';
$variable="+'&id_proyecto='+metaListaSAP.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto')";
?>
<script type="text/javascript">
Ext.ns("metaListaSAP");
function color(val){
	if(val=="Presupuestaria (Bs)"){
	    return '<span style="color:green;">'+val+'</span>';
	}else if(val=="Fisico"){
	    return '<span style="color:red;">'+val+'</span>';
	}
return val;
};
function colorCargado(valorCargado){
	return '<span style="color:green;">'+formatoNumero(valorCargado)+'</span>';
return val;
};
metaListaSAP.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

//<ClavePrimaria>
this.co_proyecto_acc_espec = new Ext.form.Hidden({
	name:'co_proyecto_acc_espec',
	value:this.OBJ.co_proyecto_acc_espec
});
//</ClavePrimaria>
this.id_proyecto = new Ext.form.Hidden({
	name:'id_proyecto',
	value:this.OBJ.id_proyecto
});

//Editar un registro
this.editar= new Ext.Button({
    text:'Editar Actividad',
    iconCls: 'icon-seguimiento',
    handler:function(){
	this.codigo  = metaListaSAP.main.gridPanel_.getSelectionModel().getSelected().get('co_metas');
	metaListaSAP.main.mascara.show();
        this.msg = Ext.get('formulario_actividad<?php echo $codigo;?>');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo,id_proyecto:metaListaSAP.main.id_proyecto.getValue()},
         url:"formulacion/modulos/avance_proyecto/editar_meta.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.avance = new Ext.Button({
	text:'Ver Avances',
	id:'verAvance',
	iconCls: 'icon-seguimiento',
	tooltip: 'Ver Avances de la Actividad',
	handler: function(boton){
		addTab(metaListaSAP.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto')+'ae','<?php echo $datosEnunciado;?>: '+metaListaSAP.main.gridPanel_.getSelectionModel().getSelected().get('tx_codigo'),'<?php echo $url_avance;?>','load','icon-seguimiento','codigo='+metaListaSAP.main.gridPanel_.getSelectionModel().getSelected().get('co_metas'));
	}
});

this.avance.disable();

this.buscador = new Ext.form.TwinTriggerField({
	initComponent : function(){
		Ext.ux.form.SearchField.superclass.initComponent.call(this);
		this.on('specialkey', function(f, e){
			if(e.getKey() == e.ENTER){
				this.onTrigger2Click();
			}
		}, this);
	},
	xtype: 'twintriggerfield',
	trigger1Class: 'x-form-clear-trigger',
	trigger2Class: 'x-form-search-trigger',
	enableKeyEvents : true,
	validationEvent:false,
	validateOnBlur:false,
	emptyText: 'Campo de Filtro',
	width:350,
	hasSearch : false,
	paramName : 'variable',
	onTrigger1Click : function() {
		if (this.hiddenField) {
			this.hiddenField.value = '';
		}
		this.setRawValue('');
		this.lastSelectionText = '';
		this.applyEmptyText();
		this.value = '';
		this.fireEvent('clear', this);
		metaListaSAP.main.store_lista.baseParams={};
		metaListaSAP.main.store_lista.baseParams.paginar = 'si';
		metaListaSAP.main.store_lista.baseParams.co_proyecto_acc_espec = metaListaSAP.main.co_proyecto_acc_espec.getValue();
		metaListaSAP.main.store_lista.load();
	},
	onTrigger2Click : function(){
		var v = this.getRawValue();
		if(v.length < 1){
			    Ext.MessageBox.show({
				       title: 'Notificación',
				       msg: 'Debe ingresar un parametro de busqueda',
				       buttons: Ext.MessageBox.OK,
				       icon: Ext.MessageBox.WARNING
			    });
		}else{
			metaListaSAP.main.store_lista.baseParams={}
			metaListaSAP.main.store_lista.baseParams.BuscarBy = true;
			metaListaSAP.main.store_lista.baseParams[this.paramName] = v;
			metaListaSAP.main.store_lista.baseParams.paginar = 'si';
			metaListaSAP.main.store_lista.baseParams.co_proyecto_acc_espec = metaListaSAP.main.co_proyecto_acc_espec.getValue();
			metaListaSAP.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    store: this.store_lista,
    loadMask:true,
    border:false,
    autoHeight:true,
    autoWidth: true,
    tbar:[
        this.avance,'-',this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_metas',hidden:true, menuDisabled:true,dataIndex: 'co_metas'},
    {header: 'CODIGO', width:70,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo'},
    {header: 'ACTIVIDAD', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nb_meta'},
    {header: 'UNIDAD DE MEDIDA', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'co_unidades_medida'},
    {header: 'PROGRAMADO ANUAL', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_prog_anual'},
    {header: 'INICIO', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'fecha_inicio'},
    {header: 'FINAL', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'fecha_fin'},
    {header: 'TOTAL CARGADO', width:120,  menuDisabled:true, sortable: true, renderer: colorCargado, dataIndex: 'mo_cargado'},
    {header: 'RESPONSABLE', width:120,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nb_responsable'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){metaListaSAP.main.avance.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedormetaListaSAP<?php echo $codigo;?>");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.co_proyecto_acc_espec = this.OBJ.co_proyecto_acc_espec;
this.store_lista.load();
this.store_lista.on('load',function(){
metaListaSAP.main.avance.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/avance_proyecto/funcion.php',
    root:'data',
    baseParams: {
	op: 3
    },
    fields:[
    {name: 'co_metas'},
    {name: 'tx_codigo'},
    {name: 'co_proyecto_acc_espec'},
    {name: 'nb_meta'},
    {name: 'co_unidades_medida'},
    {name: 'tx_prog_anual'},
    {name: 'fecha_inicio'},
    {name: 'fecha_fin'},
    {name: 'nb_responsable'},
    {name: 'mo_cargado'},
           ]
    });
    return this.store;
}
};
Ext.onReady(metaListaSAP.main.init, metaListaSAP.main);
</script>
<div id="contenedormetaListaSAP<?php echo $codigo;?>"></div>
<div id="formulario_actividad<?php echo $codigo;?>"></div>
<div id="formulario_ubicacion"></div>
