@extends('home')

@section('htmlheader_title') {{ Session::get('ejercicio') }} @endsection

@section('main-content')

<script type="text/javascript">
(function(){
    var mensaje = {
        title: 'Error',
        msg: 'Su sesión ha expirado. Debe volver a identificarse.',
        buttons: Ext.Msg.OK,
        icon: Ext.MessageBox.ERROR,
        fn: function(){
            document.location.href = 'autenticar';
        }
    };
    Ext.Ajax.on('requestcomplete', function(conn, resp){
        var head, rHead;

        head = "<!DOCTYPE html PUBLIC";
        rHead = resp.responseText.substring(0, head.length);
        if ( rHead == head ) {
            Ext.Msg.show(mensaje);
        }
    });
    Ext.Ajax.on('requestexception', function(conn, resp){
        if ( resp.status === 403 ) {
            Ext.Msg.show(mensaje);
        }
    });
}());

Ext.QuickTips.init();
Ext.form.Field.prototype.msgTarget = 'side';
Ext.override(Ext.data.Connection, { timeout : 240000 });
this.panel_detalle =  new Ext.Panel({
    region: 'east',
    title: 'Detalles del Registro',
    id: 'detalle_registro',
    collapsible: true,
    collapseMode: 'mini',
    collapsed:true,
    split: true,
    autoScroll: true,
    titleCollapse: true,
    deferredRender: false,
    width:350,
    margins: '0 0 0 0',
    script:true,
    iconCls: 'icon-reporteest',
    items:[
  		new Ext.Panel({
  			id: 'detalle'
  		})
    ]
});

this.comboTemas = new Ext.ux.ThemeCombo({
	width:100
});

this.tabpanel = new Ext.TabPanel({
	region: 'center',
	deferredRender: false,
	id: 'tabpanel',
	border:true,
	autoScroll: false,
	enableTabScroll:true,
	activeItem:0,
	listeners: {'tabchange': function(tabPanel, tab){panel_detalle.collapse();}},
	items:[{
		id: 'tabPrincipal',
		border:false,
		title: '{{ $bandeja->de_bandeja }}',
    autoScroll:true,
		iconCls:'icon-inicio',
		contentEl:'centro',
	  layout:'fit',
		padding: 0,
		autoLoad: {url: '{{ $bandeja->de_url_bandeja }}', scripts: true, scope: this}
	}]
});
Ext.onReady(function(){
this.datosUsuario = '<p class="registro_detalle"><b><span style="color:red;font-size:13px;">Bienvenido, {!! $funcionario->nb_funcionario !!} {!! $funcionario->ap_funcionario !!} </span></b></p>';
this.datosUsuario += '<p class="registro_detalle"><b>Fecha de Registro: </b>{!! trim(date_format(date_create($funcionario->fe_registro),'d/m/Y')) !!}</p>';
this.datosUsuario += '<p class="registro_detalle"><b>Cédula: </b>{!! $funcionario->inicial !!}-{!! $funcionario->nu_cedula !!}</p>';
this.datosUsuario +='<p class="registro_detalle"><b>Unidad Ejecutora: </b> {!! $funcionario->tx_ejecutor !!}</p>';
this.datosUsuario +='<p class="registro_detalle"><b>Ejercicio Fiscal: </b> {!! Session::get('ejercicio') !!}</p>';
this.datosUsuario += '<p class="registro_detalle"><b>Ultimo login: </b>{!! trim(date_format(date_create($ultimo_login->created_at),"d/m/Y - h:i A")) !!}</p>';

this.btnSalir = new Ext.Button({
	text: 'Cerrar sesi&oacute;n',
	handler: logOut,
	iconCls:'icon-salir2'
});

this.btnCambiarEjercicio = new Ext.Button({
	text: 'Cambiar Ejercicio',
	handler: cambiaEf,
	iconCls:'icon-movimiento'
});

this.reloj = new Ext.Toolbar.TextItem('');
/*correr reloj*/
Ext.TaskMgr.start({run: function(){Ext.fly(reloj.getEl()).update(new Date().format('g:i:s A'));},interval: 1000});
/*descargador*/
this.bajar = new CMS.view.FileDownload();
/*barra de estatus*/
/*this.estatusbar = new Ext.Toolbar({items:[this.reloj,'-',this.btnSalir]});*/
var viewport = new Ext.Viewport({
layout: 'fit',
items: [{
	layout: 'border',
	items: [
        /*create instance immediately*/
        new Ext.BoxComponent({
          region: 'north',
          height: 60, /*give north and south regions a height*/
          contentEl:'header'
		    }),{
          region: 'south',
          split:true,
          layout:'fit',
          maxSize: 10,
          border:false,
          bbar : [
            this.reloj,'-',
            this.btnCambiarEjercicio,'-',
            this.btnSalir,'-','->',
            {xtype: 'tbtext', text: '<span style="color:black;"><b>SISTEMA POA-PRESUPUESTO DE LA GOBERNACION DEL ESTADO ZULIA.</b></span>'}
          ]
        },{
          region: 'west',
          id: 'navegador', /*see Ext.getCmp() below*/
          title: '.::PLANIFICACION Y FORMULACION PRESUPUESTARIA - {{ Session::get('ejercicio') }}::.',
          iconCls: 'icon-navegacion',
          split: true,
          width: 240,
          minSize: 200,
          maxSize: 600,
          autoScroll:true,
          collapsible: true,
          animCollapse: true,
          collapsedTitle: true,
          margins: '0 0 0 0',
          /*bbar: this.estatusbar,*/
          bodyStyle: "background-image:url('{{ asset('/images/zulia.png') }}');background-repeat: no-repeat;    background-attachment: fixed; background-position: 4.5% 90%; background-size: 120px 120px; !important;",
          layout: 'accordion',
          layoutConfig: {
            animate: true
          },
		      items: [
  				{
    				title:'<b>Mi Cuenta</b>',
    				autoScroll:true,
    				border:false,
    				collapsed:false,
    				iconCls:'icon-usuario',
    				autoHeight:true,
    				html: miCuenta(this.datosUsuario)
  				},
				  {!! $menu !!}
				]
		    },
		    this.tabpanel,
        this.panel_detalle
    ]
}, this.bajar ]
});
});

