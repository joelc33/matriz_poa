<script type="text/javascript">
Ext.ns("forma002Editar");
forma002Editar.main = {
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

this.producto_obtenido = new Ext.form.TextArea({
	fieldLabel: 'PRODUCTO OBTENIDO DEL OBJETIVO INSTITUCIONAL(TRI)',
	name: 'tx_pr_obtenido',
	value:this.OBJ.tx_pr_obtenido,
	allowBlank: false,
	width:400,
	height: 75,
	maxLength: 6000
});

this.producto_obtenido_a = new Ext.form.TextArea({
	fieldLabel: 'PRODUCTO OBTENIDO DEL OBJETIVO INSTITUCIONAL(ACU)',
	name: 'tx_pr_obtenido_a',
	value:this.OBJ.tx_pr_obtenido_a,
	allowBlank: false,
	width:400,
	height: 75,
	maxLength: 6000
});

this.tx_pr_objetivo = new Ext.form.TextArea({
	fieldLabel: 'PRODUCTO PROGRAMADO DEL OBJETIVO',
	name: 'tx_pr_objetivo',
	value:this.OBJ.tx_pr_objetivo,
	allowBlank: false,
	width:400,
	height: 75,
	maxLength: 6000
});

this.producto_programado = new Ext.form.TextArea({
	fieldLabel: 'RESULTADOS OBTENIDOS',
	name: 'producto_programado',
	value:this.OBJ.producto_programado,
	allowBlank: false,
	width:400,
	height: 75,
	maxLength: 6000
});

this.observaciones = new Ext.form.TextArea({
	fieldLabel: 'OBSERVACIONES',
	name: 'de_observacion_002',
	value:this.OBJ.de_observacion_002,
	width:400,
	height: 75,
	maxLength: 6000
});

this.nu_po_beneficiar = new Ext.form.NumberField({
	fieldLabel:'POBLACION A BENEFICIAR',
	name:'nu_po_beneficiar',
	value:this.OBJ.nu_po_beneficiar,
	allowBlank:false,
	width:400,
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.nu_em_previsto = new Ext.form.NumberField({
	fieldLabel:'EMPLEOS A GENERAR',
	name:'nu_em_previsto',
	value:this.OBJ.nu_em_previsto,
	allowBlank:false,
	width:400,
	readOnly:true,
	style:'background:#f2d7d5;'
});

this.nu_po_beneficiada = new Ext.form.NumberField({
	fieldLabel:'POBLACION BENEFICIADA',
	name:'nu_po_beneficiada',
	value:this.OBJ.nu_po_beneficiada,
	allowBlank:false,
        allowNegative: false,
	width:400
});

this.nu_em_generado = new Ext.form.NumberField({
	fieldLabel:'EMPLEOS GENERADOS',
	name:'nu_em_generado',
	value:this.OBJ.nu_em_generado,
	allowBlank:false,
        allowNegative: false,
	width:400
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!forma002Editar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        

        if(forma002Editar.main.nu_po_beneficiada.getValue()>forma002Editar.main.nu_po_beneficiar.getValue()){
            Ext.Msg.alert("Alerta","La poblacion beneficiada no puede ser mayor a la del poa");
            return false;
        }

        

        if(forma002Editar.main.nu_em_generado.getValue()>forma002Editar.main.nu_em_previsto.getValue()){
            Ext.Msg.alert("Alerta","la cantidad de empleos generados no puede ser mayor a la del poa");
            return false;
        }
        

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea guardar los cambios?.', function(boton){
				if(boton=="yes"){

        forma002Editar.main.formPanel_.getForm().submit({
						method:'POST',
                                                url:'{{ URL::to('ac/seguimiento/002/guardarEditarAc') }}/{!! $data->id !!}',
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
                 forma002Editar.main.winformPanel_.close();
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
        forma002Editar.main.winformPanel_.close();
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
//		this.inst_mision,
//		this.inst_vision,
//		this.inst_objetivos,
		this.nu_po_beneficiar,
                this.nu_po_beneficiada,                
                this.nu_em_previsto,
                this.nu_em_generado,
                this.tx_pr_objetivo,
                this.producto_obtenido,
                this.producto_obtenido_a,
                this.producto_programado,
                this.observaciones
	]
});

this.winformPanel_ = new Ext.Window({
    title:'F002: EDITAR',
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
	this.guardar,'-',
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
forma002Lista.main.mascara.hide();
}
};
Ext.onReady(forma002Editar.main.init, forma002Editar.main);
</script>
