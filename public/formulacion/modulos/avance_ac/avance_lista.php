<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}  
include("../../configuracion/ConexionComun.php");

$codigo = decode($_POST['codigo']);
$data = json_encode(array(
	"co_meta"     => $codigo
));

$comunes = new ConexionComun();
$sql = "SELECT mo_ae as total FROM ac_seguimiento.tab_ac_ae where id=".$codigo;
$resultado = $comunes->ObtenerFilasBySqlSelect($sql);
$resultadoReal = $resultado[0]['total'];

$sql2 = "SELECT * FROM mantenimiento.tab_lapso where id_tab_ejercicio_fiscal=2015 order by nu_lapso ASC";
$resultado2 = $comunes->ObtenerFilasBySqlSelect($sql2);

$sql3 = "SELECT nu_lapso FROM mantenimiento.tab_lapso where id_tab_ejercicio_fiscal=2015 and in_activo is true";
$resultado3 = $comunes->ObtenerFilasBySqlSelect($sql3);
$tab_activo = $resultado3[0]['nu_lapso'];

$sql4 = "SELECT *, ac_seguimiento.sp_ac_ae_mo_metafin(id) AS mo_cargado FROM ac_seguimiento.tab_meta_fisica where id=".$codigo;
$resultado4 = $comunes->ObtenerFilasBySqlSelect($sql4);

$datosEnunciado='Avance de Actividades';
$url_avance='formulacion/modulos/avance_ac/avance_lista.php';
$variable="+'&id_proyecto='+avanceListaSAP.main.gridPanel_.getSelectionModel().getSelected().get('id_proyecto')";
?>
<script type="text/javascript">
Ext.ns("avanceListaSAP");
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
avanceListaSAP.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

<?php foreach($resultado2 as $key => $row): ?>
//objeto store
this.store_lista_<?php echo trim($row["nu_lapso"])?> = this.getLista_<?php echo trim($row["nu_lapso"])?>();
<?php endforeach; ?>

//<ClavePrimaria>
this.co_meta = new Ext.form.Hidden({
	name:'co_meta',
	value:this.OBJ.co_meta
});

this.datos = '<p class="registro_detalle"><b>Codigo: </b><?php echo $resultado4[0]["codigo"]?>  <b>Nombre: </b><?php echo $resultado4[0]["nb_meta"]?></p>';
this.datos += '<p class="registro_detalle"><b>Fecha de Inicio: </b><?php echo trim(date_format(date_create($resultado4[0]["fecha_inicio"]),"d/m/Y"));?>  <b>Fecha de Cierre: </b><?php echo trim(date_format(date_create($resultado4[0]["fecha_fin"]),"d/m/Y"));?><b> Progreso: </b>0%</p>';
this.datos += '<p class="registro_detalle"><b>Monto Registrado: </b>'+formatoNumero(<?php echo $resultado4[0]["mo_cargado"]?>)+'<b> Monto Causado: </b>0.00</p>';

this.fieldDatos = new Ext.form.FieldSet({
	title: 'Datos de la Actividad',
	autoWidth: true,
	autoHeight:true,
	html: this.datos,
});

