<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}
?>
<script type="text/javascript">
Ext.ns("sectorLista");
function change(val){
	if(val=="t"){
	    return '<span style="color:green;">Activo</span>';
	}else if(val=="f"){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
sectorLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

//Agregar un registro
this.nuevo = new Ext.Button({
    text:'Nuevo',
    iconCls: 'icon-nuevo',
    handler:function(){
        sectorLista.main.mascara.show();
        this.msg = Ext.get('formularioSector');
        this.msg.load({
         url:"formulacion/modulos/sector/editarSector.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Editar un registro
this.editar= new Ext.Button({
    text:'Editar',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = sectorLista.main.gridPanel_.getSelectionModel().getSelected().get('co_sectores');
	sectorLista.main.mascara.show();
        this.msg = Ext.get('formularioSector');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/sector/editarSector.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Desabilitar un registro
this.deshabilitar= new Ext.Button({
    text:'Desabilitar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = sectorLista.main.gridPanel_.getSelectionModel().getSelected().get('co_sectores');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea desabilitar sector?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/sector/funcion.php?op=3',
            params:{
                co_sectores:sectorLista.main.gridPanel_.getSelectionModel().getSelected().get('co_sectores')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    sectorLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                sectorLista.main.mascara.hide();
            }});
	}});
    }
});

this.editar.disable();
this.deshabilitar.disable();

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
		sectorLista.main.store_lista.baseParams={};
		sectorLista.main.store_lista.baseParams.paginar = 'si';
		sectorLista.main.store_lista.load();
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
			sectorLista.main.store_lista.baseParams={}
			sectorLista.main.store_lista.baseParams.BuscarBy = true;
			sectorLista.main.store_lista.baseParams[this.paramName] = v;
			sectorLista.main.store_lista.baseParams.paginar = 'si';
			sectorLista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    //title:'Lista de sector',
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
//    frame:true,
//    height:550,
    autoWidth: true,
    autoHeight:true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'sectores.nuevo', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.nuevo,'-',
<?php } ?>
	this.buscador,'-',
<?php if( in_array( array( 'de_privilegio' => 'sectores.editar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.editar,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'sectores.deshabilitar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.deshabilitar
<?php } ?>
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_sectores',hidden:true, menuDisabled:true,dataIndex: 'co_sectores'},
    {header: 'Codigo', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo'},
    {header: 'Descripcion', width:400,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_descripcion'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'edo_reg'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){sectorLista.main.editar.enable();sectorLista.main.deshabilitar.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorsectorLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
sectorLista.main.editar.disable();
sectorLista.main.deshabilitar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/sector/funcion.php?op=1',
    root:'data',
    fields:[
    {name: 'co_sectores'},
    {name: 'co_sector'},
    {name: 'co_sub_sector'},
    {name: 'nu_nivel'},
    {name: 'tx_codigo'},
    {name: 'tx_descripcion'},
    {name: 'nu_descripcion'},
    {name: 'edo_reg'},
           ]
    });
    return this.store;
}
};
Ext.onReady(sectorLista.main.init, sectorLista.main);
</script>
<div id="contenedorsectorLista"></div>
<div id="formularioSector"></div>
