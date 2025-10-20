<?php 
include ("funcionMenu.php");
session_start();
$co_rol= $_SESSION['co_rol'];
if($_SESSION['estatus']!='OK'){
	header('Location: .');
}
$comunes = new ConexionComun();
/*$sql = "SELECT fecha_creacion FROM t05_login_accion WHERE co_usuario = ".$_SESSION['co_usuario']." and co_tipo_accion= 1 ORDER BY fecha_creacion DESC LIMIT 1 OFFSET 1;";*/
$sql = "SELECT created_at FROM auditoria.tab_login_acceso WHERE id_tab_usuarios = ".$_SESSION['co_usuario']." and id_tap_tipo_accion = 1 ORDER BY created_at DESC LIMIT 1 OFFSET 1;";
$resultado = $comunes->ObtenerFilasBySqlSelect($sql);
/*$sql2 = "SELECT tx_bandeja,tx_url_bandeja FROM t02_rol WHERE co_rol = ".$co_rol.";";*/
$sql2 = "SELECT de_bandeja, de_url_bandeja FROM autenticacion.tab_rol WHERE id = ".$co_rol.";";
$resultado2 = $comunes->ObtenerFilasBySqlSelect($sql2);
/*$sql3 = "SELECT t01.*,t29.co_documento, nu_cedula, nb_funcionario, ap_funcionario, 
        t29.co_ejecutores, co_cargo, tx_direccion, tx_telefono, tx_email, inicial, tx_ejecutor, tx_rol FROM t01_usuario as t01 
	inner join t02_rol as t02 on t01.co_rol=t02.co_rol
	left join t29_funcionario as t29 on t01.co_funcionario=t29.co_funcionario
	left join t24_ejecutores as t24 on t29.co_ejecutores=t24.co_ejecutores
	left join t11_documento as t11 on t29.co_documento=t11.co_documento
	WHERE co_usuario = ".$_SESSION['co_usuario'].";";*/
$sql3 = "SELECT t01.id, nu_cedula, nb_funcionario, 
	ap_funcionario, t24.id_ejecutor , tx_email, inicial, tx_ejecutor , t01.created_at 
	FROM autenticacion.tab_usuarios as t01 
	left join mantenimiento.tab_funcionario as t29 on t01.id=t29.id_tab_usuarios
	left join mantenimiento.tab_ejecutores as t24 on t29.id_tab_ejecutores=t24.id
	left join mantenimiento.tab_documento as t11 on t29.id_tab_documento=t11.id
	WHERE t01.id = ".$_SESSION['co_usuario'].";";
$resultado3 = $comunes->ObtenerFilasBySqlSelect($sql3);
/*$sql4 = "SELECT co_ejercicio_fiscal FROM t25_ejercicio_fiscal WHERE edo_reg is true;";*/
/*$sql4 = "SELECT id FROM mantenimiento.tab_ejercicio_fiscal WHERE in_activo is true;";*/
$sql4 = "SELECT id FROM mantenimiento.tab_ejercicio_fiscal WHERE id = ".$_SESSION['ejercicio_fiscal'].";";
$resultado4 = $comunes->ObtenerFilasBySqlSelect($sql4);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>..::NUEVA ETAPA | SPE::..</title>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="images/favicon.ico" />
<link rel="stylesheet" type="text/css" media="screen" href="css/main.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ext-all.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/iconos.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/combos.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/fileuploadfield.css" />
	<div id="loading-mask" style=""></div>
  	<div id="loading">
		<div class="loading-indicator">
                <img src="images/32x32/blue-loading.gif" width="32" height="32" style="margin-right:8px; padding-left:120px; float:left;vertical-align:top;"/>
                 ..::NUEVA ETAPA - ZULIA::..<br />
                <span id="loading-msg">Cargando...</span>
            </div>
        </div>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando el Componente Central ...';</script>
