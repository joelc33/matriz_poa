<?php
	$data7 = json_encode(array(
		"co_proyecto_localizacion"     => "",
		"id_proyecto"     => $id_proyecto,
		"co_ambito"     => "",
		"tx_locacion"     => "",
	));
?>
<script type="text/javascript">
Ext.ns("tabuladorSiete");
function color(val){
	if(val=="Presupuestaria (Bs)"){
	    return '<span style="color:green;">'+val+'</span>';
	}else if(val=="Fisico"){
	    return '<span style="color:red;">'+val+'</span>';
	}
return val;
};
tabuladorSiete.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data7 ?>'});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista_accion = this.getListaAccion();
this.store_lista_fisica = this.getListaFisica();

//<ClavePrimaria>
this.id_proyecto = new Ext.form.Hidden({
	name:'id_proyecto',
	value:this.OBJ.id_proyecto
});
//</ClavePrimaria>

//Importar registros
this.cargarAccion= new Ext.Button({
    text:'Agregar',
    iconCls: 'icon-agregar',
    handler:function(){
	tabuladorSiete.main.mascara.show();
        this.msg = Ext.get('formulario_ubicacion');
        this.msg.load({
         url:"formulacion/modulos/accionEspecifica/editarAccionEspecifica.php",
	 params: {id_proyecto:editarProyecto.main.id_proyecto.getValue()},
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Editar un registro
this.editarAccion= new Ext.Button({
    text:'Editar',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = tabuladorSiete.main.gridPanelAccion_.getSelectionModel().getSelected().get('co_proyecto_acc_espec');
	tabuladorSiete.main.mascara.show();
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
		addTab(tabuladorSiete.main.gridPanelAccion_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'),'Proyecto '+tabuladorSiete.main.gridPanelAccion_.getSelectionModel().getSelected().get('id_proyecto')+', Accion Especifica '+tabuladorSiete.main.gridPanelAccion_.getSelectionModel().getSelected().get('tx_codigo'),'formulacion/modulos/accionEspecifica/listaPartidas.php','load','icon-reporteest','codigo='+tabuladorSiete.main.gridPanelAccion_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'));
		/*var direccionar = Ext.get('contenedortabuladorSiete');
		direccionar.load({ url: 'formulacion/modulos/proyecto/editarProyecto.php', scripts: true, text: 'Cargando...',              params:'codigo='+tabuladorSiete.main.gridPanel_.getSelectionModel().getSelected().get('co_proyectos')});*/
	}
});

//Eliminar un registro
this.eliminar= new Ext.Button({
    text:'Quitar',
    iconCls: 'icon-eliminar',
    handler:function(){
	this.codigo  = tabuladorSiete.main.gridPanelAccion_.getSelectionModel().getSelected().get('co_proyecto_acc_espec');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea eliminar este registro?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/accionEspecifica/funcion.php?op=5',
            params:{
                co_proyecto_acc_espec:tabuladorSiete.main.gridPanelAccion_.getSelectionModel().getSelected().get('co_proyecto_acc_espec')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    tabuladorSiete.main.store_lista_accion.load();
		    tabuladorSiete.main.store_lista_fisica.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                tabuladorSiete.main.mascara.hide();
            }});
	}});
    }
});

this.cargarPartida = new Ext.Button({
    text:'Cargar Partidas',
    iconCls: 'icon-excel',
    handler:function(){
	tabuladorSiete.main.mascara.show();
        this.msg = Ext.get('formulario_ubicacion');
        this.msg.load({
	 params:{codigo:'<?php echo $id_proyecto; ?>'},
         url:"formulacion/modulos/accionEspecifica/cargarPartida.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.descargarFormato = new Ext.Button({
    text: 'Descargar Formato',
    iconCls: 'icon-descargar',
    handler: function(){
        bajar.load({
            url: 'formulacion/modulos/descargas/FORMATO_AE_PARTIDAS_<?php echo $_SESSION['ejercicio_fiscal']; ?>.xlsx'
        });
    }
});

this.verFisico = new Ext.Button({
	text:'Ver Partidas',
	id:'verFisico',
	iconCls: 'icon-reporteest',
	handler: function(boton){
		addTab(tabuladorSiete.main.gridPanelFisica_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'),'Proyecto '+tabuladorSiete.main.gridPanelFisica_.getSelectionModel().getSelected().get('id_proyecto')+', Accion Especifica '+tabuladorSiete.main.gridPanelFisica_.getSelectionModel().getSelected().get('tx_codigo'),'formulacion/modulos/accionEspecifica/listaPartidas.php','load','icon-reporteest','codigo='+tabuladorSiete.main.gridPanelFisica_.getSelectionModel().getSelected().get('co_proyecto_acc_espec'));
		/*var direccionar = Ext.get('contenedortabuladorSiete');
		direccionar.load({ url: 'formulacion/modulos/proyecto/editarProyecto.php', scripts: true, text: 'Cargando...',              params:'codigo='+tabuladorSiete.main.gridPanel_.getSelectionModel().getSelected().get('co_proyectos')});*/
	}
});

//reordenar un registro
this.reordenar_ae = new Ext.Button({
    text:'Reordenar AE',
    iconCls: 'icon-cambiar',
    handler:function(){
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Reordenar las Acciones Especificas?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/accionEspecifica/orm.php',
            params:{
                op: 1,
                proyecto: editarProyecto.main.id_proyecto.getValue()
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    tabuladorSiete.main.store_lista_accion.load();
		    tabuladorSiete.main.store_lista_fisica.load();
                    //Ext.Msg.alert("Notificación",obj.msg);
		    Ext.MessageBox.show({
			       title: 'Notificación',
			       msg: obj.msg,
			       buttons: Ext.MessageBox.OK,
			       icon: Ext.MessageBox.INFO
		    });
                }else{
                    //Ext.Msg.alert("Notificación",obj.msg);
			var errores = '';
			for(datos in obj.msg){
				errores += obj.msg[datos] + '<br>';
			}
		    Ext.MessageBox.show({
			       title: 'Notificación',
			       msg: errores,
			       buttons: Ext.MessageBox.OK,
			       icon: Ext.MessageBox.WARNING
		    });
                }
                tabuladorSiete.main.mascara.hide();
            }});
	}});
    }
});

