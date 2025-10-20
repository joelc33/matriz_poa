<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}

require_once (__DIR__.'/../../../plugins/eloquent/app.config.php');
require_once (__DIR__.'/../../../model/tab_ejercicio_fiscal.php');

$data = tab_ejercicio_fiscal::select('id', 'in_activo')
->where('id', '=', $_POST['codigo'])
->first();

?>
<script type="text/javascript">
Ext.ns("periodoEFLista");
function change(val){
	if(val==3){
	    return '<span style="color:green;">Cerrado</span>';
	}else if(val==1){
	    return '<span style="color:red;">Abierto</span>';
	}
return val;
};
periodoEFLista.main = {
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
        periodoEFLista.main.mascara.show();
        this.msg = Ext.get('formularioperiodoEF');
        this.msg.load({
	 method:'POST',
	 params:{ef:<?php echo $_POST['codigo']; ?>},
         url:"formulacion/modulos/mantenimiento/periodo/form.php",
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
	this.codigo  = periodoEFLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	periodoEFLista.main.mascara.show();
        this.msg = Ext.get('formularioperiodoEF');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/mantenimiento/periodo/form.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Desabilitar un registro
this.eliminar= new Ext.Button({
    text:'Eliminar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = periodoEFLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Eliminar?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/mantenimiento/periodo/orm.php/eliminar',
            params:{
                id:periodoEFLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    periodoEFLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                periodoEFLista.main.mascara.hide();
            }});
	}});
    }
});

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
	width:160,
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
		periodoEFLista.main.store_lista.baseParams={};
		periodoEFLista.main.store_lista.baseParams.paginar = 'si';
		periodoEFLista.main.store_lista.baseParams.ef = '<?php echo $_POST['codigo']; ?>';
		periodoEFLista.main.store_lista.load();
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
			periodoEFLista.main.store_lista.baseParams={}
			periodoEFLista.main.store_lista.baseParams.BuscarBy = true;
			periodoEFLista.main.store_lista.baseParams.ef = '<?php echo $_POST['codigo']; ?>';
			periodoEFLista.main.store_lista.baseParams[this.paramName] = v;
			periodoEFLista.main.store_lista.baseParams.paginar = 'si';
			periodoEFLista.main.store_lista.load();
		}
	}
});

this.editar.disable();
this.eliminar.disable();

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
    autoWidth: true,
    //autoHeight:true,
height:300,
<?php if($data->in_activo == TRUE){ ?>
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.cronograma.nuevo', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.nuevo,'-',
<?php } ?>
	this.buscador,'-',
<?php if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.cronograma.editar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.editar,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.cronograma.eliminar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.eliminar
<?php } ?>
    ],
<?php }else{ ?>
    tbar:[
        this.buscador
    ],
<?php } ?>
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'Descripcion', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_apertura'},
    {header: 'Desde', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'fe_desde'},
    {header: 'Hasta', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'fe_hasta'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){periodoEFLista.main.editar.enable();periodoEFLista.main.eliminar.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 5,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

//this.gridPanel_.render("contenedorperiodoEFLista");

this.winformPanel_ = new Ext.Window({
	title:'Formulario: Cronograma',
	modal:true,
	constrain:true,
	width:714,
	height:332,
	frame:true,
	closabled:true,
	resizable: false,
	//autoHeight:true,
	items:[
		this.gridPanel_
	]
});
this.winformPanel_.show();
ejercicioLista.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.ef = '<?php echo $_POST['codigo']; ?>';
this.store_lista.load();
this.store_lista.on('load',function(){
periodoEFLista.main.editar.disable();
periodoEFLista.main.eliminar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/mantenimiento/periodo/orm.php/lista/cronograma',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'de_apertura'},
    {name: 'fe_desde'},
    {name: 'fe_hasta'}
           ]
    });
    return this.store;
}
};
Ext.onReady(periodoEFLista.main.init, periodoEFLista.main);
</script>
<div id="contenedorperiodoEFLista"></div>
<div id="formularioperiodoEF"></div>