function showResult(btn){
	if(btn=="yes"){
		Ext.MessageBox.show({title: 'Cerrando sesi&oacute;n', msg: '<br>Por favor  Espere...',width:300,closable:false,icon:Ext.MessageBox.INFO});
		location.href='autenticar';
	}
}

function logOut(){
	Ext.MessageBox.confirm('Confirmar', '¿Seguro que desea salir del Sistema?', showResult);
}

function cambiaEf(){

  this.storeCO_EJERCICIO = new Ext.data.JsonStore({
    url:'{{ URL::to('ejercicio/lista') }}',
    root:'data',
    fields:[
        {name: 'id'},{name: 'de_estatus'}
        ],
        listeners : {
            exception : function(proxy, response, operation) {
                Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
            }
        }
  });

  this._token = new Ext.form.Hidden({
  	name:'_token',
  	value:'{{ csrf_token() }}'
  });

  this.id_tab_ejercicio = new Ext.form.ComboBox({
  	fieldLabel:'Periodo',
  	store: this.storeCO_EJERCICIO,
  	typeAhead: true,
  	valueField: 'id',
  	displayField:'id',
  	hiddenName:'ejercicio',
  	forceSelection:true,
  	resizable:false,
  	triggerAction: 'all',
  	emptyText:'Ejercicio Fiscal...',
    itemSelector: 'div.search-item',
  	tpl: new Ext.XTemplate('<tpl for=".">'+
      '<div class="search-item">'+
        '<div style="margin: 4px;" class="x-boundlist-item">'+
        '<div><b>EJERCICIO FISCAL: {id}</b></div>'+
        '<div style="font-size: xx-small; color: grey;">({de_estatus})</div>'+
        '</div>'+
      '</div>'+
    '</tpl>'),
  	selectOnFocus: true,
  	mode: 'local',
  	width:200,
  	resizable:true,
  	allowBlank:false
  });

  this.storeCO_EJERCICIO.load();
  	paqueteComunJS.funcion.seleccionarComboByCo({
  	objCMB: this.id_tab_ejercicio,
  	value:  {{ Session::get('ejercicio') }},
  	objStore: this.storeCO_EJERCICIO
  });

  this.fielset1 = new Ext.form.FieldSet({
                title:'Año en Ejercicio',
                autoWidth:true,
  		          labelWidth: 130,
                items:[
                  this.id_tab_ejercicio
                ]
  });

  var formPanel_cambioEf = new Ext.form.FormPanel({
  	width:471,
  	labelWidth: 130,
  	border:false,
  	autoHeight:true,
  	autoScroll:true,
  	bodyStyle:'padding:10px;',
  	items:[
  		this._token,
      this.fielset1,
      {html : "<p><br><b>Seleccione la opcion a realizar y presione Aceptar:</b></p>",border : false}
  	]
  });

  this.guardar = new Ext.Button({
      text:'Aceptar',
      iconCls: 'icon-fin',
  		align:'center',
      handler:function(){

  			if(!formPanel_cambioEf.getForm().isValid()){
  					Ext.MessageBox.show({
  							title: 'Alerta',
  							msg: "Debe ingresar los campos en rojo",
  							closable: false,
  							icon: Ext.MessageBox.INFO,
  							resizable: false,
  							animEl: document.body,
  							buttons: Ext.MessageBox.OK
  					});
  					return false;
  			}

  			formPanel_cambioEf.getForm().submit({
  					method:'POST',
  					url:'{{ URL::to('ejercicio') }}',
  					waitMsg: 'Seleccionando Periodo, por favor espere..',
  					waitTitle:'Enviando',
  					failure: function(form, action) {
  						var errores = '';
  						for(datos in action.result.msg){
  							errores += action.result.msg[datos] + '<br>';
  						}
  						Ext.MessageBox.alert('Error en transacción', errores);
  					},
  					success: function(form, action) {
  							 if(action.result.success){
  								 Ext.MessageBox.show({title: 'Cargando Ejercicio', msg: '<br>Por favor  Espere...',width:300,closable:false,icon:Ext.MessageBox.INFO});
  								 location.href=action.result.url;
  							 }
  					 }
  			});

  		}
  });

  this.ejercicio = new Ext.Window({
        title:'Seleccione Ejercicio Fiscal',
        layout:'fit',
        iconCls: 'icon-cambio',
        width:485,
  			autoHeight:true,
        modal:true,
  			frame:true,
        autoScroll: true,
        maximizable:false,
        closable:true,
        draggable: false,
        resizable: false,
  			constrain:true,
        plain: true,
        buttonAlign:'center',
        items:[
          formPanel_cambioEf
        ],
        buttons: [
  				this.guardar
  			]
  });

  this.ejercicio.show();
}
</script>
<div id="formulario_ubicacion"></div>

@endsection