<?php foreach($resultado2 as $key => $row): ?>
this.nuevo_<?php echo trim($row["nu_lapso"])?>= new Ext.Button({
    text:'Nuevo Avance',
    iconCls: 'icon-nuevo',
    handler:function(){
	avanceListaSAP.main.mascara.show();
        this.msg = Ext.get('formulario_actividad<?php echo $codigo;?>');
        this.msg.load({
	 method:'POST',
	 params:{id_tab_meta_fisica:avanceListaSAP.main.co_meta.getValue(), nu_lapso: <?php echo trim($row["nu_lapso"])?>, tx_lapso: '<?php echo trim($row["nu_lapso"])?> Desde: <?php echo trim(date_format(date_create($row["fe_inicio"]),"d/m/Y"));?> Hasta: <?php echo trim(date_format(date_create($row["fe_fin"]),"d/m/Y"));?>'},
         url:"formulacion/modulos/avance_ac/editar_avance.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Editar un registro
this.editar_<?php echo trim($row["nu_lapso"])?>= new Ext.Button({
    text:'Editar Avance',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = avanceListaSAP.main.gridPanel_<?php echo trim($row["nu_lapso"])?>.getSelectionModel().getSelected().get('id');
	avanceListaSAP.main.mascara.show();
        this.msg = Ext.get('formulario_actividad<?php echo $codigo;?>');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo, nu_lapso: <?php echo trim($row["nu_lapso"])?>, tx_lapso: '<?php echo trim($row["nu_lapso"])?> Desde: <?php echo trim(date_format(date_create($row["fe_inicio"]),"d/m/Y"));?> Hasta: <?php echo trim(date_format(date_create($row["fe_fin"]),"d/m/Y"));?>'},
         url:"formulacion/modulos/avance_ac/editar_avance.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.buscador_<?php echo trim($row["nu_lapso"])?> = new Ext.form.TwinTriggerField({
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
		avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams={};
		avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams.paginar = 'si';
		avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams.co_meta = avanceListaSAP.main.co_meta.getValue();
		avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.load();
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
			avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams={}
			avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams.BuscarBy = true;
			avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams[this.paramName] = v;
			avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams.paginar = 'si';
			avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams.co_proyecto_acc_espec = avanceListaSAP.main.co_proyecto_acc_espec.getValue();
			avanceListaSAP.main.store_lista_<?php echo trim($row["nu_lapso"])?>.load();
		}
	}
});

this.editar_<?php echo trim($row["nu_lapso"])?>.disable();
<?php endforeach; ?>

<?php foreach($resultado2 as $key => $row): ?>
//Grid principal
this.gridPanel_<?php echo trim($row["nu_lapso"])?> = new Ext.grid.GridPanel({
    store: this.store_lista_<?php echo trim($row["nu_lapso"])?>,
    title: 'Lapso: <?php echo trim($row["nu_lapso"])?> Desde: <?php echo trim(date_format(date_create($row["fe_inicio"]),"d/m/Y"));?> Hasta: <?php echo trim(date_format(date_create($row["fe_fin"]),"d/m/Y"));?>',
    iconCls: 'icon-periodo',
    loadMask:true,
    border:false,
    disabled:<?php if($row["in_activo"]=='f'){echo 'true';}else{echo 'false';}?> ,
    autoHeight:true,
    autoWidth: true,
    tbar:[
        this.nuevo_<?php echo trim($row["nu_lapso"])?>,'-',this.editar_<?php echo trim($row["nu_lapso"])?>,'-',this.buscador_<?php echo trim($row["nu_lapso"])?>
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
    {header: 'INICIO', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'fe_inicio'},
    {header: 'FINAL', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'fe_fin'},
    {header: 'PARTIDA', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nu_partida'},
    {header: 'PONDERACION %', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nu_ponderacion'},
    {header: 'MONTO', width:120,  menuDisabled:true, sortable: true, renderer: colorCargado, dataIndex: 'mo_presupuesto'}
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){avanceListaSAP.main.editar_<?php echo trim($row["nu_lapso"])?>.enable();}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista_<?php echo trim($row["nu_lapso"])?>,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    }),
	sm: new Ext.grid.RowSelectionModel({
		singleSelect: true,
		//AQUI ES DONDE ESTA EL LISTENER
			listeners: {
			rowselect: function(sm, row, rec) {
                                            var msg = Ext.get('detalle');
                                            msg.load({
                                                    url: 'formulacion/modulos/avance_ac/detalle.php',
                                                    scripts: true,
                                                    params: {codigo:rec.json.id},
                                                    text: 'Cargando...'
                                            });
				if(panel_detalle.collapsed == true)
				{
				panel_detalle.toggleCollapse();
				}    
			}
		}
	})
});
<?php endforeach; ?>

this.TabPanel = new Ext.TabPanel({
    activeTab:<?php echo $tab_activo-1; ?>,
    autoHeight:true,
    enableTabScroll:true,
    deferredRender: false,
    border:true,
    items:[
<?php foreach($resultado2 as $key => $row): ?>
	this.gridPanel_<?php echo trim($row["nu_lapso"])?>,
<?php endforeach; ?>
	]
});

this.panel = new Ext.Panel({
	layout: "fit",
	border: false,
	padding	: 5,
	autoHeight:true,
	autoWidth: true,
	autoScroll:true,
	items: [this.fieldDatos,this.TabPanel]
});

this.panel.render("contenedoravanceListaSAP<?php echo $codigo;?>");

<?php foreach($resultado2 as $key => $row): ?>
//Cargar el grid
this.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams.paginar = 'si';
this.store_lista_<?php echo trim($row["nu_lapso"])?>.baseParams.id_tab_meta_fisica = this.OBJ.co_meta;
this.store_lista_<?php echo trim($row["nu_lapso"])?>.load();
this.store_lista_<?php echo trim($row["nu_lapso"])?>.on('load',function(){
avanceListaSAP.main.editar_<?php echo trim($row["nu_lapso"])?>.disable();
});
this.store_lista_<?php echo trim($row["nu_lapso"])?>.on('beforeload',function(){
panel_detalle.collapse();
});
<?php endforeach; ?>

},
<?php foreach($resultado2 as $key => $row): ?>
getLista_<?php echo trim($row["nu_lapso"])?>: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/avance_ac/funcion.php',
    root:'data',
    baseParams: {
	op: 4,
	nu_lapso: <?php echo trim($row["nu_lapso"])?>
    },
    fields:[
    {name: 'id'},
    {name: 'id_tab_meta_fisica'},
    {name: 'nu_partida'},
    {name: 'mo_presupuesto'},
    {name: 'nu_ponderacion'},
    {name: 'fe_inicio'},
    {name: 'fe_fin'},
    {name: 'tx_observacion'},
           ]
    });
    return this.store;
},
<?php endforeach; ?>
};
Ext.onReady(avanceListaSAP.main.init, avanceListaSAP.main);
</script>
<div id="contenedoravanceListaSAP<?php echo $codigo;?>"></div>
<div id="formulario_actividad<?php echo $codigo;?>"></div>
<div id="formulario_ubicacion"></div>
