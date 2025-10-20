<?php         
$sql = "SELECT * FROM proyecto_seguimiento.tab_proyecto_localizacion WHERE id_tab_proyecto='".$id_proyecto."';";
$result = $comunes->ObtenerFilasBySqlSelect($sql);
foreach($result as $key => $row){
	$data3 = json_encode(array(
		"co_proyecto_localizacion"     => trim($row["id"]),
		"id_proyecto"     => trim($row["id_tab_proyecto"]),
		"co_ambito"     => trim($row["id_tab_ambito_localizacion"]),
		"tx_locacion"     => trim($row["tx_otra_locacion"]),
	));
}
?>
<script type="text/javascript">
Ext.ns("tabuladorTres");
tabuladorTres.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data3 ?>'});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista_ubicacion = this.getListaUbicacion();
this.store_lista_comuna = this.getListaComuna();

//<Stores de fk>
this.storeCO_AMBITO = this.getStoreCO_AMBITO();
//<Stores de fk>

//<ClavePrimaria>
this.co_proyecto_localizacion = new Ext.form.Hidden({
	name:'co_proyecto_localizacion',
	value:this.OBJ.co_proyecto_localizacion
});
this.id_proyecto = new Ext.form.Hidden({
	name:'id_proyecto',
	value:this.OBJ.id_proyecto
});
//</ClavePrimaria>

//Agregar un registro
this.nuevo = new Ext.Button({
    text:'Agregar',
    iconCls: 'icon-nuevo',
    handler:function(){
        tabuladorTres.main.mascara.show();
        this.msg = Ext.get('formulario_ubicacionS');
        this.msg.load({
         url:"formulacion/modulos/seguimiento_proyecto/nuevaLocalizacion.php",
         scripts: true,
	 params: {id_proyecto:editarProyectoS.main.id_proyecto.getValue()},
         text: "Cargando.."
        });
    }
});

//Eliminar un registro
this.eliminar= new Ext.Button({
    text:'Quitar',
    iconCls: 'icon-eliminar',
    handler:function(){
	this.codigo  = tabuladorTres.main.gridPanel_.getSelectionModel().getSelected().get('co_proyecto_localizacion_nacional');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea eliminar este registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
            params:{
                co_proyecto_localizacion_nacional:tabuladorTres.main.gridPanel_.getSelectionModel().getSelected().get('co_proyecto_localizacion_nacional'), op: 26
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    tabuladorTres.main.store_lista_ubicacion.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                tabuladorTres.main.mascara.hide();
            }});
	}});
    }
});

<?php if($co_estatus==3){?>
this.nuevo.disable();
<?php }?>

this.eliminar.disable();

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    border:true,
    store: this.store_lista_ubicacion,
    loadMask:true,
    autoHeight:true,
    tbar:[
        this.nuevo,'-',this.eliminar
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_localizacion_nacional',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_localizacion_nacional'},
    {header: '3.2.1. ESTADO(S)', width:200,  menuDisabled:true, sortable: true,  dataIndex: 'tx_estado'},
    {header: '3.2.2. MUNICIPIO(S)', width:200,  menuDisabled:true, sortable: true,  dataIndex: 'tx_municipio'},
    {header: '3.2.3. PARROQUIA(S)', width:250,  menuDisabled:true, sortable: true,  dataIndex: 'tx_parroquia'},
    {header: '3.4.1. PAIS', width:200,  menuDisabled:true, sortable: true,  dataIndex: 'tx_pais'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
<?php if($co_estatus==1){?>
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){tabuladorTres.main.eliminar.enable();}},
<?php }?>
    bbar: new Ext.PagingToolbar({
        pageSize: 10,
        store: this.store_lista_ubicacion,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.fieldset2 = new Ext.form.FieldSet({
	title: '3.2. LOCALIZACIÓN INTERNACIONAL, NACIONAL, ESTADAL, MUNICIPAL O PARROQUIAL',
	autoWidth:true,
        items:[
		this.gridPanel_
		]
});

//Agregar un registro
this.nuevoComunal = new Ext.Button({
    text:'Agregar',
    iconCls: 'icon-nuevo',
    handler:function(){
        tabuladorTres.main.mascara.show();
        this.msg = Ext.get('formulario_ubicacionS');
        this.msg.load({
         url:"formulacion/modulos/seguimiento_proyecto/nuevaComuna.php",
         scripts: true,
	 params: {id_proyecto:editarProyectoS.main.id_proyecto.getValue()},
         text: "Cargando.."
        });
    }
});

//Eliminar un registro
this.eliminarComunal= new Ext.Button({
    text:'Quitar',
    iconCls: 'icon-eliminar',
    handler:function(){
	this.codigo  = tabuladorTres.main.gridPanelComunal_.getSelectionModel().getSelected().get('co_proyecto_localizacion_comunal');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea eliminar este registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
            params:{
                co_proyecto_localizacion_comunal:tabuladorTres.main.gridPanelComunal_.getSelectionModel().getSelected().get('co_proyecto_localizacion_comunal'), op: 28
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    tabuladorTres.main.store_lista_comuna.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                tabuladorTres.main.mascara.hide();
            }});
	}});
    }
});

