<?php        
session_start();
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}
?>
<script type="text/javascript">
Ext.ns("proyectoOrdenarEditar");
function change(val){
	if(val==3){
	    return '<span style="color:green;">Cerrado</span>';
	}else if(val==1){
	    return '<span style="color:red;">Abierto</span>';
	}
return val;
};
proyectoOrdenarEditar.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

//reordenar un registro
this.reordenar= new Ext.Button({
    text:'Reordenar Codigos',
    iconCls: 'icon-cambiar',
    handler:function(){
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Reordenar Proyectos?', function(boton){
	if(boton=="yes"){
	proyectoOrdenarEditar.main.mascara.show();
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/mantenimiento/proyectoOrdenar/orm.php/reordenar',
            params:{
                ejecutor:'<?php echo $_POST['codigo']; ?>'
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    proyectoOrdenarEditar.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                    proyectoOrdenarEditar.main.mascara.hide();
                }else{
			var errores = '';
			for(datos in obj.msg){
				errores += obj.msg[datos] + '<br>';
			}
                    //Ext.Msg.alert("Notificación", errores);
		    Ext.MessageBox.show({
			       title: 'Notificación',
			       msg: errores,
			       buttons: Ext.MessageBox.OK,
			       icon: Ext.MessageBox.WARNING
		    });
                    proyectoOrdenarEditar.main.mascara.hide();
                }
            }});
	}});
    }
});

<?php 
session_start();
	$ef = array(2015,2016);
	if (in_array($_SESSION['ejercicio_fiscal'], $ef)){
?>

	this.reordenar.disable();

<?php } ?>

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
		proyectoOrdenarEditar.main.store_lista.baseParams={};
		proyectoOrdenarEditar.main.store_lista.baseParams.paginar = 'si';
		proyectoOrdenarEditar.main.store_lista.baseParams.ejecutor = '<?php echo $_POST['codigo']; ?>';
		proyectoOrdenarEditar.main.store_lista.load();
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
			proyectoOrdenarEditar.main.store_lista.baseParams={}
			proyectoOrdenarEditar.main.store_lista.baseParams.BuscarBy = true;
			proyectoOrdenarEditar.main.store_lista.baseParams.ejecutor = '<?php echo $_POST['codigo']; ?>';
			proyectoOrdenarEditar.main.store_lista.baseParams[this.paramName] = v;
			proyectoOrdenarEditar.main.store_lista.baseParams.paginar = 'si';
			proyectoOrdenarEditar.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    iconCls: 'icon-libro',
    store: this.store_lista,border:false,
    loadMask:true,
    autoWidth: true,
    //autoHeight:true,
height:300,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'reordenar.proyecto.ver.reordenar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.reordenar,'-',
<?php } ?>
	this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'Codigo', width:130,  menuDisabled:true, sortable: true,  dataIndex: 'id_proyecto'},
    {header: 'Nombre', width:300,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nombre'},
    {header: 'Monto', width:130,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'monto'},
    {header: 'Estatus', width:100,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'co_estatus'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    bbar: new Ext.PagingToolbar({
        pageSize: 5,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

//this.gridPanel_.render("contenedorproyectoOrdenarEditar");

this.winformPanel_ = new Ext.Window({
	title:'Formulario: Proyectos',
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
proyectoOrdenarLista.main.mascara.hide();

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.ejecutor = '<?php echo $_POST['codigo']; ?>';
this.store_lista.load();
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/mantenimiento/proyectoOrdenar/orm.php/lista/proyecto',
    root:'data',
    fields:[
    {name: 'id_proyecto'},
    {name: 'nombre'},
    {name: 'monto'},
    {name: 'tx_estatus'},
    {name: 'co_estatus'}
           ]
    });
    return this.store;
}
};
Ext.onReady(proyectoOrdenarEditar.main.init, proyectoOrdenarEditar.main);
</script>
<div id="contenedorproyectoOrdenarEditar"></div>
<div id="formulariotramitetimbre"></div>
