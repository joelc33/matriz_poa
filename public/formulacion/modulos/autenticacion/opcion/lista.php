<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}
?>
<script type="text/javascript">
Ext.ns("opcionLista");
function change(val){
	if(val==3){
	    return '<span style="color:green;">Cerrado</span>';
	}else if(val==1){
	    return '<span style="color:red;">Abierto</span>';
	}
return val;
};
opcionLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
imagenEstatus: function(val){
	if(val==''){
		return '<img align="bottom" style="vertical-align:middle;height:12px;" src="images/16x16/cancel.gif"> Inactivo'
	}
	if(val=='1'){
		return '<img align="bottom" style="vertical-align:middle;height:12px;" src="images/16x16/check.png"> Habilitado'
	}
},
init:function(){

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

//Habilitar un registro
this.si= new Ext.Button({
    text:'SI',
    iconCls: 'icon-fin',
    handler:function(){
	this.codigo  = opcionLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea SI?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/autenticacion/opcion/orm.php/si',
            params:{
                id:opcionLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    opcionLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                opcionLista.main.mascara.hide();
            }});
	}});
    }
});

//Desabilitar un registro
this.no= new Ext.Button({
    text:'NO',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = opcionLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea NO?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/autenticacion/opcion/orm.php/no',
            params:{
                id:opcionLista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    opcionLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                opcionLista.main.mascara.hide();
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
		opcionLista.main.store_lista.baseParams={};
		opcionLista.main.store_lista.baseParams.paginar = 'si';
		opcionLista.main.store_lista.baseParams.rol = '<?php echo $_POST['codigo']; ?>';
		opcionLista.main.store_lista.load();
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
			opcionLista.main.store_lista.baseParams={}
			opcionLista.main.store_lista.baseParams.BuscarBy = true;
			opcionLista.main.store_lista.baseParams.rol = '<?php echo $_POST['codigo']; ?>';
			opcionLista.main.store_lista.baseParams[this.paramName] = v;
			opcionLista.main.store_lista.baseParams.paginar = 'si';
			opcionLista.main.store_lista.load();
		}
	}
});

this.si.disable();
this.no.disable();

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
    autoWidth: true,
    //autoHeight:true,
height:300,
    tbar:[
        this.buscador,'-',
<?php if( in_array( array( 'de_privilegio' => 'privilegios.opciones.si', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.si,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'privilegios.opciones.no', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.no
<?php } ?>
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'Descripcion', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_menu'},
    {header: 'Opcion', width:200,  menuDisabled:true, sortable: true,  dataIndex: 'de_privilegio'},
    {header: 'Estado', width:120, renderer: opcionLista.main.imagenEstatus, menuDisabled:true, sortable: true,  dataIndex: 'in_habilitado'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){opcionLista.main.si.enable();opcionLista.main.no.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 40,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

//this.gridPanel_.render("contenedoropcionLista");

this.winformPanel_ = new Ext.Window({
	title:'Formulario: Opcion',
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
rolLista.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.rol = '<?php echo $_POST['codigo']; ?>';
this.store_lista.load();
this.store_lista.on('load',function(){
opcionLista.main.si.disable();
opcionLista.main.no.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/autenticacion/opcion/orm.php/lista/opcion',
    root:'data',
    fields:[
    {name: 'id'},
    {name: 'de_menu'},
    {name: 'de_privilegio'},
    {name: 'in_habilitado'},
           ]
    });
    return this.store;
}
};
Ext.onReady(opcionLista.main.init, opcionLista.main);
</script>
<div id="contenedoropcionLista"></div>
<div id="formularioperiodoEF"></div>
