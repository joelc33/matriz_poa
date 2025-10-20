<script type="text/javascript">
Ext.ns("forma002ActividadEditar");
forma002ActividadEditar.main = {
init:function(){

//<Stores de fk>
this.storeCO_MUNICIPIO = this.getStoreCO_MUNICIPIO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_PARROQUIA= this.getStoreCO_PARROQUIA();
//<Stores de fk>

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.id_tab_meta_fisica = new Ext.form.Hidden({
	name:'id_tab_meta_fisica',
	value:this.OBJ.id_tab_meta_fisica
});
//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

this.datos1 = '<p class="registro_detalle"><b>Código: </b>'+this.OBJ.codigo+'</p>';
this.datos1 +='<p class="registro_detalle"><b>Actividad: </b>'+this.OBJ.nb_meta+'</p>';
this.datos1 +='<p class="registro_detalle"><b>Fecha Programada: </b>'+this.OBJ.fecha_inicio+' - '+this.OBJ.fecha_fin+'</p>';
this.datos1 +='<p class="registro_detalle"><b>Programado: </b>'+this.OBJ.tx_prog_anual+' '+this.OBJ.de_unidad_medida+'</p>';

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

this.nu_meta_moificada = new Ext.form.NumberField({
	fieldLabel:'META MODIFICADA',
	name:'meta_modificada',
	value:this.OBJ.nu_meta_modificada,
	allowBlank:false,
	width:200,
	maxLength: 20,
	decimalPrecision: 0,
// 	minValue : 0,
// 	maxValue : 999999999999999999999,
//	msgTarget : 'Rango Entre 0 y 9',
	readOnly:this.OBJ.in_bloquear_002,
	style:(this.OBJ.in_bloquear_002==true)?'background:#f2d7d5;':'',
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 20},
	allowDecimals: false,
        validationEvent: 'blur',
	validator: function(value){
		tedm=value;
        	if(isNaN(tedm)){tedm = parseFloat(0);}
		tedf=forma002ActividadEditar.main.OBJ.tx_prog_anual;
        	if(isNaN(tedf)){tedf = parseFloat(0);}
//		forma002ActividadEditar.main.nu_meta_actualizada.setValue(parseFloat(tedf)+parseFloat(tedm));
	}         
});

this.nu_meta_actualizada = new Ext.form.NumberField({
	fieldLabel:'META ACTUALIZADA',
	name:'meta_actualizada',
	value:this.OBJ.nu_meta_actualizada,
	allowBlank:false,
	width:200,
	maxLength: 20,
	decimalPrecision: 0,
 	minValue : 0,
 	maxValue : 999999999999999999999,
	msgTarget : 'Rango Entre 0 y 9',
	readOnly:true,
	style:'background:#f2d7d5;',        
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 20},
	allowDecimals: false,
	allowNegative: false
});

this.nu_obtenido = new Ext.form.NumberField({
	fieldLabel:'OBTENIDO AL CORTE',
	name:'obtenido',
	value:this.OBJ.nu_obtenido,
	allowBlank:false,
	width:200,
	maxLength: 20,
	decimalPrecision: 0,
 	minValue : 0,
 	maxValue : 999999999999999999999,
	msgTarget : 'Rango Entre 0 y 9',
	readOnly:this.OBJ.in_bloquear_002,
	style:(this.OBJ.in_bloquear_002==true)?'background:#f2d7d5;':'',        
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 20},
	allowDecimals: false,
	allowNegative: false,
        validationEvent: 'blur',
	validator: function(value){
		tedm=value;
        	if(isNaN(tedm)){tedm = parseFloat(0);}
		tedf=forma002ActividadEditar.main.nu_meta_actualizada.getValue();
        	if(isNaN(tedf)){tedf = parseFloat(0);}
                if(tedf==0){
                forma002ActividadEditar.main.nu_corte.setValue(0);    
                }else{
		forma002ActividadEditar.main.nu_corte.setValue((parseFloat(tedm)*100)/parseFloat(tedf));
                }
	}         
});

this.nu_corte = new Ext.form.NumberField({
	fieldLabel:'% EJEC. OBTENIDA AL CORTE',
	name:'corte',
	value:this.OBJ.nu_corte,
	allowBlank:false,
	width:200,
	decimalPrecision: 2,
 	minValue : 0,
 	maxValue : 100,
	msgTarget : 'Rango Entre 0 y 9',
	readOnly:true,
	style:'background:#f2d7d5;',             
	allowDecimals: true,
	allowNegative: false,
});

this.nb_responsable = new Ext.form.TextField({
	fieldLabel:'RESPONSABLE',
	name:'responsable',
	value:this.OBJ.nb_responsable,
	width:400,
	readOnly:this.OBJ.in_bloquear_002,
	style:(this.OBJ.in_bloquear_002==true)?'background:#f2d7d5;':'',        
	allowBlank:false
});

this.resultado = new Ext.form.TextField({
	fieldLabel:'RESULTADOS OBTENIDOS',
	name:'resultado',
	value:this.OBJ.resultado,
	width:400,
	readOnly:this.OBJ.in_bloquear_002,
	style:(this.OBJ.in_bloquear_002==true)?'background:#f2d7d5;':'',        
	allowBlank:false
});

this.de_observacion = new Ext.form.TextField({
	fieldLabel:'Observacion',
	name:'observacion',
	value:this.OBJ.observacion,
	width:400,
	readOnly:this.OBJ.in_bloquear_002,
	style:(this.OBJ.in_bloquear_002==true)?'background:#f2d7d5;':'',          
});

