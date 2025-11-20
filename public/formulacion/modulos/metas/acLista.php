<?php
session_start(); 
if( $_SESSION['estatus'] !== 'OK' ) {
    http_response_code(403);
	die();
} 
include("../../configuracion/ConexionComun.php");

$codigo = decode($_POST['id_tab_t47_ac_accion_especifica']);
$data = json_encode(array(
    	"co_proyecto_acc_espec"     => $_POST['codigo'],
	"id_accion_centralizada"     => $_POST['id_accion_centralizada'],
	"id_tab_t47_ac_accion_especifica"     => $codigo
));

$comunes = new ConexionComun();
$sql = "SELECT ('AC' || t24b.id_ejecutor || id_ejercicio || lpad(t46.id_accion::text, 5, '0')) as id_ac, t47.monto, in_definitivo,id_ejercicio FROM t47_ac_accion_especifica as t47 
	inner join t46_acciones_centralizadas as t46 on t47.id_accion_centralizada=t46.id
	inner join mantenimiento.tab_ejecutores as t24b on t46.id_ejecutor=t24b.id_ejecutor
where t47.id_tab_t47_ac_accion_especifica=".$codigo;
$resultado = $comunes->ObtenerFilasBySqlSelect($sql);
$resultadoIdProyecto = $resultado[0]['id_ac'];
$resultadoReal = $resultado[0]['monto'];
$resultadoDefinitivo = $resultado[0]['in_definitivo'];
$id_ejercicio = $resultado[0]['id_ejercicio'];
?>
<script type="text/javascript">
Ext.ns("metaLista");
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

    Ext.define('AccionCentralizada.Distribucion', {
        extend: 'Ext.Window',
        xtype: 'accion_centralizada_distribucion',
        constructor: function(config) {
            var self = this;

            var campos = ['1', '2', '3', '4', '5', '6',
                '7', '8', '9', '10', '11', '12', 'min', 'max'
            ].map(function(e) {
                return {
                    name: e,
                    type: 'int'
                };
            });

            this.store = Ext.create({
                xtype: 'jsonstore',
                proxy: new Ext.data.HttpProxy({
                    method: 'POST',
                    api: {
                        read: 'formulacion/modulos/accionCentralizada/funcion.php?op='
                            + config.leer,
                        update: 'formulacion/modulos/accionCentralizada/funcion.php?op='
                            + config.actualizar
                    }
                }),
                baseParams: {
                    co_metas: config.co_metas,
                },
                writer: new Ext.data.JsonWriter({
                    encode: true,
                    writeAllFields: false
                }),
                autoLoad: true,
                autoSave: false,
                autoDestroy: true,
                root: 'data',
                fields: [
                    'id'
                ].concat(campos, [
                    't1', 't2', 't3', 't4',
                    'tot', 'totc'
                ])
            });

            this.store.on('exception', function(s, t, a, o, r) {
                Ext.Msg.alert('Error almacenando los cambios', r.raw.msg)
                    .setIcon(Ext.MessageBox.ERROR);
                self.store.reload();
            });

            var editor = new Ext.ux.grid.RowEditor({
                saveText: 'Ok',
                cancelText: 'Cancelar'
            });

            editor.on('validateedit', function(re, chg, rec, idx) {
                var acum = 0,
                    i, k, keys;
                keys = Object.keys(chg);
                for (i = 0; i < keys.length; i++) {
                    k = keys[i];
                    if (k < rec.data.min || k > rec.data.max) {
                        Ext.Msg.alert( 'Atención',
                            'No pueden asignarse recursos a meses fuera'
                                + ' del rango de la actividad'
                        ).setIcon(Ext.MessageBox.WARNING);
                        return false;
                    }
                    acum += chg[k] - rec.data[k];
                }
                if (acum !== 0) {
                    Ext.Msg.alert( 'Atención',
                            'La cantidad total por actividad debe'
                            + ' coincidir con el declarado'
                    ).setIcon(Ext.MessageBox.ERROR);
                    return false;
                }
                return true;
            });

            var meses = [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
                'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ].map(function(e, i) {
                return {
                    header: e,
                    dataIndex: (i + 1).toString(),
                    editor: {
                        xtype: 'numberfield',
                        allowBlank: false,
                        allowDecimals: false,
                        allowNegative: false
                    }
                };
            });

            var trimestres = [1, 2, 3, 4].map(function(e) {
                return {
                    header: 'Trimestre ' + e,
                    dataIndex: 't' + e,
                    renderer: function(value, metaData, record) {
                        var i, t = e - 1, acum = 0;
                        for (i = (t * 3) + 1; i < (t * 3) + 3 + 1; i++) {
                            acum += record.data[i.toString()];
                        }
                        return config.formato(acum);
                    }
                };
            });

            var columnas = [];

            meses.forEach(function(e, i) {
                columnas.push(e);
                if ((i+1) % 3 === 0) {
                    columnas.push(trimestres.shift());
                }
            });

            this.grid = Ext.create({
                xtype: 'grid',
                store: self.store,
                plugins: [editor],
                flex: 1,
                stripeRows: true,
                viewConfig: {
                    autoFit: true
                },
                minColumnWidth: 70,
                bbar: [{
                    text: 'Editar',
                    iconCls: 'icon-editar',
                    handler: function() {
                        var r = self.grid.getSelectionModel().getSelected();
                        editor.startEditing(self.store.indexOf(r), false);
                    }
                }, '->', {
                    text: 'Guardar',
                    iconCls: 'icon-guardar',
                    handler: function() {
                        self.store.save();
                    }
                }],
                colModel: new Ext.grid.ColumnModel({
                    defaults: {
                        menuDisabled: false,
                        sortable: true,
                        editor: {
                            xtype: 'numberfield',
                            readOnly: true
                        },
                        renderer: config.formato
                    },
                    columns: [{
                        header: 'NÚMERO',
                        width: 60,
                        dataIndex: 'id',
                        renderer: null
                    }].concat(columnas, [{
                        header: 'TOTAL',
                        dataIndex: 'tot'
                    }])
                })
            });

            config = Ext.apply({
                title: 'Distribución ' + config.tipo
                    + ' de la Actividad: ' + config.codigo,
                modal: true,
                maximizable: true,
                resizable: true,
                x: 0,
                y: 0,
                width: 500,
                height: 400,
                layout: 'fit',
                items: [
                    this.grid
                ]
            }, config);

            this.callParent(arguments);


        }
    });

