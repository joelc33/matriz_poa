<script type="text/javascript">
Ext.ns("forma005EditarCambio");
forma005EditarCambio.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this.id_tab_ac = new Ext.form.Hidden({
	name:'ac',
	value:this.OBJ.id_tab_ac
});
//</token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});

this.de_programado_anual = new Ext.form.TextArea({
	fieldLabel: 'PRODUCTO PROGRAMADO ANUAL DEL OBJETIVO INSTITUCIONAL',
	name: 'programado_anual',
	value:this.OBJ.pp_anual,
	allowBlank: false,
	width:400,
	height: 100,
	maxLength: 3000,
	/*readOnly:this.OBJ.in_bloquear_005,
	style:(this.OBJ.in_bloquear_005==true)?'background:#f2d7d5;':''*/
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.tp_indicador = new Ext.form.TextField({
	fieldLabel:'INDICADORES DE GESTIÓN (EFICIENCIA, EFICACIA, EFECTIVIDAD)',
	name:'tipo_indicador',
	value:this.OBJ.tp_indicador,
	width:400,
	maxLength: 600,
	allowBlank:false,
	/*readOnly:this.OBJ.in_bloquear_005,
	style:(this.OBJ.in_bloquear_005==true)?'background:#f2d7d5;':''*/
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.nb_indicador = new Ext.form.TextField({
	fieldLabel:'NOMBRE DEL INDICADOR',
	name:'nombre_indicador',
	value:this.OBJ.nb_indicador_gestion,
	width:400,
	maxLength: 600,
	allowBlank:false,
	/*readOnly:this.OBJ.in_bloquear_005,
	style:(this.OBJ.in_bloquear_005==true)?'background:#f2d7d5;':''*/
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.valor_objetivo = new Ext.form.TextField({
	fieldLabel:'VALOR OBJETIVO',
	name:'valor_objetivo',
	value:this.OBJ.de_valor_objetivo,
	width:400,
	maxLength: 600,
	allowBlank:false,
	/*readOnly:this.OBJ.in_bloquear_005,
	style:(this.OBJ.in_bloquear_005==true)?'background:#f2d7d5;':''*/
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.valor_obtenido = new Ext.form.TextField({
	fieldLabel:'VALOR OBTENIDO',
	name:'valor_obtenido',
	value:this.OBJ.de_valor_obtenido,
	width:400,
	maxLength: 600,
	allowBlank:false,
	/*readOnly:this.OBJ.in_bloquear_005,
	style:(this.OBJ.in_bloquear_005==true)?'background:#f2d7d5;':''*/
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.nu_cumplimiento = new Ext.form.NumberField({
	fieldLabel:'CUMPLIMIENTO %',
	name:'cumplimiento',
	value:this.OBJ.nu_cumplimiento,
	allowBlank:false,
	width:100,
	minLength : 0,
	maxLength: 18,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 18},
	/*readOnly:this.OBJ.in_bloquear_005,
	style:(this.OBJ.in_bloquear_005==true)?'background:#f2d7d5;':''*/
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.de_indicador = new Ext.form.TextField({
	fieldLabel:'DESCRIPCIÓN DEL INDICADOR',
	name:'indicador',
	value:this.OBJ.de_indicador_descripcion,
	width:400,
	maxLength: 600,
	allowBlank:false,
	/*readOnly:this.OBJ.in_bloquear_005,
	style:(this.OBJ.in_bloquear_005==true)?'background:#f2d7d5;':''*/
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.de_formula = new Ext.form.TextField({
	fieldLabel:'FÓRMULA',
	name:'formula',
	value:this.OBJ.de_formula,
	width:400,
	maxLength: 600,
	allowBlank:false,
	/*readOnly:this.OBJ.in_bloquear_005,
	style:(this.OBJ.in_bloquear_005==true)?'background:#f2d7d5;':''*/
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.de_observacion = new Ext.form.TextField({
	fieldLabel:'OBSERVACION',
	name:'observacion',
	value:this.OBJ.de_observacion,
	allowBlank:false,
	width:400,
	/*readOnly:this.OBJ.in_bloquear_005,
	style:(this.OBJ.in_bloquear_005==true)?'background:#f2d7d5;':''*/
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.guardar = new Ext.Button({
    text:'Aprobar',
    iconCls: 'icon-fin',
    handler:function(){

        if(!forma005EditarCambio.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea aprobar los cambios solicitados?<br><b>Nota:</b> No se podran modificar los cambios.', function(boton){
				if(boton=="yes"){

        forma005EditarCambio.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('seguimiento/ac/005/cambio/aprobar') }}',
						@else
							url:'{{ URL::to('seguimiento/ac/005/cambio/aprobar') }}/{!! $data->id !!}',
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
                 forma005ListaCambio.main.store_lista.load();
                 forma005EditarCambio.main.winformPanel_.close();
             }
        });

			}
			});

    }
});

this.negar = new Ext.Button({
    text:'Negar',
    iconCls: 'icon-cancelar',
    handler:function(){

        if(!forma005EditarCambio.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea negar los cambios solicitados?<br><b>Nota:</b> El Ejecutor tendra que solicitar de nuevo los cambios.', function(boton){
				if(boton=="yes"){

        forma005EditarCambio.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('seguimiento/ac/005/cambio/negar') }}',
						@else
							url:'{{ URL::to('seguimiento/ac/005/cambio/negar') }}/{!! $data->id !!}',
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
                 forma005ListaCambio.main.store_lista.load();
                 forma005EditarCambio.main.winformPanel_.close();
             }
        });

			}
			});

    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        forma005EditarCambio.main.winformPanel_.close();
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
    this.id_tab_ac,
		this.de_programado_anual,
		this.tp_indicador,
		this.nb_indicador,
		this.valor_objetivo,
		this.valor_obtenido,
//		this.nu_cumplimiento,
		this.de_indicador,
		this.de_formula,
//		this.de_observacion
	]
});

this.winformPanel_ = new Ext.Window({
    title:'F005: INDICADORES DE GESTIÓN',
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
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.005.cambio.aprobar', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_005==false)
					this.guardar,'-',
				@endif
			@endif
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.005.cambio.negar', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_005==false)
					this.negar,'-',
				@endif
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
forma005ListaCambio.main.mascara.hide();
}
};
Ext.onReady(forma005EditarCambio.main.init, forma005EditarCambio.main);
</script>
