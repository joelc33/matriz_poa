<script type="text/javascript">
Ext.ns('ejercicioEditar');
ejercicioEditar.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.displaySolicitud = new Ext.Panel({
    title:'PERIODO A CREAR',
    style:'padding:0px;padding-top:0px;text-align:left;',
    autoWidth:true,
    frame:true,
    items:[
        {
            xtype:'displayfield',
            id:'display_num_sol',
            style:'font-size:25px',
            html:'<table border="0" style="width:100%" cellpadding="3"><tr><td style="width:25%"><b>PERIODO FISCAL:</b></td><td style="width:75%">'+this.OBJ.nu_anio+'</td></tr></table>'

        }
    ]
});

this.GrupoBotones = Ext.extend(Ext.Panel, {
    autoWidth:true,
    autoHeight:true,
    style: 'margin-top:0px',
    bodyStyle: 'padding:0px',
    padding	: '5px',
    border:false,
    autoScroll: true
});

this.botones = new this.GrupoBotones({
        //title: 'Opciones',
        items:[
          this.displaySolicitud
        ],
        bbar: [{
            xtype: 'buttongroup',
            title: 'Opciones',
            columns: 5,
            border:true,
            defaults: {
                scale: 'medium',
                iconAlign:'top'
            },
            items: [
              @if( in_array( array( 'de_privilegio' => 'ejerciciofiscal.guardar', 'in_habilitado' => true), Session::get('credencial') ))
              {
                text:'Crear',  // Generar la impresión en pdf
                iconCls:'icon-salvar',
                handler:function(){
                  Ext.MessageBox.confirm('Confirmación', '¿Realmente desea crear un nuevo Periodo?<br><b>Nota:</b> No se podran modificar los cambios.', function(boton){
                    if(boton=="yes"){
                    ejercicioLista.main.mascara.show();
                    Ext.Ajax.request({
                      method:'POST',
                      url:'{{ URL::to('mantenimiento/ejercicio/guardar') }}',
                      waitMsg: 'Enviando datos, por favor espere..',
                      waitTitle:'Enviando',
                      params:{
                        _token: '{{ csrf_token() }}',
                        periodo: '{{ $ejercicio }}'
                      },
                      success:function(result, request ) {
                        obj = Ext.util.JSON.decode(result.responseText);

                        var errores = '';
                        for(datos in obj.msg){
                          errores += obj.msg[datos] + '<br>';
                        }

                        if(obj.success==true){
                          ejercicioLista.main.store_lista.load();
                          Ext.Msg.alert("Notificación",obj.msg);
                          this.panelCambio = Ext.getCmp('tabpanel');
                          this.panelCambio.remove('nuevoEjercicio');
                          ejercicioLista.main.mascara.hide();
                        }else{
                          Ext.Msg.alert("Notificación",errores);
                          ejercicioLista.main.mascara.hide();
                        }

                      }
                    });
                    }
                  });
                }
              },
              @endif
              {
                text:'Cancelar',  // Limpiar campos del formulario
                iconCls:'icon-canceladeclara',
                handler: function(){

                  this.panelCambio = Ext.getCmp('tabpanel');
              		this.panelCambio.remove('nuevoEjercicio');

                }

              }
            ]
        }]
});

this.botones.render('ejercicioEditar');
}
};
Ext.onReady(ejercicioEditar.main.init, ejercicioEditar.main);
</script>
<div id="ejercicioEditar"></div>
