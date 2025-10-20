@extends('app')

@section('htmlheader_title')  Selección de Ejercicio Fiscal @endsection

@section('main-content')

<style type="text/css">
body {
background-color:white;
}
</style>

<script type="text/javascript">
Ext.QuickTips.init();
Ext.form.Field.prototype.msgTarget = 'side';

Ext.ns("seleccionEjercicio");
seleccionEjercicio.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.storeCO_EJERCICIO = this.getStoreCO_EJERCICIO();

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
	resizable:true,
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
	value:  this.OBJ.id_tab_ejercicio,
	objStore: this.storeCO_EJERCICIO
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Año en Ejercicio',
              autoWidth:true,
		          labelWidth: 130,
              items:[
		              this.id_tab_ejercicio,
              ]
});
@if($data->in_verificado==false)

this.OBJfuncionario = paqueteComunJS.funcion.doJSON({stringData:'{!! $funcionario !!}'});

this.id_funcionario = new Ext.form.Hidden({
	name:'id_funcionario',
	value:this.OBJfuncionario.id_funcionario
});

this.storeCO_DOCUMENTO = this.getStoreCO_DOCUMENTO();

this.co_documento = new Ext.form.ComboBox({
	fieldLabel:'documento',
	store: this.storeCO_DOCUMENTO,
	typeAhead: true,
	valueField: 'id',
	displayField:'inicial',
	hiddenName:'documenton',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'...',
	selectOnFocus: true,
	mode: 'local',
	width:40,
	resizable:true,
	allowBlank:false
});

this.storeCO_DOCUMENTO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_documento,
	value:  this.OBJfuncionario.id_tab_documento,
	objStore: this.storeCO_DOCUMENTO
});

this.nu_cedula = new Ext.form.NumberField({
	fieldLabel:'Nu cedula',
	name:'cedula',
	value:this.OBJfuncionario.nu_cedula,
	width:155,
	allowBlank:false,
	minLength : 5,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
});

this.compositefieldCI = new Ext.form.CompositeField({
fieldLabel: 'Cedula',
items: [
	this.co_documento,
	this.nu_cedula,
	]
});

this.nb_funcionario = new Ext.form.TextField({
	fieldLabel:'Nombre',
	name:'nombre',
	value:this.OBJfuncionario.nb_funcionario,
	allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.ap_funcionario = new Ext.form.TextField({
	fieldLabel:'Apellido',
	name:'apellido',
	value:this.OBJfuncionario.ap_funcionario,
	allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_telefono = new Ext.form.TextField({
	fieldLabel:'Telefono Contacto',
	name:'telefono_funcionario',
	value:this.OBJfuncionario.tx_telefono,
	allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_email = new Ext.form.TextField({
	fieldLabel:'Correo Electrónico',
	name:'correo_funcionario',
	value:this.OBJfuncionario.tx_email,
	allowBlank:false,
	width:250,
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail',
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.de_correo = new Ext.form.TextField({
	fieldLabel:'Correo Institucional',
	name:'correo',
	value:this.OBJ.de_correo,
	width:250,
	allowBlank:false,
	/*emptyText: 'correo@diminio.com',*/
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail'
});

this.de_telefono = new Ext.form.TextField({
	fieldLabel:'Telefono Institucion',
	name:'telefono',
	value:this.OBJ.de_telefono,
	allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fielset2 = new Ext.form.FieldSet({
	title:'Datos de contacto del Ejecutor',
	autoWidth:true,
	labelWidth: 130,
	items:[
		this.id_funcionario,
		this.compositefieldCI,
		this.nb_funcionario,
		this.ap_funcionario,
		this.tx_telefono,
		this.tx_email,
		this.de_correo,
		this.de_telefono
	]
});

@endif

this.formPanel_ = new Ext.form.FormPanel({
	width:471,
	labelWidth: 130,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
    this.fielset1,
		@if($data->in_verificado==false)
		this.fielset2,
		@endif
    {html : "<p><br><b>Seleccione la opcion a realizar y presione Aceptar:</b></p>",border : false}
	]
});

this.guardar = new Ext.Button({
    text:'Aceptar',
    iconCls: 'icon-fin',
		align:'center',
    handler:function(){

			if(!seleccionEjercicio.main.formPanel_.getForm().isValid()){
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

			seleccionEjercicio.main.formPanel_.getForm().submit({
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
      closable:false,
      draggable: false,
      resizable: false,
			constrain:true,
      plain: true,
      buttonAlign:'center',
      items:[
        this.formPanel_
      ],
      buttons: [
				this.guardar
			]
});

this.ejercicio.show();
},
getStoreCO_EJERCICIO:function(){
      this.store = new Ext.data.JsonStore({
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
      return this.store;
},
getStoreCO_DOCUMENTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/documento') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'inicial'}
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
Ext.onReady(seleccionEjercicio.main.init, seleccionEjercicio.main);
</script>

@endsection
