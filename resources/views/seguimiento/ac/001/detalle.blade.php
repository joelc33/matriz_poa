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
  /*handler:function(){
		addTab('foma001{!! $data['nu_codigo'] !!}','A.C: {!! $data['nu_codigo'] !!}','{{ URL::to('ac/seguimiento/001/datos') }}/'+{!! $data['id'] !!},'load','icon-editar','');
	}*/
	handler:function(){
	this.codigo  = forma001Lista.main.gridPanel_.getSelectionModel().getSelected().get('id');
	forma001Lista.main.mascara.show();
			this.msg = Ext.get('formularioEditar{!! $data['id'] !!}');
			this.msg.load({
			 url:"{{ URL::to('ac/seguimiento/001/editar') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
			});
	}
});

this.cerrar = new Ext.Button({
	text:'Cerrar',
	iconCls: 'icon-guardar',
  handler:function(){
		addTab('foma001'+this.OBJ.nu_codigo,'A.C: '+this.OBJ.nu_codigo,'{{ URL::to('ac/seguimiento/001/datos') }}/'+this.OBJ.id,'load','icon-editar','');
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
		@if( in_array( array( 'de_privilegio' => 'acseguimiento.001.editar', 'in_habilitado' => true), Session::get('credencial') ))
      this.editar/*,'-',this.cerrar*/
		@endif
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
