<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}
?>
<script type="text/javascript">
Ext.ns("ejercicioLista");
function change(val){
	if(val=="t"){
	    return '<span style="color:green;">Activo</span>';
	}else if(val=="f"){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
ejercicioLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.habilitar= new Ext.Button({
    text:'Aperturar',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejercicio_fiscal');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea habilitar?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/ejercicioFiscal/funcion.php?op=3',
            params:{
                co_ejercicio_fiscal:ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejercicio_fiscal')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    ejercicioLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                ejercicioLista.main.mascara.hide();
            }});
	}});
    }
});

//Desabilitar un registro
this.deshabilitar= new Ext.Button({
    text:'Cerrar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejercicio_fiscal');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea desabilitar?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/ejercicioFiscal/funcion.php?op=4',
            params:{
                co_ejercicio_fiscal:ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejercicio_fiscal')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    ejercicioLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                ejercicioLista.main.mascara.hide();
            }});
	}});
    }
});

this.habilitar.disable();
this.deshabilitar.disable();

//Editar un registro
this.ver= new Ext.Button({
    text:'Cronograma',
    iconCls: 'icon-calendario',
    handler:function(){
	this.codigo  = ejercicioLista.main.gridPanel_.getSelectionModel().getSelected().get('co_ejercicio_fiscal');
	ejercicioLista.main.mascara.show();
        this.msg = Ext.get('formularioejercicioLista');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/mantenimiento/periodo/lista.php",
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
		ejercicioLista.main.store_lista.baseParams={};
		ejercicioLista.main.store_lista.baseParams.paginar = 'si';
		ejercicioLista.main.store_lista.load();
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
			ejercicioLista.main.store_lista.baseParams={}
			ejercicioLista.main.store_lista.baseParams.BuscarBy = true;
			ejercicioLista.main.store_lista.baseParams[this.paramName] = v;
			ejercicioLista.main.store_lista.baseParams.paginar = 'si';
			ejercicioLista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    //title:'Lista de plan',
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
//    frame:true,
//    height:550,
    autoWidth: true,
    autoHeight:true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.aperturar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.habilitar,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.cerrar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.deshabilitar,'-',
<?php } ?>
	this.buscador,'-',
<?php if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.cronograma', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.ver
<?php } ?>
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'Descripcion', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'co_ejercicio_fiscal'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'edo_reg'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){ejercicioLista.main.habilitar.enable();ejercicioLista.main.deshabilitar.enable();ejercicioLista.main.ver.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorejercicioLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
ejercicioLista.main.habilitar.disable();
ejercicioLista.main.deshabilitar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/ejercicioFiscal/funcion.php?op=1',
    root:'data',
    fields:[
    {name: 'co_ejercicio_fiscal'},
    {name: 'edo_reg'},
           ]
    });
    return this.store;
}
};
Ext.onReady(ejercicioLista.main.init, ejercicioLista.main);
</script>
<div id="contenedorejercicioLista"></div>
<div id="formularioejercicioLista"></div>
