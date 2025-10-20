<script type="text/javascript">
Ext.ns("forma005Lista");
function change(val){
	if(val==true){
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/check.png') }}" style="cursor:pointer;">'+' <span style="color:green;"> Cargado</span>'+'</div></tpl>';
	}else{
	    return '<tpl><div style="margin-bottom: -4px; margin-top: -4px;" class="x-grid-row">'+'<img src="{{ asset('images/16x16/seguimiento.png') }}" style="cursor:pointer;">'+' <span style="color:red;"> Pendiente</span>'+'</div></tpl>';
	}
return val;
};
function movimiento(val){
	if(val==true){
	    return '<span style="color:green;">Si</span>';
	}else if(val==false){
	    return '<span style="color:red;">No</span>';
	}
return val;
};
forma005Lista.main = {
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
			this.codigo  = forma005Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
			bajar.load({
				url: '{{ URL::to('reporte/ac/seguimiento/ficha/005') }}/'+this.codigo
			});
    }
});

this.ficha.disable();

this.ficha_acumulada= new Ext.Button({
    text:'Ver Ficha Acumulada',
    iconCls: 'icon-pdf',
    handler:function(){
			this.codigo  = forma005Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
			bajar.load({
				url: '{{ URL::to('reporte/ac/seguimiento/ficha/005/acumulada') }}/'+this.codigo
			});
    }
});

this.ficha_acumulada.disable();

this.cargar = new Ext.Button({
	text:'Editar AE',
	iconCls: 'icon-editar',
	handler:function(){
            this.codigo  = forma005Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
                    forma005Lista.main.mascara.show();
			this.msg = Ext.get('formularioacseguimiento');
			this.msg.load({
			 url:"{{ URL::to('ac/seguimiento/005/datos/lista') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
			});
                    }
});
this.cargar.disable();

this.cargar_admin = new Ext.Button({
	text:'Editar AE',
	iconCls: 'icon-editar',
	handler:function(){
            this.codigo  = forma005Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
                    forma005Lista.main.mascara.show();
			this.msg = Ext.get('formularioacseguimiento');
			this.msg.load({
			 url:"{{ URL::to('ac/seguimiento/005/datos/lista') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
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
		forma005Lista.main.store_lista.baseParams={};
		forma005Lista.main.store_lista.baseParams.paginar = 'si';
                forma005Lista.main.store_lista.baseParams.id_lapso = forma005Lista.main.OBJ.id;
		forma005Lista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
		forma005Lista.main.store_lista.load();
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
			forma005Lista.main.store_lista.baseParams={}
			forma005Lista.main.store_lista.baseParams.BuscarBy = true;
                        forma005Lista.main.store_lista.baseParams.id_lapso = forma005Lista.main.OBJ.id;
			forma005Lista.main.store_lista.baseParams._token = '{{ csrf_token() }}';
			forma005Lista.main.store_lista.baseParams[this.paramName] = v;
			forma005Lista.main.store_lista.baseParams.paginar = 'si';
			forma005Lista.main.store_lista.load();
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
                        this.ficha,'-',this.ficha_acumulada,'-',this.cargar,'-',
                        @else
                        this.ficha,'-',this.ficha_acumulada,'-',this.cargar_admin,'-',
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
    {header: 'Estatus', width:80,  menuDisabled:true, sortable: true, renderer: change, dataIndex: 'in_005'},
    ],
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    listeners:{cellclick:function(Grid, rowIndex, columnIndex,e ){
            if(forma005Lista.main.gridPanel_.getSelectionModel().getSelected().get('in_abierta')==true){
			forma005Lista.main.ficha.enable();
                        forma005Lista.main.ficha_acumulada.enable();
                        forma005Lista.main.cargar.enable();                
            }else{
                        if(forma005Lista.main.gridPanel_.getSelectionModel().getSelected().get('activo')==true){
			forma005Lista.main.ficha.enable();
                        forma005Lista.main.ficha_acumulada.enable();
                        forma005Lista.main.cargar.enable();
                    }else{
                        forma005Lista.main.ficha.enable();
                        forma005Lista.main.ficha_acumulada.enable();
                        forma005Lista.main.cargar.disable();
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
//									url: '{{ URL::to('ac/seguimiento/005/detalle') }}',
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
//	this.record = forma005Lista.main.store_lista.getAt(row);
//	this.codigo = this.record.data["id"];
//	this.msg = Ext.get('detalle');
//	this.msg.load({
//	    url: '{{ URL::to('ac/seguimiento/005/detalle') }}',
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

this.panel.render("contenedorforma005Lista");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.id_lapso = this.OBJ.id;
this.store_lista.baseParams._token = '{{ csrf_token() }}';
this.store_lista.load();
this.store_lista.on('load',function(){
forma005Lista.main.ficha.disable();
forma005Lista.main.ficha_acumulada.disable();
forma005Lista.main.cargar.disable();
});
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
    this.store = new Ext.data.JsonStore({
	    url:'{{ URL::to('ac/seguimiento/005/storeLista') }}',
	    root:'data',
	    fields:[
		    {name: 'id'},
				{name: 'id_ejecutor'},
				{name: 'id_tab_ejecutores'},
		    {name: 'tx_ejecutor_ac'},
				{name: 'nu_codigo'},
                                {name: 'activo'},
		    {name: 'de_ac'},
                    {name: 'de_lapso'},
                    {name: 'in_abierta'},
				{name: 'in_005'},
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
Ext.onReady(forma005Lista.main.init, forma005Lista.main);
</script>
<div id="contenedorforma005Lista"></div>
<div id="formularioacseguimiento"></div>
