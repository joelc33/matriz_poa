<?php        
session_start(); 
if( $_SESSION['estatus'] !== 'OK' ) {
    http_response_code(403);
	die();
} 
?>
<script type="text/javascript">
Ext.ns("rolLista");
rolLista.main = {
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
        rolLista.main.mascara.show();
        this.msg = Ext.get('formulariorol');
        this.msg.load({
         url:"formulacion/modulos/rol/nuevoRol.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Editar un registro
this.editar= new Ext.Button({
    text:'Privilegios',
    iconCls: 'icon-login',
    handler:function(){
	this.codigo  = rolLista.main.gridPanel_.getSelectionModel().getSelected().get('co_rol');
	rolLista.main.mascara.show();
        this.msg = Ext.get('formulariorol');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         //url:"rol/editarRol.php?codigo="+this.codigo,
         url:"formulacion/modulos/rol/editarRol.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//filtro
this.filtro = new Ext.Button({
    text:'Filtro',
    iconCls: 'icon-buscar',
    handler:function(){
        this.msg = Ext.get('filtrorol');
        rolLista.main.mascara.show();
        rolLista.main.filtro.setDisabled(true);
        this.msg.load({
             url: 'rol/filtro.php',
             scripts: true
        });
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
		rolLista.main.store_lista.baseParams={};
		rolLista.main.store_lista.baseParams.paginar = 'si';
		rolLista.main.store_lista.load();
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
			rolLista.main.store_lista.baseParams={}
			rolLista.main.store_lista.baseParams.BuscarBy = true;
			rolLista.main.store_lista.baseParams[this.paramName] = v;
			rolLista.main.store_lista.baseParams.paginar = 'si';
			rolLista.main.store_lista.load();
		}
	}
});

this.editar.disable();

//Editar un registro
this.ver= new Ext.Button({
    text:'Opciones',
    iconCls: 'icon-organizacion',
    handler:function(){
	this.codigo  = rolLista.main.gridPanel_.getSelectionModel().getSelected().get('co_rol');
	rolLista.main.mascara.show();
        this.msg = Ext.get('contenedoropcionLista');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/autenticacion/opcion/lista.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.ver.disable();


//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    //title:'Lista de Roles',iconCls: 'icon-privilegio',
    store: this.store_lista,
    loadMask:true,border:false,
//    frame:true,
//    height:350,
    autoWidth: true,
    autoHeight:true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'privilegios.nuevo', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.nuevo,'-',
<?php } ?>
	this.buscador,'-',
<?php if( in_array( array( 'de_privilegio' => 'privilegios.privilegios', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.editar,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'privilegios.opciones', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.ver
<?php } ?>
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_rol',hidden:true, menuDisabled:true,dataIndex: 'co_rol'},
    {header: 'Nombre del Rol', width:200,  menuDisabled:true, sortable: true,  dataIndex: 'tx_rol'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){rolLista.main.editar.enable();rolLista.main.ver.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 10,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorrolLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){
rolLista.main.editar.disable();
rolLista.main.ver.disable();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/rol/funcion.php',
    root:'data',
    fields:[
    {name: 'co_rol'},
    {name: 'tx_rol'},
           ]
    });
    return this.store;
}
};
Ext.onReady(rolLista.main.init, rolLista.main);
</script>
<div id="contenedorrolLista"></div>
<div id="contenedoropcionLista"></div>
<div id="formulariorol"></div>
<div id="filtrorol"></div>
</body>
</html>