<?php if($co_estatus==3){?>
this.cargarPartida.disable();
this.cargarAccion.disable();
this.reordenar_ae.disable();
<?php }?>

this.eliminar.disable();
this.editarAccion.disable();
this.verDetalle.disable();
this.verFisico.disable();

//Grid principal
this.gridPanelAccion_ = new Ext.grid.GridPanel({
    store: this.store_lista_accion,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.agregar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.cargarAccion,
	'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.editar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.editarAccion,
	'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.quitar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.eliminar,
	'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.reordenar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.reordenar_ae,
	'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.cargarpartida', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.cargarPartida,
	'-',
<?php } ?>
	this.descargarFormato,
	'-',
	this.verDetalle
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_acc_espec',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_acc_espec'},
    {header: 'id_proyecto',hidden:true, menuDisabled:true,dataIndex: 'id_proyecto'},
    {header: 'CÓD.', width:50,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo'},
    {header: 'Nombre de la Acción', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'descripcion'},
    {header: 'UNIDAD DE MEDIDA', width:120,  menuDisabled:true, sortable: true,  dataIndex: 'co_unidades_medida'},
    {header: 'META', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'meta'},
    {header: 'PONDERACIÓN (%)', width:120,  menuDisabled:true, sortable: true,  dataIndex: 'ponderacion'},
    {header: 'BIEN O SERVICIO', width:120,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'bien_servicio'},
    {header: 'TOTAL GENERAL Bs.', width:120,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'total'},
    {header: 'MONTO CARGADO Bs.', width:140,  menuDisabled:true, sortable: true, renderer: formatoNumero, dataIndex: 'mo_cargado'},
    {header: 'FECHA DE INICIO', width:120,  menuDisabled:true, sortable: true,  dataIndex: 'fec_inicio'},
    {header: 'FECHA DE CULMINACIÓN', width:150,  menuDisabled:true, sortable: true,  dataIndex: 'fec_termino'},
    {header: 'UNIDAD EJECUTORA RESPONSABLE', width:250,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'co_ejecutores'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
<?php if($co_estatus==1){?>
	tabuladorSiete.main.editarAccion.enable();
	tabuladorSiete.main.eliminar.enable();
<?php }?>
	tabuladorSiete.main.verDetalle.enable();
}},
    bbar: new Ext.PagingToolbar({
        pageSize: 10,
        store: this.store_lista_accion,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
	this.gridPanelAccion_
		]
});

