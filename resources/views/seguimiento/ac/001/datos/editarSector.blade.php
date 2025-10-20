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
this.de_sector = new Ext.form.TextField({
	fieldLabel:'Sector',
	name:'de_sector',
	value:this.OBJ.de_sector,
	allowBlank:false,
	width:400,
});

this.tx_ejecutor_ac = new Ext.form.TextArea({
	fieldLabel:'Ejecutor',
	name:'tx_ejecutor_ac',
	value:this.OBJ.tx_ejecutor_ac,
	allowBlank:false,
	width:400,
});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!forma001Editar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }

        forma001Editar.main.formPanel_.getForm().submit({
						method:'POST',
						@if(empty($data->id))
							url:'{{ URL::to('ac/seguimiento/001/guardarSector') }}',
						@else
							url:'{{ URL::to('ac/seguimiento/001/guardarSector') }}/{!! $data->id !!}',
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
		this.de_sector,
                this.tx_ejecutor_ac
	]
});

this.winformPanel_ = new Ext.Window({
    title:'EDITAR EJECUTOR/SECTOR',
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
forma001Lista.main.mascara.hide();
}
};
Ext.onReady(forma001Editar.main.init, forma001Editar.main);
</script>
