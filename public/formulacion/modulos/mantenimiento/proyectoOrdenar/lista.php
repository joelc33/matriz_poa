<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}
?>
<script type="text/javascript">
Ext.ns("proyectoOrdenarLista");
function change(val){
	if(val=="t"){
	    return '<span style="color:green;">Activo</span>';
	}else if(val=="f"){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
proyectoOrdenarLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

//Editar un registro
this.ver= new Ext.Button({
    text:'Ver Proyectos',
    iconCls: 'icon-buscar',
    handler:function(){
	this.codigo  = proyectoOrdenarLista.main.gridPanel_.getSelectionModel().getSelected().get('id_ejecutor');
	proyectoOrdenarLista.main.mascara.show();
        this.msg = Ext.get('formularioproyectoOrdenar');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/mantenimiento/proyectoOrdenar/form.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.ver.disable();

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
		proyectoOrdenarLista.main.store_lista.baseParams={};
		proyectoOrdenarLista.main.store_lista.baseParams.paginar = 'si';
		proyectoOrdenarLista.main.store_lista.load();
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
			proyectoOrdenarLista.main.store_lista.baseParams={}
			proyectoOrdenarLista.main.store_lista.baseParams.BuscarBy = true;
			proyectoOrdenarLista.main.store_lista.baseParams[this.paramName] = v;
			proyectoOrdenarLista.main.store_lista.baseParams.paginar = 'si';
			proyectoOrdenarLista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    //title:'Lista de unidadEjecutora',
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
//    frame:true,
//    height:550,
    autoWidth: true,
    autoHeight:true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'reordenar.proyecto.ver', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.ver,'-',
<?php } ?>
	this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'Codigo', width:80,  menuDisabled:true, sortable: true,  dataIndex: 'id_ejecutor'},
    {header: 'Ejecutor', width:300,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_ejecutor'},
    {header: 'Cantidad Proyectos', width:120,  menuDisabled:true, sortable: true, dataIndex: 'nu_proyecto'},
    {header: 'Ejercicio Fiscal', width:100,  menuDisabled:true, sortable: true, dataIndex: 'id_ejercicio'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){proyectoOrdenarLista.main.ver.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorproyectoOrdenarLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
proyectoOrdenarLista.main.ver.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/mantenimiento/proyectoOrdenar/orm.php/lista',
    root:'data',
    fields:[
    {name: 'id_ejercicio'},
    {name: 'id_ejecutor'},
    {name: 'tx_ejecutor'},
    {name: 'nu_proyecto'},
           ]
    });
    return this.store;
}
};
Ext.onReady(proyectoOrdenarLista.main.init, proyectoOrdenarLista.main);
</script>
<div id="contenedorproyectoOrdenarLista"></div>
<div id="formularioproyectoOrdenar"></div>
