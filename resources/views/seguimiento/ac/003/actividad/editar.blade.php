<script type="text/javascript">
Ext.ns("forma003ActividadEditar");
forma003ActividadEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.datos1 = '<p class="registro_detalle"><b>Código: </b>'+this.OBJ.codigo+'</p>';
this.datos1 +='<p class="registro_detalle"><b>Actividad: </b>'+this.OBJ.nb_meta+'</p>';
this.datos1 +='<p class="registro_detalle"><b>Fecha Programada: </b>'+this.OBJ.fecha_inicio+' - '+this.OBJ.fecha_fin+'</p>';
this.datos1 +='<p class="registro_detalle"><b>Presupuesto Programado Anual: </b>'+formatoNumero(this.OBJ.mo_presupuesto)+'</p>';
this.datos1 +='<p class="registro_detalle"><b>Categoria: </b>'+this.OBJ.co_sector + '.' + this.OBJ.nu_original + '.00.0' + this.OBJ.nu_numero + '.' + this.OBJ.co_partida+'</p>';

this.fieldset1 = new Ext.form.FieldSet({
	title: 'Datos de la Actividad',
	html: this.datos1
});

/*this.nu_meta_moificada = new Ext.form.TextField({
	fieldLabel:'META MODIFICADA',
	name:'meta_modificada',
	value:this.OBJ.nu_meta_modificada,
	width:400,
	maxLength: 250,
	allowBlank:false
});*/

this.mo_modificado_anual = new Ext.form.NumberField({
	fieldLabel:'PRESUPUESTO MODIFICADO ANUAL',
	name:'modificado_anual',
	value:this.OBJ.mo_modificado_anual?this.OBJ.mo_modificado_anual:0,
	width:200,
	maxLength: 20,
	decimalPrecision: 2,
 	maxValue : 999999999999999999999,
	msgTarget : 'Rango Entre 0 y 9',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 20},
	allowDecimals: true,
        readOnly:(this.OBJ.in_enviado==true)?true:(this.OBJ.id_tab_origen==2)?(this.OBJ.id_tab_tipo_periodo==19)?true:false:false,
        style:(this.OBJ.in_enviado==true)?'background:#f2d7d5;':'',
        validationEvent: 'blur',
	validator: function(value){
		tedm=value;
        	if(isNaN(tedm)){tedm = parseFloat(0);}
		tedf=forma003ActividadEditar.main.OBJ.mo_presupuesto_nuevo;
        	if(isNaN(tedf)){tedf = parseFloat(0);}
		forma003ActividadEditar.main.mo_actualizado_anual.setValue(parseFloat(tedf)+parseFloat(tedm));
	}        
});

this.mo_actualizado_anual = new Ext.form.NumberField({
	fieldLabel:'PRESUPUESTO ACTUALIZADO ANUAL (Bs.)',
	name:'actualizado_anual',
	value:parseFloat(this.OBJ.mo_presupuesto) + parseFloat(this.OBJ.mo_modificado),
	allowBlank:false,
        readOnly:true,
        style:'background:#f2d7d5;',        
	width:200,
	allowDecimals: true,
	allowNegative: false
});

this.mo_comprometido = new Ext.form.NumberField({
	fieldLabel:'PRESUPUESTO COMPROM. AL CORTE (Bs.)',
	name:'comprometido',
	value:this.OBJ.mo_comprometido?this.OBJ.mo_comprometido:0,
	allowBlank:false,
	width:200,
	maxLength: 20,
	decimalPrecision: 2,
 	maxValue : 999999999999999999999,
        readOnly:(this.OBJ.in_enviado==true)?true:false,
        style:(this.OBJ.in_enviado==true)?'background:#f2d7d5;':'',        
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 20},
	allowDecimals: true
});