<script type="text/javascript" src="js/ext-3.4.1/adapter/ext/ext-base.js"></script>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando la Interfaz Grafica...';</script>
<script type="text/javascript" src="js/ext-3.4.1/ext-all.js"></script>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando Esquema General...';</script>
<script type="text/javascript" src="js/funciones_comunes/paqueteComun.js"></script>
<script type="text/javascript" src="js/open/js/swfobject.js"></script>
<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Cargando el idioma...';</script>
<script type="text/javascript" src="js/ext-3.4.1/locale/ext-lang-es.js"></script>
<script type="text/javascript" src="js/ext-3.4.1/ux/ux-all.js"></script>
<script type="text/javascript" src="js/funciones_comunes/Ext.util.JSON.js"></script>
<script type="text/javascript" src="js/funciones_comunes/SuperBoxSelect.js"></script>
<script type="text/javascript" src="js/funciones_comunes/CMS.view.FileDownload.js"></script>

<script type="text/javascript">
(function(){
    var mensaje = {
        title: 'Error',
        msg: 'Su sesión ha expirado. Debe volver a identificarse.',
        buttons: Ext.Msg.OK,
        icon: Ext.MessageBox.ERROR,
        fn: function(){
            document.location.href = 'index.php';
        }
    };
    Ext.Ajax.on('requestcomplete', function(conn, resp){
        var head, rHead;

        head = "<!DOCTYPE html PUBLIC";
        rHead = resp.responseText.substring(0, head.length);
        if ( rHead == head ) {
            Ext.Msg.show(mensaje);
        }
    });
    Ext.Ajax.on('requestexception', function(conn, resp){
        if ( resp.status === 403 ) {
            Ext.Msg.show(mensaje);
        }
    });
}());

Ext.QuickTips.init();
Ext.form.Field.prototype.msgTarget = 'side';
this.panel_detalle =  new Ext.Panel({
        region: 'east', //
        title: 'Detalles del Registro',
        id: 'detalle_registro',
        collapsible: true,
        collapseMode: 'mini',
        collapsed:true,
        split: true,
        autoScroll: true,
        titleCollapse: true,
        deferredRender: false,
        width:350,
	margins: '0 0 5 0',
        script:true,
	iconCls: 'icon-reporteest',
        items:[
		new Ext.Panel({
			id: 'detalle'
		})
        ]
});

this.comboTemas = new Ext.ux.ThemeCombo({
	width:100
});

