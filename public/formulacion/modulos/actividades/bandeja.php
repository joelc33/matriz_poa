<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}  
?>
<script type="text/javascript">
Ext.ns("bandejaLista");
function barra(value, meta, rec, row, col, store){
    var porcentaje = value;
    var miBarra = Ext.id();
    (function(){
        new Ext.ProgressBar({
	    text: porcentaje+' %',
	    animate : {duration: 1,easing: 'bounceOut'},
            renderTo: miBarra,
            value: porcentaje/100
        });
    }).defer(25)
    return '<span id="' + miBarra + '"></span>';
};
function change(val){
	if(val=="t"){
	    return '<span style="color:green;">Activo</span>';
	}else if(val=="f"){
	    return '<span style="color:red;">Inactivo</span>';
	}
return val;
};
bandejaLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
//<Stores de fk>
this.storeCO_DOCUMENTO = this.getStoreCO_DOCUMENTO();
//<Stores de fk>
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

this.co_documento = new Ext.form.ComboBox({
	fieldLabel:'documento',
	store: this.storeCO_DOCUMENTO,
	typeAhead: true,
	valueField: 'co_documento',
	displayField:'inicial',
	hiddenName:'co_documento',
	//readOnly:(this.OBJ.co_documento!='')?true:false,
	//style:(this.main.OBJ.co_documento!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'...',
	selectOnFocus: true,
	mode: 'local',
	width:40,
	resizable:true,
//	allowBlank:false
});
this.storeCO_DOCUMENTO.load();

this.nu_cedula_rif = new Ext.form.TextField({
	fieldLabel:'Nu cedula',
	name:'nu_cedula_rif',
	value:'',
	width:155
});

this.compositefieldCIRIF = new Ext.form.CompositeField({
fieldLabel: 'Cedula / Rif',
items: [
	this.co_documento,
	this.nu_cedula_rif,
	]
});

this.tx_razon_social = new Ext.form.TextField({
	fieldLabel:'Nombre',
	name:'tx_razon_social',
	value:'',
	width:500
});

this.desde = new Ext.form.DateField({
	fieldLabel:'Fecha Desde',
	width: '150px',
	name:'fe_desde'
});

this.hasta = new Ext.form.DateField({
	fieldLabel:'Fecha Hasta',
	width: '150px',
	name:'fe_hasta'
});

this.compositefieldFecha = new Ext.form.CompositeField({
fieldLabel: 'Fecha Desde',
items: [
	this.desde,
             {
                   xtype: 'displayfield',
                   value: '&nbsp;&nbsp;&nbsp; Fecha Hasta:',
                   width: 100
             },
	this.hasta
	]
});

/**
* <Form Principal que carga el Filtro>
*/
this.formFiltroPrincipal = new Ext.form.FormPanel({
    title:'Lista de Solicitudes',
    iconCls: 'icon-libro',
    collapsible: true,
    titleCollapse: true,
    autoWidth:true,
    border:false,
    labelWidth: 110,
    padding:'10px',
    items: [
        this.compositefieldCIRIF,
        this.tx_razon_social,
	this.compositefieldFecha
    ],
    buttonAlign:'center',
    buttons:[
        {
            text:'Consultar',
            iconCls:'icon-buscar',
            handler:function(){
                bandejaLista.main.aplicarFiltroByFormulario();
            }
        },
        {
            text:'Limpiar',
            iconCls:'icon-limpiar',
            handler:function(){
                bandejaLista.main.limpiarCamposByFormFiltro();
            }
        }
    ]
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    //title:'Lista de ramoes',iconCls: 'icon-privilegio',
    store: this.store_lista,
    loadMask:true,border:true,
//    frame:true,
//    height:350,
    autoWidth: true,
    autoHeight:true,
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_solicitud',hidden:true, menuDisabled:true,dataIndex: 'co_solicitud'},
    {header: 'Fecha', width:70,  menuDisabled:true, sortable: true,  dataIndex: 'tx_fecha'},
    {header: 'Tipo Solicitud', width:300,  menuDisabled:true, sortable: true,  dataIndex: 'tx_ramo'},
    {header: 'Rif', width:100,  menuDisabled:true, sortable: true,  dataIndex: 'tx_doc_rif'},
    {header: 'Nombre / Razon Social', width:200,  menuDisabled:true, sortable: true,  dataIndex: 'nb_razon_social'},
    {header: 'Estado', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'tx_estado'},
    {header: 'Progreso', width:100,  menuDisabled:true, sortable: true, renderer: barra, dataIndex: 'nu_progreso'},
    ],
    stripeRows: true,
    autoScramol:true,
    stateful: true,
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
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
                                                    url: 'formulacion/modulos/actividades/detalle.php',
                                                    scripts: true,
                                                    params: {codigo:rec.json.co_solicitud},
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

this.panel = new Ext.Panel({
//	title: 'Lista de contribuyente',
	border:false,
	items: [this.formFiltroPrincipal,this.gridPanel_]
});

this.panel.render("contenedorbandejaLista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.load();
this.store_lista.on('load',function(){

});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
    url:'formulacion/modulos/actividades/funcion.php?op=2',
    root:'data',
    fields:[
    {name: 'co_solicitud'},
    {name: 'tx_fecha'},
    {name: 'tx_doc_rif'},
    {name: 'nb_razon_social'},
    {name: 'tx_estado'},
    {name: 'tx_prioridad'},
    {name: 'nu_progreso'},
           ]
    });
    return this.store;
},
getStoreCO_DOCUMENTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/actividades/funcion.php?op=1',
        root:'data',
        fields:[
            {name:'co_documento'},
            {name:'inicial'}
        ]
    });
    return this.store;
},
aplicarFiltroByFormulario: function(){
	//Capturamos los campos con su value para posteriormente verificar cual
	//esta lleno y trabajar en base a ese.
	var campo = bandejaLista.main.formFiltroPrincipal.getForm().getValues();

	bandejaLista.main.store_lista.baseParams={}

	var swfiltrar = false;
	for(campName in campo){
	    if(campo[campName]!=''){
		swfiltrar = true;
		eval(" bandejaLista.main.store_lista.baseParams."+campName+" = '"+campo[campName]+"';");
	    }
	}
	if(swfiltrar==true){
	    bandejaLista.main.store_lista.baseParams.BuscarBy = true;
	    bandejaLista.main.store_lista.load();
	}else{
	    Ext.MessageBox.show({
		       title: 'Notificación',
		       msg: 'Debe ingresar un parametro de busqueda',
		       buttons: Ext.MessageBox.OK,
		       icon: Ext.MessageBox.WARNING
	    });
	}
},
limpiarCamposByFormFiltro: function(){
	bandejaLista.main.formFiltroPrincipal.getForm().reset();
	bandejaLista.main.store_lista.baseParams={};
	bandejaLista.main.store_lista.load();
}
};
Ext.onReady(bandejaLista.main.init, bandejaLista.main);
</script>
<div id="contenedorbandejaLista"></div>
<div id="formularioramo"></div>
<div id="filtroramo"></div>