this.mo_causado = new Ext.form.NumberField({
	fieldLabel:'PRESUPUESTO CAUSADO AL CORTE (Bs.)',
	name:'causado',
	value:this.OBJ.mo_causado?this.OBJ.mo_causado:0,
	allowBlank:false,
	width:200,
	maxLength: 20,
	decimalPrecision: 2,
 	maxValue : 999999999999999999999,
        readOnly:(this.OBJ.in_enviado==true)?true:false,
        style:(this.OBJ.in_enviado==true)?'background:#f2d7d5;':'',         
	msgTarget : 'Rango Entre 0 y 9',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 20},
	allowDecimals: true
});

this.mo_pagado = new Ext.form.NumberField({
	fieldLabel:'PRESUPUESTO PAGADO AL CORTE (Bs.)',
	name:'pagado',
	value:this.OBJ.mo_pagado?this.OBJ.mo_pagado:0,
	allowBlank:false,
	width:200,
	maxLength: 20,
	decimalPrecision: 2,
 	maxValue : 999999999999999999999,
        readOnly:(this.OBJ.in_enviado==true)?true:false,
        style:(this.OBJ.in_enviado==true)?'background:#f2d7d5;':'',         
	msgTarget : 'Rango Entre 0 y 9',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 20},
	allowDecimals: true
});

this.fieldset2 = new Ext.form.FieldSet({
	title: 'Datos del Seguimiento',
	items:[
		this.mo_modificado_anual,
                this.mo_actualizado_anual,
		this.mo_comprometido,
		this.mo_causado,
		this.mo_pagado
	]
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){
        
         if(forma003ActividadEditar.main.mo_actualizado_anual.getValue()<0){
            Ext.Msg.alert("Alerta","El monto actualizado no puede ser menor a cero");
            return false;
        }         

        if(!forma003ActividadEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        
        if(forma003ActividadEditar.main.OBJ.id_tab_tipo_periodo==19){        
        
         if(forma003ActividadEditar.main.mo_modificado_anual.getValue()>forma003ActividadEditar.main.mo_actualizado_anual.getValue()){
            Ext.Msg.alert("Alerta","El monto modificado no puede ser mayor al inicial o aprobado");
            return false;
        }    
        
        
        
         if(forma003ActividadEditar.main.mo_comprometido.getValue()>forma003ActividadEditar.main.mo_actualizado_anual.getValue()){
            Ext.Msg.alert("Alerta","El monto comprometido no puede ser mayor al inicial o aprobado");
            return false;
        } 
        
         if(forma003ActividadEditar.main.mo_causado.getValue()>forma003ActividadEditar.main.mo_comprometido.getValue()){
            Ext.Msg.alert("Alerta","El monto causado no puede ser mayor al comprometido");
            return false;
        }   
        
         if(forma003ActividadEditar.main.mo_pagado.getValue()>forma003ActividadEditar.main.mo_causado.getValue()){
            Ext.Msg.alert("Alerta","El monto pagado no puede ser mayor al causado");
            return false;
        } 
        
        }
        
        forma003ActividadEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('ac/seguimiento/003/actividad/guardar') }}',
	@else
		url:'{{ URL::to('ac/seguimiento/003/actividad/guardar') }}/{!! $data->id !!}',
	@endif
		waitMsg: 'Enviando datos, por favor espere..',
		waitTitle:'Enviando',
            failure: function(form, action) {
		var errores = '';
		for(datos in action.result.msg){
			errores += action.result.msg[datos] + '<br>';
		}
                Ext.MessageBox.alert('Error en transacción', action.result.msg);
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
                 forma003ActividadLista.main.store_lista.reload();
                 forma003ActividadEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        forma003ActividadEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:800,
	labelWidth: 280,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
		this.fieldset1,
		this.fieldset2
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: METAS FINANCIERAS',
    modal:true,
    constrain:true,
width:814,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.nuevo', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_enviado==false)
					this.guardar,'-',
				@endif
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
forma003ActividadLista.main.mascara.hide();
}
};
Ext.onReady(forma003ActividadEditar.main.init, forma003ActividadEditar.main);
</script>
