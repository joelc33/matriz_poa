<script type="text/javascript">
Ext.ns('formaejecucionListaDetalle');
formaejecucionListaDetalle.main = {
init: function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'{!! $data !!}'});

this.datos2 = '<p class="registro_detalle"><b>Disponibilidad Financiera: </b>'+formatoNumero(this.OBJ.mo_df)+'</p>';
this.datos2 +='<p class="registro_detalle"><b>Disponibilidad Presupuestaria: </b>'+formatoNumero(this.OBJ.mo_dp)+'</p>';

this.fieldset2 = new Ext.form.FieldSet({
	title: 'DISPONIBILIDAD: '+this.OBJ.co_partida,
	html: this.datos2
});

this.formPanel_ = new Ext.form.FormPanel({
	autoWidth:true,
	border:false,
	layout:'fit',
  padding: 5,
	deferredRender: false,
  items:[
  this.fieldset2
  ]
});

this.formPanel_.render('formaejecucionListaDetalle');
}
};
Ext.onReady(formaejecucionListaDetalle.main.init, formaejecucionListaDetalle.main);
</script>
<div id="formaejecucionListaDetalle"></div>