metaLista.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

//<ClavePrimaria>
this.id_tab_t47_ac_accion_especifica = new Ext.form.Hidden({
	name:'id_tab_t47_ac_accion_especifica',
	value:this.OBJ.id_tab_t47_ac_accion_especifica
});


this.co_proyecto_acc_espec = new Ext.form.Hidden({
	name:'co_proyecto_acc_espec',
	value:this.OBJ.co_proyecto_acc_espec
});
//</ClavePrimaria>
this.id_accion_centralizada = new Ext.form.Hidden({
	name:'id_accion_centralizada',
	value:this.OBJ.id_accion_centralizada
});
//</ClavePrimaria>

this.id_ejercicio = new Ext.form.Hidden({
	name:'id_ejercicio',
	value:'<?php echo $id_ejercicio ?>'
});
//Agregar un registro
this.nuevo = new Ext.Button({
    text:'Nueva Actividad',
    iconCls: 'icon-nuevo',
    handler:function(){
        metaLista.main.mascara.show();
        this.msg = Ext.get('formulario_actividad<?php echo $codigo;?>');
        this.msg.load({
         url:"formulacion/modulos/metas/editarMetaAC.php",
	 params: {co_proyecto_acc_espec:metaLista.main.co_proyecto_acc_espec.getValue(),id_accion_centralizada:metaLista.main.id_accion_centralizada.getValue(),id_ejercicio:metaLista.main.id_ejercicio.getValue(),id_tab_t47_ac_accion_especifica:metaLista.main.id_tab_t47_ac_accion_especifica.getValue()},
         scripts: true,
         text: "Cargando.."
        });
    }
});

//Editar un registro
this.editar= new Ext.Button({
    text:'Editar Actividad',
    iconCls: 'icon-editar',
    handler:function(){
	this.codigo  = metaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_metas');
	metaLista.main.mascara.show();
        this.msg = Ext.get('formulario_actividad<?php echo $codigo;?>');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/metas/editarMetaAC.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

this.eliminar= new Ext.Button({
    text:'Borrar',
    iconCls: 'icon-eliminar',
    handler:function(){
	this.codigo  = metaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_planes_zulia');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Borrar Actividad?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/metas/funcion.php?op=12',
            params:{
                co_metas:metaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_metas')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    metaLista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                metaLista.main.mascara.hide();
            }});
	}});
    }
});

