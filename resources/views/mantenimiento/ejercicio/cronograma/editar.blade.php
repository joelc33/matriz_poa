<script type="text/javascript">
Ext.ns("cronogramaEditar");
cronogramaEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>

//<ClavePrimaria>
this.ejercicio = new Ext.form.Hidden({
    name:'periodo',
    value:this.OBJ.id_tab_ejercicio_fiscal});
//</ClavePrimaria>

this.fe_desde = new Ext.form.DateField({
	fieldLabel:'Fecha Apertura',
	name:'fecha_apertura',
	value:this.OBJ.fe_desde,
	minValue:this.OBJ.fe_ini,
	maxValue:this.OBJ.fe_fin,
	allowBlank:false,
	width:100
});

this.fe_hasta = new Ext.form.DateField({
	fieldLabel:'Fecha Cierre',
	name:'fecha_cierre',
	value:this.OBJ.fe_hasta,
	minValue:this.OBJ.fe_ini,
	maxValue:this.OBJ.fe_fin,
	allowBlank:false,
	width:100
});

this.de_apertura = new Ext.form.TextField({
	fieldLabel:'Descipcion',
	name:'descripcion',
	value:this.OBJ.de_apertura,
	allowBlank:false,
	width:400
});

this.fieldset1 = new Ext.form.FieldSet({
	title: 'Datos del Periodo',
	autoWidth:true,
        items:[
		this.fe_desde,
		this.fe_hasta,
		this.de_apertura
		]
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!cronogramaEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        cronogramaEditar.main.formPanel_.getForm().submit({
		method:'POST',
	@if(empty($data->id))
		url:'{{ URL::to('mantenimiento/ejercicio/cronograma/guardar') }}',
	@else
		url:'{{ URL::to('mantenimiento/ejercicio/cronograma/guardar') }}/{!! $data->id !!}',
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
                 cronogramaLista.main.store_lista.load();
                 cronogramaEditar.main.winformPanel_.close();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        cronogramaEditar.main.winformPanel_.close();
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
    this.ejercicio,
		this.fieldset1
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Periodo',
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
			@if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.cronograma.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
cronogramaLista.main.mascara.hide();
}
};
Ext.onReady(cronogramaEditar.main.init, cronogramaEditar.main);
</script>
