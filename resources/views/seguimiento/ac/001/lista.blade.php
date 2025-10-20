<script type="text/javascript">
Ext.ns("forma001Lista");
function change(val){
	if(val==true){
	    return '<tpl><div class="x-grid-row">'+'<img src="{{ asset('images/16x16/check.png') }}" style="cursor:pointer;">'+' <span style="color:green;"> Cargado</span>'+'</div></tpl>';
	}else{
	    return '<tpl><div class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> Pendiente</span>'+'</div></tpl>';
	}
return val;
};
forma001Lista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){
    
this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});
//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();
<?php $rol_planificador = array( 3, 8); ?>
//Editar un registro
this.ficha= new Ext.Button({
    text:'Ver Ficha',
    iconCls: 'icon-pdf',
    handler:function(){
			this.codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
			bajar.load({
				url: '{{ URL::to('reporte/ac/seguimiento/ficha/001') }}/'+this.codigo
			});
    }
});

this.ficha.disable();

this.ficha_acumulada= new Ext.Button({
    text:'Ver Ficha Acumulada',
    iconCls: 'icon-pdf',
    handler:function(){
			this.codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
			bajar.load({
				url: '{{ URL::to('reporte/ac/seguimiento/ficha/001') }}/'+this.codigo
			});
    }
});

this.ficha_acumulada.disable();

this.nueva = new Ext.Button({
	text:'Agregar AC',
	iconCls: 'icon-nuevo',
        handler:function(){
            addTab(99,'Agregar AC','formulacion/modulos/seguimiento_ac/editar.php','load','icon-nuevo','id_tab_lapso='+forma001Lista.main.OBJ.id);
	}
});
if(this.OBJ.activo==false){
    this.nueva.disable();
        }
this.editar = new Ext.Button({
	text:'Editar AC',
	iconCls: 'icon-editar',
        handler:function(){
            this.codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
            this.nu_codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('nu_codigo');
            this.panelCambio = Ext.getCmp('tabpanel');
            this.panelCambio.remove(this.codigo);
            addTab(this.codigo,'EDITAR A.C: '+this.nu_codigo,'formulacion/modulos/seguimiento_ac/editar.php','load','icon-editar','codigo='+this.codigo+'&id_tab_lapso='+forma001Lista.main.OBJ.id);
	}
});

this.cargar = new Ext.Button({
	text:'Editar Marco Instit.',
	iconCls: 'icon-editar',
	handler:function(){
	this.codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	forma001Lista.main.mascara.show();
			this.msg = Ext.get('formularioEditar');
			this.msg.load({
			 url:"{{ URL::to('ac/seguimiento/001/editar') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
			});
	}
});

this.cargar.disable();


this.eliminar= new Ext.Button({
    text:'Eliminar Ac',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Eliminar esta Ac?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('ac/seguimiento/001/eliminar') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    forma001Lista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                forma001Lista.main.mascara.hide();
            }});
	}});
    }
});

this.eliminar.disable();

this.editar_sector= new Ext.Button({
	text:'Editar Ejecutor/Sector',
	iconCls: 'icon-editar',
	handler:function(){
	this.codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	forma001Lista.main.mascara.show();
			this.msg = Ext.get('formularioEditar');
			this.msg.load({
			 url:"{{ URL::to('ac/seguimiento/001/editarSector') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
			});
	}
});

this.eliminar.disable();

this.extender= new Ext.Button({
    text:'Extender Ac',
    iconCls: 'icon-buscar',
    handler:function(){
	this.codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Extender esta Ac?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('ac/seguimiento/001/extender') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id')
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    forma001Lista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                forma001Lista.main.mascara.hide();
            }});
	}});
    }
});

this.extender.disable();

this.crear= new Ext.Button({
    text:'Crear Periodo',
    iconCls: 'icon-nuevo',
    handler:function(){
	this.codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	Ext.MessageBox.confirm('Confirmación', '¿Realmente desea Crear el siguente Periodo?', function(boton){
	if(boton=="yes"){
        Ext.Ajax.request({
            method:'POST',
            url:'{{ URL::to('ac/seguimiento/001/crearPeriodo') }}',
            params:{
		_token: '{{ csrf_token() }}',
                id: forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id'),
                id_tab_lapso: forma001Lista.main.OBJ.id
            },
            success:function(result, request ) {
                obj = Ext.util.JSON.decode(result.responseText);
                if(obj.success=="true"){
		    forma001Lista.main.store_lista.load();
                    Ext.Msg.alert("Notificación",obj.msg);
                }else{
                    Ext.Msg.alert("Notificación",obj.msg);
                }
                forma001Lista.main.mascara.hide();
            }});
	}});
    }
});

