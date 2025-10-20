<script type="text/javascript">
Ext.ns("prforma001Editar");
prforma001Editar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.de_objetivo = new Ext.form.TextArea({
	fieldLabel: '1.7. OBJETIVO GENERAL DEL PROYECTO',
	name: 'objetivo',
	value:this.OBJ.de_objetivo,
	allowBlank: false,
	width:400,
	height: 100,
	maxLength: 6000,
	readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''
});

this.de_proyecto = new Ext.form.TextArea({
	fieldLabel: '1.10. DESCRIPCIÓN DEL PROYECTO',
	name: 'descripcion',
	value:this.OBJ.de_proyecto,
	allowBlank: false,
	width:400,
	height: 100,
	maxLength: 6000,
	readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''
});

this.de_observacion = new Ext.form.TextField({
	fieldLabel:'Observacion',
	name:'observacion',
	value:this.OBJ.de_observacion_001,
	allowBlank:false,
	width:400,
	readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!prforma001Editar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea guardar si hacer cambios?<br><b>Nota:</b> No se podra modificar el contenido.', function(boton){
				if(boton=="yes"){

        prforma001Editar.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('proyecto/seguimiento/001/guardar') }}',
						@else
							url:'{{ URL::to('proyecto/seguimiento/001/guardar') }}/{!! $data->id !!}',
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
                 prforma001Lista.main.store_lista.load();
                 prforma001Editar.main.winformPanel_.close();
             }
        });

			}
			});

    }
});

this.enviar = new Ext.Button({
    text:'Enviar Cambios',
    iconCls: 'icon-report',
    handler:function(){

        if(!prforma001Editar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea solicitar los cambios?<br><b>Nota:</b> Debe esperar por aprobacion de parte de Planificacion.', function(boton){
				if(boton=="yes"){

        prforma001Editar.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('proyecto/seguimiento/001/enviar') }}',
						@else
							url:'{{ URL::to('proyecto/seguimiento/001/enviar') }}/{!! $data->id !!}',
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
                 prforma001Lista.main.store_lista.load();
                 prforma001Editar.main.winformPanel_.close();
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
        prforma001Editar.main.winformPanel_.close();
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
			@if( in_array( array( 'de_privilegio' => 'proyectoseguimiento.001.enviar', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_bloquear_001==false)
					this.enviar,'-',
				@endif
			@endif
			@if( in_array( array( 'de_privilegio' => 'proyectoseguimiento.001.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_bloquear_001==false)
					this.guardar,'-',
				@endif
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
prforma001Lista.main.mascara.hide();
}
};
Ext.onReady(prforma001Editar.main.init, prforma001Editar.main);
</script>