this.id_tab_municipio_detalle = new Ext.form.ComboBox({
	fieldLabel:'LOCALIZACIÓN',
	store: this.storeCO_MUNICIPIO,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_municipio',
	hiddenName:'municipio',
	//readOnly:(this.OBJ.id_tab_tipo_personal!='')?true:false,
	//style:(this.main.OBJ.id_tab_tipo_personal!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Localizacion...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	readOnly:this.OBJ.in_bloquear_002,
	style:(this.OBJ.in_bloquear_002==true)?'background:#f2d7d5;':'',        
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_municipio}</div></div></tpl>'),
	resizable:true,
	allowBlank:false,
	listeners:{
						change: function(){
								forma002ActividadEditar.main.storeCO_PARROQUIA.load({
										params: {id_tab_municipio:this.getValue(), _token:'{{ csrf_token() }}'}
								})
						}
	}
});

this.storeCO_MUNICIPIO.load();

paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_tab_municipio_detalle,
	value:  this.OBJ.id_tab_municipio_detalle,
	objStore: this.storeCO_MUNICIPIO
});

if(this.OBJ.id_tab_municipio_detalle){
  this.storeCO_PARROQUIA.load({
                    params: {id_tab_municipio:this.OBJ.id_tab_municipio_detalle, _token: '{{ csrf_token() }}'},
                    callback: function(){
                        forma002ActividadEditar.main.id_tab_parroquia_detalle.setValue(forma002ActividadEditar.main.OBJ.id_tab_parroquia_detalle);
                    }
                });
}

this.id_tab_municipio_detalle.on('beforeselect',function(cmb,record,index){
        	this.id_tab_parroquia_detalle.clearValue();
},this);

this.id_tab_parroquia_detalle = new Ext.form.ComboBox({
	fieldLabel:'PARROQUIA',
	store: this.storeCO_PARROQUIA,
	typeAhead: true,
	valueField: 'id',
	displayField:'de_parroquia',
	hiddenName:'parroquia',
	//readOnly:(this.OBJ.co_parroquia!='')?true:false,
	//style:(this.OBJ.co_parroquia!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Parroquia',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	readOnly:this.OBJ.in_bloquear_002,
	style:(this.OBJ.in_bloquear_002==true)?'background:#f2d7d5;':'',        
	allowBlank:false
});

this.fieldset2 = new Ext.form.FieldSet({
	title: 'Datos del Seguimiento',
	items:[
		this.nb_responsable,
		this.id_tab_municipio_detalle,
		this.id_tab_parroquia_detalle,
		this.nu_meta_moificada,
                this.nu_meta_actualizada,
		this.nu_obtenido,
                this.nu_corte,
//		this.de_observacion
	]
});

this.guardar = new Ext.Button({
    text:'Revisado',
    iconCls: 'icon-fin',
    handler:function(){

        if(!forma002ActividadEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea aprobar los cambios solicitados?<br><b>Nota:</b> No se podran modificar los cambios.', function(boton){
				if(boton=="yes"){

        forma002ActividadEditar.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('seguimiento/ac/002/cambio/aprobar') }}',
						@else
							url:'{{ URL::to('seguimiento/ac/002/cambio/aprobar') }}/{!! $data->id !!}',
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
                 forma002ActividadListaCambio.main.store_lista.reload();
                 forma002ListaCambio.main.store_lista.reload();
                 forma002ActividadEditar.main.winformPanel_.close();
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

        if(!forma002ActividadEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

				Ext.MessageBox.confirm('Confirmación', '¿Realmente desea negar los cambios solicitados?<br><b>Nota:</b> El Ejecutor tendra que solicitar de nuevo los cambios.', function(boton){
				if(boton=="yes"){

        forma002ActividadEditar.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('seguimiento/ac/002/cambio/negar') }}',
						@else
							url:'{{ URL::to('seguimiento/ac/002/cambio/negar') }}/{!! $data->id !!}',
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
                 forma002ActividadListaCambio.main.store_lista.load();
                 forma002ListaCambio.main.store_lista.load();
                 forma002ActividadEditar.main.winformPanel_.close();
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
        forma002ActividadEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:800,
	labelWidth: 200,
	border:false,
	autoHeight:true,
	autoScroll:true,
	bodyStyle:'padding:10px;',
	items:[
		this._token,
                this.id_tab_meta_fisica,
		this.fieldset1,
		this.fieldset2
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: METAS FÍSICAS',
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
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.001.cambio.aprobar', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_002==false)
					this.guardar,'-',
				@endif
			@endif
			@if( in_array( array( 'de_privilegio' => 'acseguimiento.001.cambio.negar', 'in_habilitado' => true), Session::get('credencial') ))
				@if($data->in_002==false)
					this.negar,'-',
				@endif
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
forma002ListaCambio.main.mascara.hide();
},
getStoreCO_MUNICIPIO:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/municipio/todo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_municipio'}
            ],
            listeners : {
                exception : function(proxy, response, operation) {
                    Ext.Msg.alert("Aviso", 'Error al obtener respuesta del servidor intente de nuevo!');
                }
            }
    });
    return this.store;
},
getStoreCO_PARROQUIA:function(){
    this.store = new Ext.data.JsonStore({
        url:'{{ URL::to('auxiliar/parroquia/todo') }}',
        root:'data',
        fields:[
            {name: 'id'},{name: 'de_parroquia'}
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
Ext.onReady(forma002ActividadEditar.main.init, forma002ActividadEditar.main);
</script>
