<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}  
include("../../configuracion/ConexionComun.php");

$codigo = decode($_POST['codigo']);
$data = json_encode(array(
	"id_proyecto"     => $codigo,
));
?>
<script type="text/javascript">
Ext.ns("accionLista");
function color(val){
	if(val=="Presupuestaria (Bs)"){
	    return '<span style="color:green;">'+val+'</span>';
	}else if(val=="Fisico"){
	    return '<span style="color:red;">'+val+'</span>';
	}
return val;
};
accionLista.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getListaAccion();

//<ClavePrimaria>
this.id_proyecto = new Ext.form.Hidden({
	name:'id_proyecto',
	value:this.OBJ.id_proyecto
});
//</ClavePrimaria>

//Editar un registro
this.editarAccion= new Ext.Button({
    text:'Ver Acciones Fisicas',
    iconCls: 'icon-accion_fisica',
    handler:function(){
	this.codigo  = accionLista.main.gridPanel_.getSelectionModel().getSelected().get('co_proyecto_acc_espec');
	accionLista.main.mascara.show();
        this.msg = Ext.get('formulario_ubicacion');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/accionEspecifica/editarAccionEspecifica.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.verDetalle = new Ext.Button({
	text:'Ver Partidas',
	id:'verPartidas',
	iconCls: 'icon-reporteest',
	handler: function(boton){
		addTab(accionLista.main.gridPanel_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'),'Proyecto '+accionLista.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto')+', Accion Especifica '+accionLista.main.gridPanel_.getSelectionModel().getSelected().get('tx_codigo'),'formulacion/modulos/accionEspecifica/listaPartidas.php','load','icon-reporteest','codigo='+accionLista.main.gridPanel_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'));
		/*var direccionar = Ext.get('contenedoraccionLista');
		direccionar.load({ url: 'formulacion/modulos/proyecto/editarProyecto.php', scripts: true, text: 'Cargando...',              params:'codigo='+accionLista.main.gridPanel_.getSelectionModel().getSelected().get('co_proyectos')});*/
	}
});

this.verFisico = new Ext.Button({
	text:'Ver Partidas',
	id:'verFisico',
	iconCls: 'icon-reporteest',
	handler: function(boton){
		addTab(accionLista.main.gridPanelFisica_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'),'Proyecto '+accionLista.main.gridPanelFisica_.getSelectionModel().getSelected().get('id_proyecto')+', Accion Especifica '+accionLista.main.gridPanelFisica_.getSelectionModel().getSelected().get('tx_codigo'),'formulacion/modulos/accionEspecifica/listaPartidas.php','load','icon-reporteest','codigo='+accionLista.main.gridPanelFisica_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'));
		/*var direccionar = Ext.get('contenedoraccionLista');
		direccionar.load({ url: 'formulacion/modulos/proyecto/editarProyecto.php', scripts: true, text: 'Cargando...',              params:'codigo='+accionLista.main.gridPanel_.getSelectionModel().getSelected().get('co_proyectos')});*/
	}
});

this.editarAccion.disable();

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    store: this.store_lista,
    loadMask:true,
    border:false,
    autoHeight:true,
    autoWidth: true,
    tbar:[
        this.editarAccion
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_acc_espec',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_acc_espec'},
    {header: 'id_proyecto',hidden:true, menuDisabled:true,dataIndex: 'id_proyecto'},
    {header: 'CÓD.', width:50,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo'},
    {header: 'Nombre de la Acción', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'descripcion'},
    {header: 'UNIDAD EJECUTORA RESPONSABLE', width:250,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'co_ejecutores'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){accionLista.main.editarAccion.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedoraccionLista<?php echo $codigo;?>");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista.load();
this.store_lista.on('load',function(){
accionLista.main.editarAccion.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getListaAccion: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/accionEspecifica/funcion.php?op=1',
    root:'data',
    fields:[
    {name: 'co_proyecto_acc_espec'},
    {name: 'id_proyecto'},
    {name: 'tx_codigo'},
    {name: 'descripcion'},
    {name: 'co_unidades_medida'},
    {name: 'meta'},
    {name: 'ponderacion'},
    {name: 'bien_servicio'},
    {name: 'total'},
    {name: 'fec_inicio'},
    {name: 'fec_termino'},
    {name: 'co_ejecutores'},
           ]
    });
    return this.store;
}
};
Ext.onReady(accionLista.main.init, accionLista.main);
</script>
<div id="contenedoraccionLista<?php echo $codigo;?>"></div>
