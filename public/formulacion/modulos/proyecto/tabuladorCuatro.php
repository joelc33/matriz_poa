<?php
if($co_proyecto_imagen!=''||$co_proyecto_imagen!=null){
	$sql = "SELECT co_proyecto_imagen, id_proyecto, mime_proyecto, nb_archivo_proyecto, mime_satelital, nb_archivo_satelital FROM t36_proyecto_imagen WHERE co_proyecto_imagen=".$co_proyecto_imagen;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data4 = json_encode(array(
			"co_proyecto_imagen"     => trim($row["co_proyecto_imagen"]),
			"id_proyecto"     => trim($row["id_proyecto"]),
			"mime_proyecto"     => trim($row["mime_proyecto"]),
			"nb_archivo_proyecto"     => trim($row["nb_archivo_proyecto"]),
			"mime_satelital"     => trim($row["mime_satelital"]),
			"nb_archivo_satelital"     => trim($row["nb_archivo_satelital"]),
		));
	}
}else{
	$data4 = json_encode(array(
		"co_proyecto_imagen"     => "",
		"id_proyecto"     => $id_proyecto,
		"mime_proyecto"     => "",
		"nb_archivo_proyecto"     => "",
		"mime_satelital"     => "",
		"nb_archivo_satelital"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("tabuladorCuatro");
tabuladorCuatro.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data4 ?>'});

//<ClavePrimaria>
this.co_proyecto_imagen = new Ext.form.Hidden({
	name:'co_proyecto_imagen',
	value:this.OBJ.co_proyecto_imagen
});
//</ClavePrimaria>

this.imagenProyecto = new Ext.form.FieldSet({
    border:false,
    html:'<img width="250" height="155" src="formulacion/modulos/imagen/funcion.php?op=1&id_proyecto='+tabuladorCuatro.main.OBJ.id_proyecto+'&t='+new Date().getTime()+'">'
});

this.fotoProyecto = new Ext.ux.form.FileUploadField({
	emptyText: 'Seleccione una Imagen',
	fieldLabel: '4.1. IMAGEN FOTOGRÁFICA DEL PROYECTO',
	name:'fotoProyecto',
	buttonText: '',
	value:this.OBJ.nb_archivo_proyecto,
	buttonCfg: {
		iconCls: 'icon-subirImagen'
	},
	width:200
});

this.proyectoField = new Ext.form.FieldSet({
	title: 'FOTOGRÁFICA DEL PROYECTO',
	border:true,
	items:[
		this.imagenProyecto,
		this.fotoProyecto,
	]
});

this.imagenSatelite = new Ext.form.FieldSet({
    border:false,
    html:'<img width="250" height="155" src="formulacion/modulos/imagen/funcion.php?op=2&id_proyecto='+tabuladorCuatro.main.OBJ.id_proyecto+'&t='+new Date().getTime()+'">'
});

this.fotoSatelite = new Ext.ux.form.FileUploadField({
	emptyText: 'Seleccione una Imagen',
	fieldLabel: '4.2. IMAGEN SATELITAL DEL PROYECTO',
	name:'fotoSatelital',
	buttonText: '',
	value:this.OBJ.nb_archivo_satelital,
	buttonCfg: {
		iconCls: 'icon-subirImagen'
	},
	width:200
});

this.sateliteField = new Ext.form.FieldSet({
	title: 'IMAGEN SATELITAL DEL PROYECTO',
	border:true,
	items:[
		this.imagenSatelite,
		this.fotoSatelite,
	]
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.proyectoField,
		this.sateliteField
		]
});

this.panelDatos = new Ext.Panel({
    title: '4. IMAGEN DEL PROYECTO',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.co_proyecto_imagen,
		this.fieldset1
	]
});

}
};
Ext.onReady(tabuladorCuatro.main.init, tabuladorCuatro.main);
</script>