this.crear.disable();

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
		forma001Lista.main.store_lista.baseParams={};
		forma001Lista.main.store_lista.baseParams.paginar = 'si';
                forma001Lista.main.store_lista.baseParams.id_lapso = forma001Lista.main.OBJ.id;
		forma001Lista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		forma001Lista.main.store_lista.load();
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
			forma001Lista.main.store_lista.baseParams={}
			forma001Lista.main.store_lista.baseParams.BuscarBy = true;
                        forma001Lista.main.store_lista.baseParams.id_lapso = forma001Lista.main.OBJ.id;
			forma001Lista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			forma001Lista.main.store_lista.baseParams[this.paramName] = v;
			forma001Lista.main.store_lista.baseParams.paginar = 'si';
			forma001Lista.main.store_lista.load();
		}
	}
});

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    iconCls: 'icon-libro',
    store: this.store_lista,
    border:true,
    loadMask:true,
    autoWidth: true,
    autoHeight:true,
    tbar:[
                        @if (in_array(Session::get('rol'), $rol_planificador))
                        this.ficha,'-',this.nueva,'-',this.editar,'-',this.cargar,'-',
                        @else
                        this.ficha,'-',this.nueva,'-',this.editar,'-',this.cargar,'-',this.eliminar,'-',this.editar_sector,'-',this.extender,'-',this.crear,'-',  
			@endif
                        this.buscador
    ],
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'id',hidden:true, menuDisabled:true,dataIndex: 'id'},
		{header: 'Periodo', width:150,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_lapso'},
    {header: 'Ejecutor', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'ejecutor'},
		{header: 'Codigo', width:120,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'nu_codigo'},
    {header: 'Descripcion', width:200,  menuDisabled:true, sortable: true, renderer: textoLargo, dataIndex: 'de_ac'},
    {header: 'Estatus', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_001'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
                        if(forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('in_abierta')==true){
			forma001Lista.main.ficha.enable();
                        forma001Lista.main.ficha_acumulada.enable();
                        forma001Lista.main.crear.enable();
                        forma001Lista.main.editar.enable();
                        forma001Lista.main.nueva.enable();
                        forma001Lista.main.cargar.enable();
                        forma001Lista.main.editar_sector.enable();
                        if(forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id_tab_tipo_registro')==2){
                        forma001Lista.main.eliminar.enable();
                        }else{
                        forma001Lista.main.eliminar.disable();
                            }       
                            

                    }else{
                        if(forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('activo')==true){
			forma001Lista.main.ficha.enable();
                        forma001Lista.main.ficha_acumulada.enable();
                        forma001Lista.main.editar.enable();
                        forma001Lista.main.cargar.enable();
                        forma001Lista.main.nueva.enable();
                        forma001Lista.main.editar_sector.enable();
                        if(forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id_tab_tipo_registro')==2){
                        forma001Lista.main.eliminar.enable();
                        }else{
                        forma001Lista.main.eliminar.enable();
                            }
                        }else{                        
                        forma001Lista.main.ficha.enable();
                        forma001Lista.main.extender.enable();
                        forma001Lista.main.ficha_acumulada.enable();
                        forma001Lista.main.nueva.disable();
                        forma001Lista.main.editar.disable();
                        forma001Lista.main.cargar.disable();
                        forma001Lista.main.eliminar.disable();
                        forma001Lista.main.editar_sector.disable();
                    }
                }
		}},
    bbar: new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    }),
		sm: new Ext.grid.RowSelectionModel({
			singleSelect: true,
			/*AQUI ES DONDE ESTA EL LISTENER*/
				listeners: {
				rowselect: function(sm, row, rec) {
//					var msg = Ext.get('detalle');
//					msg.load({
//									url: '{{ URL::to('ac/seguimiento/001/detalle') }}',
//									scripts: true,
//									params: {_token:'{{ csrf_token() }}', codigo:rec.json.id},
//									text: 'Cargando...'
//					});
//					if(panel_detalle.collapsed == true)
//					{
//						panel_detalle.toggleCollapse();
//					}
				}
			}
		})
});

/*Evento Doble Click*/
//this.gridPanel_.on('rowdblclick', function( grid, row, evt){
//	panel_detalle.toggleCollapse(true);
//	this.record = forma001Lista.main.store_lista.getAt(row);
//	this.codigo = this.record.data["id"];
//	this.msg = Ext.get('detalle');
//	this.msg.load({
//	    url: '{{ URL::to('ac/seguimiento/001/detalle') }}',
//	    scripts: true,
//	    params: {_token:'{{ csrf_token() }}', codigo:this.codigo},
//	    text: "Cargando..."
//	});
//});

this.panel = new Ext.Panel({
	layout: "fit",
	border: false,
	padding: 5,
	items: [
		this.gridPanel_
	]
});

this.panel.render("contenedorforma001Lista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.id_lapso = this.OBJ.id;
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
forma001Lista.main.ficha.disable();
forma001Lista.main.ficha_acumulada.disable();
forma001Lista.main.editar.disable();
forma001Lista.main.cargar.disable();
forma001Lista.main.eliminar.disable();
forma001Lista.main.editar_sector.disable();
forma001Lista.main.extender.disable();
forma001Lista.main.crear.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
	    url:'{{ URL::to('ac/seguimiento/001/storeLista') }}',
	    root:'data',
	    fields:[
		    {name: 'id'},
				{name: 'id_ejecutor'},
				{name: 'id_tab_ejecutores'},
		    {name: 'tx_ejecutor_ac'},
				{name: 'nu_codigo'},
		    {name: 'de_ac'},
                    {name: 'de_lapso'},
                    {name: 'activo'},
                    {name: 'in_abierta'},
                    {name: 'id_tab_tipo_registro'},
				{name: 'in_001'},
				{
						name: 'ejecutor',
						convert: function(v, r) {
								return r.id_ejecutor + ' - ' + r.tx_ejecutor_ac;
						}
				},
				{
						name: 'periodo',
						convert: function(v, r) {
								return r.fe_inicio + ' - ' + r.fe_fin;
						}
				}
	    ]
    });
    return this.store;
}
};
Ext.onReady(forma001Lista.main.init, forma001Lista.main);
</script>
<div id="contenedorforma001Lista"></div>
<div id="formularioacseguimiento"></div>
<div id="formularioEditar"></div>
