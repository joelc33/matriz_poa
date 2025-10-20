<script type="text/javascript">
Ext.ns("presupuestoingresoEditar");
presupuestoingresoEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.nu_numero = new Ext.form.NumberField({
	fieldLabel:'Partida',
	name:'partida',
	value:this.OBJ.nu_partida,
	allowBlank:false,
	minLength : 1,
	maxLength: 12,
	allowDecimals: false,
	decimalPrecision: 0,
	allowNegative: false,
	// readOnly:true,
	// style:'background:#c9c9c9;',
	msgTarget: 'under',
	width:200,
	validator: function(){
		return this.validFlag;
	},
	listeners:{
		change: function(textfield, newValue, oldValue){
			presupuestoingresoEditar.main.formPanel_.el.mask('Por Favor Espere...', 'x-mask-loading');
			var me = this;
			Ext.Ajax.request({
				method:'POST',
				url: 'auxiliar/partida/buscar',
				params: {
					partida: newValue,
					_token: '{{ csrf_token() }}'
				},
				failure: function(response){
					presupuestoingresoEditar.main.formPanel_.el.unmask();
				},
				success : function(response) {
					presupuestoingresoEditar.main.formPanel_.el.unmask();
					var errores = '';
					for(datos in Ext.decode(response.responseText).msg){
						errores += Ext.decode(response.responseText).msg[datos] + '<br>';
					}
					me.validFlag = Ext.decode(response.responseText).valido ? true : errores;
					me.validate();

					obj = Ext.util.JSON.decode(response.responseText);
					if(!obj.data){
						presupuestoingresoEditar.main.de_nombre.setValue("");
					}else{
						presupuestoingresoEditar.main.de_nombre.setValue(obj.data.tx_nombre);
					}
				}
			});
		}
	}
});

this.de_nombre = new Ext.form.TextArea({
	fieldLabel:'Denominacion',
	name:'denominacion',
	value:this.OBJ.de_partida,
	allowBlank:false,
	width:400,
	height: 100,
	//readOnly:true
	listeners:{
			change: function(){
					this.setValue(String(this.getValue()).toUpperCase());
			}
	}
});

this.mo_partida = new Ext.form.NumberField({
	fieldLabel:'Monto',
	name:'monto',
	value:this.OBJ.mo_partida,
	allowBlank:false,
	minLength : 1,
	maxLength: 22,
	allowDecimals: true,
	decimalPrecision: 2,
	allowNegative: false,
 	maxValue: this.OBJ.monto,
	msgTarget: 'under',
	width:200
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!presupuestoingresoEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        presupuestoingresoEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/presupuestoingreso/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/presupuestoingreso/guardar') }}/{!! $data->id !!}',
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
                 presupuestoingresoLista.main.store_lista.load();
                 presupuestoingresoEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        presupuestoingresoEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	labelWidth: 120,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
		this.nu_numero,
		this.de_nombre,
		this.mo_partida
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Presupuesto de Ingreso',
    modal:true,
    constrain:true,
width:614,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'libro.presupuestoingreso.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
presupuestoingresoLista.main.mascara.hide();
}
};
Ext.onReady(presupuestoingresoEditar.main.init, presupuestoingresoEditar.main);
</script>