//Editar un registro
this.editarFisico= new Ext.Button({
    text:'Editar Fisico',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = tabuladorSiete.main.gridPanelFisica_.getSelectionModel().getSelected().get('co_proyecto_acc_espec_rec');
	tabuladorSiete.main.mascara.show();
        this.msg = Ext.get('formulario_ubicacion');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/accionDistribucion/editarFisico.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Editar un registro
this.editarFinanciero= new Ext.Button({
    text:'Editar Financiero',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = tabuladorSiete.main.gridPanelFisica_.getSelectionModel().getSelected().get('co_proyecto_acc_espec_rec');
	tabuladorSiete.main.mascara.show();
        this.msg = Ext.get('formulario_ubicacion');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/accionDistribucion/editarFinanciero.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.editarFisico.disable();
this.editarFinanciero.disable();

this.gridPanelFisica_ = new Ext.grid.GridPanel({
    store: this.store_lista_fisica,
    loadMask:true,
    border:true,
    autoHeight:true,
    autoWidth: true,
    tbar:[
        this.verFisico,
	'-',
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.editarfisico', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.editarFisico,
	'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'proyecto.ae.editarfinanciero', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.editarFinanciero
<?php } ?>
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_proyecto_acc_espec_rec',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_acc_espec_rec'},
    {header: 'id_proyecto',hidden:true, menuDisabled:true,dataIndex: 'id_proyecto'},
    {header: 'co_proyecto_acc_espec',hidden:true, menuDisabled:true,dataIndex: 'co_proyecto_acc_espec'},
    {header: 'CÓD.', width:50,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo'},
    {header: 'Nombre de la Acción', width:250,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'descripcion'},
    {header: 'DISTRIBUCIÓN', width:150,  menuDisabled:true, sortable: true, renderer: color, dataIndex: 'tx_distribucion'},
    {header: 'Enero', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_01'},
    {header: 'Febrero', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_02'},
    {header: 'Marzo', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_03'},
    {header: 'Trimestre I', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'trimestre_01'},
    {header: 'Abril', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_04'},
    {header: 'Mayo', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_05'},
    {header: 'Junio', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_06'},
    {header: 'Trimestre II', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'trimestre_02'},
    {header: 'Julio', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_07'},
    {header: 'Agosto', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_08'},
    {header: 'Septiembre', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_09'},
    {header: 'Trimestre III', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'trimestre_03'},
    {header: 'Octubre', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_10'},
    {header: 'Noviembre', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_11'},
    {header: 'Diciembre', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'presup_12'},
    {header: 'Trimestre IV', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'trimestre_04'},
    {header: 'Total', width:100,  menuDisabled:true, sortable: true, /*renderer: formatoNumero,*/ dataIndex: 'mo_total'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
	tabuladorSiete.main.verFisico.enable();
<?php if($co_estatus==1){?>
	tabuladorSiete.main.editarFisico.enable();
	tabuladorSiete.main.editarFinanciero.enable();
<?php }?>
}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista_fisica,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.fieldset2 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
	this.gridPanelFisica_
		]
});

this.panelDatos71 = new Ext.Panel({
    title: '7.1.  ACCIONES ESPECÍFICAS DEL PROYECTO ',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    autoWidth: true,
    autoScroll:true,
    items:[
	this.fieldset1
	]
});

this.panelDatos72 = new Ext.Panel({
    title: '7.2. PROGRAMACIÓN FÍSICA/PRESUPUESTARIA (Bs.)  DE LAS ACCIONES ESPECÍFICAS',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    autoWidth: true,
    autoScroll:true,
    items:[
	this.fieldset2
	]
});

this.panelDatos = new Ext.TabPanel({
    activeTab:0,
    enableTabScroll:true,
    deferredRender: false,
    title: '7. ACCIONES ESPECÍFICAS Y PROGRAMACIÓN POR ACCIONES ESPECÍFICAS',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[this.panelDatos71,this.panelDatos72]
});

//Cargar el grid
this.store_lista_accion.baseParams.paginar = 'si';
this.store_lista_accion.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista_accion.load();
this.store_lista_accion.on('load',function(){
tabuladorSiete.main.editarAccion.disable();
tabuladorSiete.main.verDetalle.disable();
tabuladorSiete.main.eliminar.disable();
});
this.store_lista_accion.on('beforeload',function(){
panel_detalle.collapse();
});
this.store_lista_fisica.baseParams.paginar = 'si';
this.store_lista_fisica.baseParams.id_proyecto = this.OBJ.id_proyecto;
this.store_lista_fisica.load();
this.store_lista_fisica.on('load',function(){
tabuladorSiete.main.verFisico.disable();
tabuladorSiete.main.editarFisico.disable();
tabuladorSiete.main.editarFinanciero.disable();
});
this.store_lista_fisica.on('beforeload',function(){
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
    {name: 'mo_cargado'},
    {name: 'fec_inicio'},
    {name: 'fec_termino'},
    {name: 'co_ejecutores'},
           ]
    });
    return this.store;
},
getListaFisica: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/accionEspecifica/funcion.php?op=6',
    root:'data',
    fields:[
    {name: 'co_proyecto_acc_espec_rec'},
    {name: 'co_proyecto_acc_espec'},
    {name: 'id_proyecto'},
    {name: 'tx_codigo'},
    {name: 'tx_distribucion'},
    {name: 'descripcion'},
    {name: 'presup_01'},
    {name: 'presup_02'},
    {name: 'presup_03'},
    {name: 'trimestre_01'},
    {name: 'presup_04'},
    {name: 'presup_05'},
    {name: 'presup_06'},
    {name: 'trimestre_02'},
    {name: 'presup_07'},
    {name: 'presup_08'},
    {name: 'presup_09'},
    {name: 'trimestre_03'},
    {name: 'presup_10'},
    {name: 'presup_11'},
    {name: 'presup_12'},
    {name: 'trimestre_04'},
    {name: 'mo_total'},
           ]
    });
    return this.store;
}
};
Ext.onReady(tabuladorSiete.main.init, tabuladorSiete.main);
</script>
