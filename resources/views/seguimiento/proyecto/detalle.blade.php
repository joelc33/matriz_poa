<script type="text/javascript">
Ext.ns('proyectoseguimientoDetalle');
proyectoseguimientoDetalle.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.datos2 = '<p class="registro_detalle"><b>Código: </b>'+this.OBJ.nu_codigo+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Descripcion: </b>'+this.OBJ.de_proyecto+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Periodo de Seguimiento: </b>'+this.OBJ.fe_inicio+' - '+this.OBJ.fe_fin+'</p>';

this.fieldset2 = new Ext.form.FieldSet({
	title: 'Datos del Proyecto',
	html: this.datos2
});

this.deposito = new Ext.Button({
	text:'Cerrar',
	iconCls: 'icon-reabrir',
	handler: function(boton){
		var nu_serial  = acseguimientoLista.main.gridPanel_.getSelectionModel().getSelected().get('id');
		acseguimientoLista.main.mascara.show();
		this.msg = Ext.get('formularioDeposito');
		this.msg.load({
			url:"{{ URL::to('seguimiento/proyecto/cerrar') }}/"+nu_serial,
			scripts: true,
			text: "Cargando.."
		});
	}
});

this.formPanel_ = new Ext.form.FormPanel({
	autoWidth:true,
	border:false,
	layout:'fit',
  padding: 5,
	deferredRender: false,
  items:[
  this.fieldset2
  ],
  tbar:[
      this.deposito
  ]
});

this.formPanel_.render('proyectoseguimientoDetalle');
}
};
Ext.onReady(proyectoseguimientoDetalle.main.init, proyectoseguimientoDetalle.main);
</script>
<div id="proyectoseguimientoDetalle"></div>
<div id="formularioacseguimiento"></div>
<div id="formularioDeposito"></div>
