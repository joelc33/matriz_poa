<script type="text/javascript">
Ext.ns("rolEditar");
rolEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

//<token>
this._token = new Ext.form.Hidden({
	name:'_token',
	value:'{{ csrf_token() }}'
});
//</token>
//<ClavePrimaria>
this.co_rol = new Ext.form.Hidden({
    name:'co_rol',
    value:this.OBJ.co_rol});
//</ClavePrimaria>


this.tx_rol = new Ext.form.TextField({
	fieldLabel:'Nombre del Rol',
	name:'tx_rol',
	value:this.OBJ.tx_rol,
	allowBlank:false,
	width:200,
	style:'background:#c9c9c9;',
	readOnly:true
});

this.opciones = new Ext.tree.TreePanel({
                    id:'im-tree',
                    loader: new Ext.tree.TreeLoader(),
                    rootVisible:false,
                    lines:true,
                    autoScroll:true,
                    border: false,
                    height:300,
                    iconCls:'nav',
                    root: new Ext.tree.AsyncTreeNode({
                        text:'Inicio',
                        children:[{!! $menu !!}]

                    })
});

this.fielsetOP = new Ext.form.FieldSet({
              title:'Parametros del Rol',
              items:[this.opciones]});

function array1dToJson(a, p) {
	  var i, s = '[';
	  for (i = 0; i < a.length; ++i) {
	    if (typeof a[i] == 'string') {
	      s += '{"id_menu":' + a[i] + '}';
	    }
	    else { // assume number type
	      s += a[i];
	    }
	    if (i < a.length - 1) {
	      s += ',';
	    }
	  }
	  s += ']';
	  /*if (p) {
	    return '{"' + p + '":' + s + '}';
	  }*/
	  return s;
}

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

                var check = new Array();
                var selNodes =  rolEditar.main.opciones.getChecked();
                var i = 0;
                Ext.each(selNodes, function(node){
                     check[i]=node.id;
                     i++;
                });
                var array = array1dToJson(check,'seleccion');

        if(!rolEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        rolEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'{{ URL::to('rol/guardarPrivilegio') }}',
	    params:{seleccion:array},
            waitMsg: 'Enviando datos, por favor espere..',
            waitTitle:'Enviando',
            failure: function(form, action) {
                Ext.MessageBox.alert('Error en transacción', action.result.msg);
            },
            success: function(form, action) {
                 if(action.result.success){
			//Ext.utiles.msg('Mensaje del Sistema', action.result.msg);
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
                 rolLista.main.store_lista.load();
                 rolEditar.main.winformPanel_.hide();
             }
        });


    }
});

this.salir = new Ext.Button({
    text:'Salir',
    handler:function(){
        rolEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
//    frame:true,
    width:400,border:false,
    autoHeight:true,
    autoScroll:true,
    bodyStyle:'padding:10px;',
    items:[
		this._token,
		this.co_rol,
		this.tx_rol,
		this.fielsetOP
            ]
});

this.winformPanel_ = new Ext.Window({
    title:'Ficha: rol',
    modal:true,
    constrain:true,
	width:410,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
			@if( in_array( array( 'de_privilegio' => 'privilegios.guardar', 'in_habilitado' => true), Session::get('credencial') ))
				this.guardar,
			@endif
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
this.opciones.getRootNode().expand(true);
rolLista.main.mascara.hide();
}
};
Ext.onReady(rolEditar.main.init, rolEditar.main);
</script>