this.tabpanel = new Ext.TabPanel({
	region: 'center',
	deferredRender: false,
	id: 'tabpanel',
	border:true,
	autoScroll: false,
	enableTabScroll:true,
	activeItem:0,
	listeners: {'tabchange': function(tabPanel, tab){panel_detalle.collapse();}},
	items:[{
		id: 'tabPrincipal',
		border:false,
		title: '<?php echo $resultado2[0][de_bandeja]; ?>',
                autoScroll:true,
		iconCls:'icon-inicio',
		contentEl:'centro',
	        layout:'fit',
		autoLoad: {url: '<?php echo $resultado2[0][de_url_bandeja]; ?>', scripts: true, scope: this}
	}]
});
Ext.onReady(function(){
//this.datosUsuario = '<img width="100" height="80" src="images/logo.png">';
this.datosUsuario = '<p class="registro_detalle"><b><span style="color:red;font-size:13px;">Bienvenido, <?php echo $resultado3[0][nb_funcionario].' '.$resultado3[0][ap_funcionario]; ?> </span></b></p>';
this.datosUsuario += '<p class="registro_detalle"><b>Fecha de Registro: </b><?php echo trim(date_format(date_create($resultado3[0][created_at]),"d/m/Y")); ?></p>';
this.datosUsuario += '<p class="registro_detalle"><b>Cedula: </b><?php echo $resultado3[0][inicial].'-'.$resultado3[0][nu_cedula]; ?></p>';
this.datosUsuario +='<p class="registro_detalle"><b>Nombre: </b> <?php echo $resultado3[0][nb_funcionario].' '.$resultado3[0][ap_funcionario]; ?></p>';
this.datosUsuario +='<p class="registro_detalle"><b>Unidad Ejecutora: </b> <?php echo $resultado3[0][tx_ejecutor]; ?></p>';
this.datosUsuario +='<p class="registro_detalle"><b>Ejercicio Fiscal: </b> <?php echo $resultado4[0][id]; ?></p>';
this.datosUsuario += '<p class="registro_detalle"><b>Ultimo login: </b><?php echo trim(date_format(date_create($resultado[0][created_at]),"d/m/Y - h:i A")); ?></p>';

this.btnSalir = new Ext.Button({
	text: 'Cerrar sesi&oacute;n',
	handler: logOut,
	iconCls:'icon-salir2'
});
	   
this.reloj = new Ext.Toolbar.TextItem('');
//correr reloj
Ext.TaskMgr.start({run: function(){Ext.fly(reloj.getEl()).update(new Date().format('g:i:s A'));},interval: 1000});
//descargador
this.bajar = new CMS.view.FileDownload();
//barra de estatus
this.estatusbar = new Ext.Toolbar({items:[this.reloj,'-',this.btnSalir]}); 
var viewport = new Ext.Viewport({
layout: 'fit',
items: [{
	layout: 'border',
	items: [
		    // create instance immediately
		    new Ext.BoxComponent({
		        region: 'north',
		        height: 60, // give north and south regions a height
		 	contentEl:'header'
		    }),{
		        region: 'west',
		        id: 'navegador', // see Ext.getCmp() below
		        title: '.::NUEVA ETAPA::.',
			iconCls: 'icon-navegacion',
		        split: true,
		        width: 240,
		        minSize: 200,
		        maxSize: 600,
			autoScroll:true,
		        collapsible: true,
			animCollapse: true,
			collapsedTitle: true,
		        margins: '0 0 0 0',
			bbar: this.estatusbar,
			bodyStyle: "background-image:url('images/logotipo.png');background-repeat: no-repeat;    background-attachment: fixed; background-position: 4.5% 93%; background-size: 120px 230px; !important;",
			layout: 'accordion',
			layoutConfig: {
				animate: true
			},
		        items: [
				{
				title:'<b>Mi Cuenta</b>',
				autoScroll:true,
				border:false,
				collapsed:false,
				iconCls:'icon-usuario',
				autoHeight:true,
				html: miCuenta(this.datosUsuario)
				},
				<?php echo ArmaMenu($co_rol); ?>
				]
		    },
		    this.tabpanel,
            	    this.panel_detalle
           ]
}, this.bajar ]
});
});

function showResult(btn){
	if(btn=="yes"){
		Ext.MessageBox.show({title: 'Cerrando sesi&oacute;n', msg: '<br>Por favor  Espere...',width:300,closable:false,icon:Ext.MessageBox.INFO});
		location.href='.';
	}
}

function logOut(){
	Ext.MessageBox.confirm('Confirmar', 'Seguro que desea salir de la Aplicaci&oacute;n?', showResult);
}
</script>

</head>
<div id="header">
	<div style="background-color:white; padding-left:0px; padding-right:0px; padding-bottom:0px;">
<!--	<img height="58" src="images/izquierda.png"> 
	<img height="58" align="right" src="images/derecha.png">-->
	</div>
</div>
<body style="background: #FFFFFF">
	<div id="centro" align="center" style="padding-bottom: 1%;width:100%;height:600px;"> </div>    
<!--	<img src="images/logo.jpg" width="120" style="position: absolute; top: 60%; left: 50px;" />
	<img src="images/logo.png" width="70" style="position: absolute; top: 85%; left: 70px;" />
	</div>
	<div id="centro" class="x-hide-display"></div>-->
</body>
</html>
