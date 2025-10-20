<script type="text/javascript">
Ext.ns('acseguimientoDetalleCambio');
acseguimientoDetalleCambio.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.datos2 = '<p class="registro_detalle"><b>Fecha de Solicitud: </b>'+this.OBJ.fe_solicitud+'</p>';
this.datos2 += '<p class="registro_detalle"><b>Ejecutor: </b>'+this.OBJ.tx_ejecutor_ac+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Código: </b>'+this.OBJ.nu_codigo+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Descripcion: </b>'+this.OBJ.de_ac+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Periodo de Seguimiento: </b>'+this.OBJ.fe_inicio+' - '+this.OBJ.fe_fin+'</p>';
//this.datos2 +='<p class="registro_detalle"><b>Obervacion: </b>'+this.OBJ.de_observacion+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Usuario: </b>'+this.OBJ.da_login_a+'</p>';
@if(!empty($data->da_login_b))
this.datos2 +='<p class="registro_detalle"><b>Atendio: </b>'+this.OBJ.da_login_b+'</p>';
@endif

this.fieldset2 = new Ext.form.FieldSet({
	title: 'Datos de la Accion Centralizada',
	html: this.datos2
});

this.editar = new Ext.Button({
	text:'Datos',
	iconCls: 'icon-editar',
  /*handler:function(){
		addTab('foma005{!! $data['nu_codigo'] !!}','A.C: {!! $data['nu_codigo'] !!}','{{ URL::to('ac/seguimiento/005/datos') }}/'+{!! $data['id'] !!},'load','icon-editar','');
	}*/
	handler:function(){
	this.codigo  = forma005ListaCambio.main.gridPanel_.getSelectionModel().getSelected().get('id');
	forma005ListaCambio.main.mascara.show();
			this.msg = Ext.get('formularioEditar{!! $data['id'] !!}');
			this.msg.load({
			 url:"{{ URL::to('seguimiento/ac/005/cambio/editar') }}/"+this.codigo,
			 scripts: true,
			 text: "Cargando.."
			});
	}
});

this.cerrar = new Ext.Button({
	text:'Cerrar',
	iconCls: 'icon-guardar',
  handler:function(){
		addTab('foma005'+this.OBJ.nu_codigo,'A.C: '+this.OBJ.nu_codigo,'{{ URL::to('ac/seguimiento/005/datos') }}/'+this.OBJ.id,'load','icon-editar','');
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
		@if( in_array( array( 'de_privilegio' => 'acseguimiento.005.cambio.editar', 'in_habilitado' => true), Session::get('credencial') ))
      this.editar/*,'-',this.cerrar*/
		@endif
  ]
});

this.formPanel_.render('acseguimientoDetalleCambio');
}
};
Ext.onReady(acseguimientoDetalleCambio.main.init, acseguimientoDetalleCambio.main);
</script>
<div id="acseguimientoDetalleCambio"></div>
<div id="formularioacseguimiento"></div>
<div id="formularioEditar{!! $data['id'] !!}"></div>