this.cuadrar = new Ext.Button({
    text:'"Cerrar si Cuadra"',
    iconCls: 'icon-guardar',
    handler:function(){
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea cerrar las Actividades:<br><b>Nota:</b> No se podran modificar los datos.', function(boton){
	if(boton=="yes"){

        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/metas/orm.php',
            params:{
                id_tab_t47_ac_accion_especifica:metaLista.main.id_tab_t47_ac_accion_especifica.getValue(),
                op:2
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    metaLista.main.store_lista.load();
                    //Ext.Msg.alert("Notificación",obj.msg);
		    Ext.MessageBox.show({
			       title: 'Notificación',
			       msg: obj.msg,
			       buttons: Ext.MessageBox.OK,
			       icon: Ext.MessageBox.INFO
		    });
		this.panelCambio = Ext.getCmp('tabpanel');
		this.panelCambio.remove('<?php echo trim($resultadoIdProyecto); ?>ae');
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
                }
                metaLista.main.mascara.hide();
            }});

	}});
   
    }
});

this.ver= new Ext.Button({
    text:'Ver Actividad',
    iconCls: 'icon-buscar',
    handler:function(){
	this.codigo  = metaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_metas');
	metaLista.main.mascara.show();
        this.msg = Ext.get('formulario_actividad<?php echo $codigo;?>');
        this.msg.load({
	 method:'POST',
	 params:{codigo:this.codigo},
         url:"formulacion/modulos/metas/verMetaAc.php",
         scripts: true,
         text: "Cargando.."
        });
    }
});

<?php
$usuario = (object) $_SESSION;

if( $usuario->co_rol < 3 ):?>

this.reabrir = new Ext.Button({
    text:'"Reabrir Actividades"',
    iconCls: 'icon-reabrir',
    handler:function(){
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Reabrir las Actividades?', function(boton){
	if(boton=="yes"){

        Ext.Ajax.request({
            method:'POST',
            url:'formulacion/modulos/metas/orm.php',
            params:{
                id_tab_t47_ac_accion_especifica:metaLista.main.id_tab_t47_ac_accion_especifica.getValue(),
                op:6
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success==true){
		    metaLista.main.store_lista.load();
                    //Ext.Msg.alert("Notificación",obj.msg);
		    Ext.MessageBox.show({
			       title: 'Notificación',
			       msg: obj.msg,
			       buttons: Ext.MessageBox.OK,
			       icon: Ext.MessageBox.INFO
		    });
		this.panelCambio = Ext.getCmp('tabpanel');
		this.panelCambio.remove('<?php echo trim($resultadoIdProyecto); ?>ae');
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
                }
                metaLista.main.mascara.hide();
            }});

	}});
   
    }
});

<?php endif;?>

this.verDistribucion = new Ext.Button({
text: 'Distribución Financiera',
iconCls: 'icon-reporteest',
disabled: true,
etiquetas: {
    ver: true
},
handler: function(btn) {
    
        this.codigo  = metaLista.main.gridPanel_.getSelectionModel().getSelected().get('tx_codigo');
        this.co_metas  = metaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_metas');    
    var v = Ext.create({
        xtype: 'accion_centralizada_distribucion',
        ac: self.ac,
        leer: 22,
        actualizar: 24,
        codigo: this.codigo,
        co_metas: this.co_metas,
        tipo: 'Financiera',
        formato: formatoNumero
    });
    v.show();
}
});

this.verDistribucionFisica = new Ext.Button({
    text: 'Distribución Física',
    iconCls: 'icon-reporteest',
    disabled: true,
    etiquetas: {
        ver: true
    },
    handler: function(btn) {
        
        this.codigo  = metaLista.main.gridPanel_.getSelectionModel().getSelected().get('tx_codigo');
        this.co_metas  = metaLista.main.gridPanel_.getSelectionModel().getSelected().get('co_metas');
        var v = Ext.create({
            xtype: 'accion_centralizada_distribucion',
            ac: self.ac,
            leer: 23,
            actualizar: 25,
            codigo: this.codigo,
            co_metas: this.co_metas,
            tipo: 'Física',
            formato: function(v) { return v; }
        });
        v.show();
    }
});

this.editar.disable();
this.eliminar.disable();
this.ver.disable();
this.verDistribucion.disable();
this.verDistribucionFisica.disable();