<?php if($co_estatus==3){?>
this.nuevoComunal.disable();
<?php }?>

this.eliminarComunal.disable();

//Grid principal
this.gridPanelComunal_ = new Ext.grid.GridPanel({
    border:true,
    store: this.store_lista_comuna,
    loadMask:true,
    autoHeight:true,
    tbar:[
        this.nuevoComunal,'-',this.eliminarComunal
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_localizacion_comunal',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_localizacion_comunal'},
    {header: 'CÓDIGO DE COMUNA', width:150,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo_comuna'},
    {header: 'AGREGACIÓN COMUNAL', width:200,  menuDisabled:true, sortable: true,  dataIndex: 'tx_agregacion_comunal'},
    {header: 'ESTADO(S)', width:150,  menuDisabled:true, sortable: true,  dataIndex: 'tx_estado'},
    {header: 'MUNICIPIO(S)', width:200,  menuDisabled:true, sortable: true,  dataIndex: 'tx_municipio'},
    {header: 'PARROQUIA(S)', width:200,  menuDisabled:true, sortable: true,  dataIndex: 'tx_parroquia'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
<?php if($co_estatus==1){?>
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){tabuladorTres.main.eliminarComunal.enable();}},
<?php }?>
    bbar: new Ext.PagingToolbar({
        pageSize: 10,
        store: this.store_lista_comuna,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.fieldset3 = new Ext.form.FieldSet({
	title: '3.3. LOCALIZACIÓN COMUNAL',
	autoWidth:true,
        items:[
		this.gridPanelComunal_
		]
});


this.co_ambito = new Ext.form.ComboBox({
	fieldLabel:'3.1. ÁMBITO',
	store: this.storeCO_AMBITO,
	typeAhead: true,
	valueField: 'co_ambito',
	displayField:'tx_ambito',
	hiddenName:'co_ambito',
	//readOnly:(this.OBJ.co_ambito!='')?true:false,
	//style:(this.OBJ.co_ambito!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Ambito',
	selectOnFocus: true,
	mode: 'local',
	width:200,
	resizable:true,
	//allowBlank:false
});

this.storeCO_AMBITO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_ambito,
	value:  this.OBJ.co_ambito,
	objStore: this.storeCO_AMBITO
});

this.tx_locacion = new Ext.form.TextField({
	fieldLabel:'EN CASO DE SER OTRO INDIQUE LA LOCALIZACIÓN',
	name:'tx_locacion',
	value:this.OBJ.tx_locacion,
	width:400,
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.co_ambito,
		this.fieldset2,
		this.fieldset3,
		this.tx_locacion
		]
});

this.panelDatos = new Ext.Panel({
    title: '3.  LOCALIZACIÓN  DEL PROYECTO',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.fieldset1,
		this.co_proyecto_localizacion
	]
});

//Cargar el grid
this.store_lista_ubicacion.baseParams.paginar = 'si';
this.store_lista_ubicacion.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista_ubicacion.load();
this.store_lista_ubicacion.on('beforeload',function(){
panel_detalle.collapse();
});
this.store_lista_comuna.baseParams.paginar = 'si';
this.store_lista_comuna.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista_comuna.load();
this.store_lista_comuna.on('beforeload',function(){
panel_detalle.collapse();
});
},
getStoreCO_AMBITO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 18
	},
        fields:[
            {name: 'co_ambito'},{name: 'tx_ambito'}
            ]
    });
    return this.store;
},
getListaUbicacion: function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 19
	},
	fields:[
	{name: 'co_proyecto_localizacion_nacional'},
	{name: 'tx_pais'},
	{name: 'tx_estado'},
	{name: 'tx_municipio'},
	{name: 'tx_parroquia'},
	   ]
    });
    return this.store;
},
getListaComuna: function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 20
	},
	fields:[
	{name: 'co_proyecto_localizacion_comunal'},
	{name: 'tx_codigo_comuna'},
	{name: 'tx_agregacion_comunal'},
	{name: 'tx_estado'},
	{name: 'tx_municipio'},
	{name: 'tx_parroquia'},
	   ]
    });
    return this.store;
}
};
Ext.onReady(tabuladorTres.main.init, tabuladorTres.main);
</script>
