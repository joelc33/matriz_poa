<script type="text/javascript">
Ext.ns("forma001Editar");
forma001Editar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.inst_mision = new Ext.form.TextArea({
	fieldLabel: '1.4.1. MISION',
	name: 'mision',
	value:this.OBJ.inst_mision,
	allowBlank: false,
	width:400,
	height: 100,
	maxLength: 6000,
	readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''
});

this.inst_vision = new Ext.form.TextArea({
	fieldLabel: '1.4.2. VISION',
	name: 'vision',
	value:this.OBJ.inst_vision,
	allowBlank: false,
	width:400,
	height: 100,
	maxLength: 6000,
	readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''
});

this.inst_objetivos = new Ext.form.TextArea({
	fieldLabel: '1.4.3. OBJETIVOS DE LA INSTITUCION',
	name: 'objetivos',
	value:this.OBJ.inst_objetivos,
	allowBlank: false,
	width:400,
	height: 200,
	maxLength: 6000,
	readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''
});

this.nu_po_beneficiar = new Ext.form.TextField({
	fieldLabel:'Poblacion a boneficiar',
	name:'nu_po_beneficiar',
	value:this.OBJ.nu_po_beneficiar,
	allowBlank:false,
	width:400,
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.nu_em_previsto = new Ext.form.TextField({
	fieldLabel:'Empleos a Generar',
	name:'nu_em_previsto',
	value:this.OBJ.nu_em_previsto,
	allowBlank:false,
	width:400,
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.nu_po_beneficiada = new Ext.form.TextField({
	fieldLabel:'Poblacion Beneficiada',
	name:'nu_po_beneficiada',
	value:this.OBJ.nu_po_beneficiada,
	allowBlank:false,
	width:400,
	readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''
});

this.nu_em_generado = new Ext.form.TextField({
	fieldLabel:'Empleos Generados',
	name:'nu_em_generado',
	value:this.OBJ.nu_em_generado,
	allowBlank:false,
	width:400,
	readOnly:this.OBJ.in_bloquear_001,
	style:(this.OBJ.in_bloquear_001==true)?'background:#f2d7d5;':''
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!forma001Editar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea guardar sin hacer cambios?<br><b>Nota:</b> No se podra modificar el contenido.', function(boton){
				if(boton=="yes"){

        forma001Editar.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('ac/seguimiento/001/guardar') }}',
						@else
							url:'{{ URL::to('ac/seguimiento/001/guardar') }}/{!! $data->id !!}',
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
                 forma001Lista.main.store_lista.load();
                 forma001Editar.main.winformPanel_.close();
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

        if(!forma001Editar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea solicitar los cambios?<br><b>Nota:</b> Debe esperar por aprobacion de parte de Planificacion.', function(boton){
				if(boton=="yes"){

        forma001Editar.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('ac/seguimiento/001/enviar') }}',
						@else
							url:'{{ URL::to('ac/seguimiento/001/enviar') }}/{!! $data->id !!}',
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
                 forma001Lista.main.store_lista.load();
                 forma001Editar.main.winformPanel_.close();
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
        forma001Editar.main.winformPanel_.close();
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
		this.inst_mision,
		this.inst_vision,
		this.inst_objetivos,
//		this.nu_po_beneficiar,
//                this.nu_em_previsto,
//                this.nu_po_beneficiada,
//                this.nu_em_generado
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
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_bloquear_001==false)
					this.enviar,'-',this.guardar,'-',
				@endif
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
forma001Lista.main.mascara.hide();
}
};
Ext.onReady(forma001Editar.main.init, forma001Editar.main);
</script>
