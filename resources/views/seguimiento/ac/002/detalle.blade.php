<script type="text/javascript">
Ext.ns('acseguimientoDetalle');
acseguimientoDetalle.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.datos2 = '<p class="registro_detalle"><b>Código: </b>'+this.OBJ.nu_codigo+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Descripcion: </b>'+this.OBJ.de_ac+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Periodo de Seguimiento: </b>'+this.OBJ.fe_inicio+' - '+this.OBJ.fe_fin+'</p>';

this.fieldset2 = new Ext.form.FieldSet({
	title: 'Datos de la Accion Centralizada',
	html: this.datos2
});

this.editar = new Ext.Button({
	text:'Datos',
	iconCls: 'icon-editar',
  handler:function(){
		addTab('foma002{!! $data['nu_codigo'] !!}','F002 - A.C: {!! $data['nu_codigo'] !!}','{{ URL::to('ac/seguimiento/002/datos') }}/'+{!! $data['id'] !!},'load','icon-editar','');
	}
});

this.cerrar = new Ext.Button({
	text:'Cerrar',
	iconCls: 'icon-guardar',
  handler:function(){
		addTab('foma002'+this.OBJ.nu_codigo,'A.C: '+this.OBJ.nu_codigo,'{{ URL::to('ac/seguimiento/001/datos') }}/'+this.OBJ.id,'load','icon-editar','');
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
      this.editar/*,'-',this.cerrar*/
  ]
});

this.formPanel_.render('acseguimientoDetalle');
}
};
Ext.onReady(acseguimientoDetalle.main.init, acseguimientoDetalle.main);
</script>
<div id="acseguimientoDetalle"></div>
<div id="formularioacseguimiento"></div>
<div id="formularioEditar{!! $data['id'] !!}"></div>