this.resultadoReal = new Ext.form.DisplayField({
	value:"<b>Monto: Bs. <?php echo number_format($resultadoReal, 2, ',', '.'); ?></b>"
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
		metaLista.main.store_lista.baseParams={};
		metaLista.main.store_lista.baseParams.paginar = 'si';
		metaLista.main.store_lista.baseParams.id_tab_t47_ac_accion_especifica = metaLista.main.id_tab_t47_ac_accion_especifica.getValue();
		metaLista.main.store_lista.load();
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
			metaLista.main.store_lista.baseParams={}
			metaLista.main.store_lista.baseParams.BuscarBy = true;
			metaLista.main.store_lista.baseParams[this.paramName] = v;
			metaLista.main.store_lista.baseParams.paginar = 'si';
			metaLista.main.store_lista.baseParams.id_tab_t47_ac_accion_especifica = metaLista.main.id_tab_t47_ac_accion_especifica.getValue();
			metaLista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    store: this.store_lista,
    loadMask:true,
    border:false,
    autoHeight:true,
    autoWidth: true,
<?php if($resultadoDefinitivo <> true){ ?>
    tbar:[
<?php if( in_array( array( 'de_privilegio' => 'ac.ae.actividad.nueva', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.nuevo,'-',
<?php } ?>
	this.buscador,'-',
<?php if( in_array( array( 'de_privilegio' => 'ac.ae.actividad.editar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.editar,'-',
<?php } ?>
<?php if( in_array( array( 'de_privilegio' => 'ac.ae.actividad.borrar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	this.eliminar,'-',this.verDistribucion,'-',this.verDistribucionFisica
<?php } ?>
    ],
<?php }else{ ?>
    tbar:[
        this.ver,'-',
	this.buscador,'->',
<?php if( in_array( array( 'de_privilegio' => 'ac.ae.actividad.reabrir', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
	<?php if( $usuario->co_rol < 3 ):?>
	this.reabrir 
	<?php endif;?>
<?php } ?>
    ],
<?php } ?>
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_metas',hidden:true, menuDisabled:true,dataIndex: 'co_metas'},
    {header: 'CODIGO', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'tx_codigo'},
    {header: 'ACTIVIDAD', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nb_meta'},
    {header: 'UNIDAD DE MEDIDA', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'co_unidades_medida'},
    {header: 'PROGRAMADO ANUAL ', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'tx_prog_anual'},
    {header: 'INICIO', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'fecha_inicio'},
    {header: 'FINAL', width:100,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'fecha_fin'},
    {header: 'TOTAL CARGADO', width:120,  menuDisabled:true, sortable: true, renderer: colorCargado, dataIndex: 'mo_cargado'},
    {header: 'INDICADOR', width:120,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nb_responsable'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    viewConfig: {
	forceFit:true,
	enableRowBody:true,
	showPreview:true,
    },
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){metaLista.main.editar.enable();metaLista.main.eliminar.enable();metaLista.main.ver.enable();metaLista.main.verDistribucion.enable();metaLista.main.verDistribucionFisica.enable();}},
    bbar: [ 
	new Ext.PagingToolbar({
		pageSize: 20,
		store: this.store_lista,
		displayInfo: true,
		displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
		emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    	}),'-',
	new Ext.ux.StatusBar({
	    id: 'statusbar-actividad<?php echo $codigo;?>',
	    autoScroll:true,
	    defaults:{style:'color:red;font-size:15px;',autoWidth:true},
	    items:[
		this.resultadoReal,'-',
<?php if( in_array( array( 'de_privilegio' => 'ac.ae.actividad.cerrar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
		<?php if($resultadoDefinitivo <> true){ ?>this.cuadrar<?php }else{} ?>
<?php } ?>
		]
    	}),'-'
	]
});

this.gridPanel_.render("contenedormetaLista<?php echo $codigo;?>");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.id_tab_t47_ac_accion_especifica = this.OBJ.id_tab_t47_ac_accion_especifica;
this.store_lista.load();
this.store_lista.on('load',function(){
metaLista.main.editar.disable();
metaLista.main.eliminar.disable();
metaLista.main.ver.disable();
metaLista.main.verDistribucion.disable();
metaLista.main.verDistribucionFisica.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/metas/funcion.php?op=8',
    root:'data',
    fields:[
    {name: 'co_metas'},
    {name: 'tx_codigo'},
    {name: 'co_proyecto_acc_espec'},
    {name: 'nb_meta'},
    {name: 'co_unidades_medida'},
    {name: 'tx_prog_anual'},
    {name: 'fecha_inicio'},
    {name: 'fecha_fin'},
    {name: 'nb_responsable'},
    {name: 'mo_cargado'},
           ]
    });
    return this.store;
}
};
Ext.onReady(metaLista.main.init, metaLista.main);
</script>
<div id="contenedormetaLista<?php echo $codigo;?>"></div>
<div id="formulario_actividad<?php echo $codigo;?>"></div>
<div id="formulario_ubicacion"></div>
