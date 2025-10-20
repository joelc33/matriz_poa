<script type="text/javascript">
Ext.ns("proyectoseguimientoEditar");
proyectoseguimientoEditar.main = {
init:function(){

this.storeCO_EJERCICIO = this.getStoreCO_EJERCICIO();

this.storeCO_AC = this.getStoreCO_AC();

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.id_tab_ejercicio = new Ext.form.ComboBox({
	fieldLabel:'Periodo',
	store: this.storeCO_EJERCICIO,
	typeAhead: true,
	valueField: 'id',
	displayField:'rango',
	hiddenName:'ejercicio',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Periodo en Gestion...',
  itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for=".">'+
    '<div class="search-item">'+
      '<div style="margin: 4px;" class="x-boundlist-item">'+
      '<div><b>{de_lapso}: {id_tab_ejercicio_fiscal}</b></div>'+
      '<div style="font-size: xx-small; color: grey;">({rango})</div>'+
      '</div>'+
    '</div>'+
  '</tpl>'),
	selectOnFocus: true,
	mode: 'local',
	width:300,
	resizable:true,
	allowBlank:false,
	listeners:{
						change: function(){
								proyectoseguimientoEditar.main.storeCO_AC.load({
										params: { periodo:this.getValue(), _token: '{{ csrf_token() }}'}
								})
						}
				}
});

this.storeCO_EJERCICIO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_ejercicio,
	value:  this.OBJ.id_tab_ejercicio,
	objStore: this.storeCO_EJERCICIO
});

this.id_tab_ejecutores = new Ext.form.ComboBox({
	fieldLabel:'Proyecto',
	store: this.storeCO_AC,
	typeAhead: true,
	valueField: 'co_proyectos',
	displayField:'codigo',
	hiddenName:'proyecto',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Proyecto...',
  itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for=".">'+
    '<div class="search-item">'+
      '<div style="margin: 4px;" class="x-boundlist-item">'+
      '<div><b>PROYECTO: {codigo}</b></div>'+
      '<div style="font-size: xx-small; color: grey;">({de_nombre})</div>'+
			'<div style="font-size: xx-small; color: blue;">{tx_ejecutor}</div>'+
      '</div>'+
    '</div>'+
  '</tpl>'),
	selectOnFocus: true,
	mode: 'local',
	width:300,
	resizable:true,
	allowBlank:false
});

/*this.storeCO_AC.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_ejecutores,
	value:  this.OBJ.id_tab_ejecutores,
	objStore: this.storeCO_AC
});*/

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!proyectoseguimientoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        proyectoseguimientoEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('proyecto/seguimiento/guardar') }}',
	@else
		url:'{{ URL::to('proyecto/seguimiento/guardar') }}/{!! $data->id !!}',
	@endif
		waitMsg: 'Enviando datos, por favor espere..',
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
                     Ext.MessageBox.show({
                         title: 'Mensaje',
                         msg: action.result.msg,
                         closable: false,
                         icon: Ext.MessageBox.INFO,
                         resizable: false,
			 animEl: document.body,
                         buttons: Ext.MessageBox.OK
                     });
                 }
                 proyectoseguimientoLista.main.store_lista.load();
                 proyectoseguimientoEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        proyectoseguimientoEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:500,
	labelWidth: 120,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
    this.id_tab_ejercicio,
		this.id_tab_ejecutores
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Agregar Proyecto',
    modal:true,
    constrain:true,
width:514,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'proyectoseguimiento.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
proyectoseguimientoLista.main.mascara.hide();
},
getStoreCO_EJERCICIO:function(){
      this.store = new Ext.data.JsonStore({
          url:'{{ URL::to('auxiliar/lapso') }}',
          root:'data',
          fields:[
              {name: 'id'},
              {name: 'id_tab_ejercicio_fiscal'},
              {name: 'id_tab_periodo'},
              {name: 'fe_inicio'},
              {name: 'fe_fin'},
							{name: 'de_lapso'},
              {
                  name: 'rango',
                  convert: function(v, r) {
                      return r.fe_inicio + ' - ' + r.fe_fin;
                  }
              }
              ],
              listeners : {
                  exception : function(proxy, response, operation) {
                      Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                  }
              }
      });
      return this.store;
},
getStoreCO_AC:function(){
      this.store = new Ext.data.JsonStore({
          url:'{{ URL::to('proyecto/seguimiento/disponible') }}',
          root:'data',
          fields:[
              {name: 'co_proyectos'},
              {name: 'codigo'},
              {name: 'de_nombre'},
							{name: 'tx_ejecutor'}
              ],
              listeners : {
                  exception : function(proxy, response, operation) {
                      Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                  }
              }
      });
      return this.store;
}
};
Ext.onReady(proyectoseguimientoEditar.main.init, proyectoseguimientoEditar.main);
</script>
