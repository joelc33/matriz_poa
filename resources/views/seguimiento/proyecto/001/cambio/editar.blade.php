<script type="text/javascript">
Ext.ns("forma001EditarCambio");
forma001EditarCambio.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this.id_tab_proyecto = new Ext.form.Hidden({
	name:'proyecto',
	value:this.OBJ.id_tab_proyecto
});
//</token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});

this.de_objetivo = new Ext.form.TextArea({
	fieldLabel: '1.7. OBJETIVO GENERAL DEL PROYECTO',
	name: 'objetivo',
	value:this.OBJ.de_objetivo,
	allowBlank: false,
	width:400,
	height: 100,
	maxLength: 6000,
	/*readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''*/
  readOnly:true,
  style:'background:#f2d7d5;'
});

this.de_proyecto = new Ext.form.TextArea({
	fieldLabel: '1.10. DESCRIPCIÓN DEL PROYECTO',
	name: 'descripcion',
	value:this.OBJ.de_proyecto,
	allowBlank: false,
	width:400,
	height: 100,
	maxLength: 6000,
	/*readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''*/
  readOnly:true,
  style:'background:#f2d7d5;'
});

this.de_observacion = new Ext.form.TextField({
	fieldLabel:'Observacion',
	name:'observacion',
	value:this.OBJ.de_observacion,
	allowBlank:false,
	width:400,
	/*readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''*/
  readOnly:true,
  style:'background:#f2d7d5;'
});

this.guardar = new Ext.Button({
    text:'Aprobar',
    iconCls: 'icon-fin',
    handler:function(){

        if(!forma001EditarCambio.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea aprobar los cambios solicitados?<br><b>Nota:</b> No se podran modificar los cambios.', function(boton){
				if(boton=="yes"){

        forma001EditarCambio.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('seguimiento/proyecto/001/cambio/aprobar') }}',
						@else
							url:'{{ URL::to('seguimiento/proyecto/001/cambio/aprobar') }}/{!! $data->id !!}',
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
                 forma001ListaCambio.main.store_lista.load();
                 forma001EditarCambio.main.winformPanel_.close();
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

        if(!forma001EditarCambio.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea negar los cambios solicitados?<br><b>Nota:</b> El Ejecutor tendra que solicitar de nuevo los cambios.', function(boton){
				if(boton=="yes"){

        forma001EditarCambio.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('seguimiento/proyecto/001/cambio/negar') }}',
						@else
							url:'{{ URL::to('seguimiento/proyecto/001/cambio/negar') }}/{!! $data->id !!}',
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
                 forma001ListaCambio.main.store_lista.load();
                 forma001EditarCambio.main.winformPanel_.close();
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
        forma001EditarCambio.main.winformPanel_.close();
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
    this.id_tab_proyecto,
		this.de_objetivo,
		this.de_proyecto,
		this.de_observacion
	]
});

this.winformPanel_ = new Ext.Window({
    title:'F001: MARCO NORMATIVO INSTITUCIONAL',
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
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.001.cambio.aprobar', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_001==false)
					this.guardar,'-',
				@endif
			@endif
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.001.cambio.negar', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_001==false)
					this.negar,'-',
				@endif
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
forma001ListaCambio.main.mascara.hide();
}
};
Ext.onReady(forma001EditarCambio.main.init, forma001EditarCambio.main);
</script>
