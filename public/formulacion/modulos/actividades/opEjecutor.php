<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}  
include("../../configuracion/ConexionComun.php");

function  ArmaOpcion($co_menu){
	$comunes = new ConexionComun();
	$sql= "SELECT co_menu_acceso, tx_menu_acceso, tx_menu_acceso_url FROM t06_menu_acceso WHERE co_menu=$co_menu;";
	$resultado = $comunes->ObtenerFilasBySqlSelect($sql);			
        $radio = '';
        foreach($resultado as $key => $fila){
			$radio.="{
				boxLabel: '".$fila["tx_menu_acceso"]."',
				name: 'co_menu_acceso',
				inputValue: '".$fila["co_menu_acceso"]."'
			},";
                }
        return $radio;
}
function  VerificarOpcion($co_menu){
	$comunes = new ConexionComun();
	$sql= "SELECT co_menu_acceso, tx_menu_acceso, tx_menu_acceso_url FROM t06_menu_acceso WHERE co_menu=$co_menu;";
	$resultado = $comunes->ObtenerFilasBySqlSelect($sql);			
        $switch = '';
        foreach($resultado as $key => $fila){
			$switch.="case '".$fila["co_menu_acceso"]."' : tx_opcion_url = '".$fila["tx_menu_acceso_url"]."'; break;";
                }
        return $switch;
}
?>
<script type="text/javascript">
Ext.ns("opcionEjecutor");
opcionEjecutor.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

this.formularioUno = new Ext.form.FormPanel({
	border: false,
	items: [{
		html: "<br><h1>¿Que desea Hacer?</h1><br>",
		border: false},
		{xtype: 'fieldset', autoHeight: true,
        		items: [{xtype: 'radiogroup',autoHeight: true, defaultType: 'radio', allowBlank: false, anchor: '95%',
				items: [{columnWidth: '.25', items: [<?php echo ArmaOpcion(5); ?>]}]	
		}]},
		{html : "<p><br>Seleccione la opcion a realizar y presione Siguiente:</p>",border : false}]
});

this.panel = new Ext.Panel({
	//title: 'Crear / Actualizar Expendedor',
	layout: "fit",
	border: false,
	padding	: 10,
	items: [this.formularioUno],
	buttonAlign:'left',
	buttons:[{
			text:'Siguiente',
			iconCls:'icon-siguiente',
			handler:function(){
				if(!opcionEjecutor.main.formularioUno.getForm().isValid()){
				    Ext.Msg.alert("Alerta","Debe Seleccionar una Opcion");
				    return false;
				}
				opcionEjecutor.main.verificar(opcionEjecutor.main.formularioUno.getForm().getValues()['co_menu_acceso']);
			}
	}]
});

this.panel.render("contenedoropcionEjecutor");
},
verificar: function(values){
	var tx_opcion_url = "";
	switch(values) {
		<?php echo VerificarOpcion(5); ?> 
	}
	var direccionar = Ext.get('contenedoropcionEjecutor');
	direccionar.load({ url: tx_opcion_url, scripts: true, text: 'Cargando...'});
}
};
Ext.onReady(opcionEjecutor.main.init, opcionEjecutor.main);
</script>
<div id="contenedoropcionEjecutor"></div>
