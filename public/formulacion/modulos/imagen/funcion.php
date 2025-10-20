<?php
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
} 
include("../../configuracion/ConexionComun.php");

$comunes = new ConexionComun();

if($_GET['op']==1){
        $cadena="host=".SERVIDOR." port=5432 dbname=".BASEDEDATOS." user=".USUARIO." password=".CLAVE."";
        $conexion = pg_connect($cadena);

        $sql = "select im_proyecto, mime_proyecto from t36_proyecto_imagen where id_proyecto = '".$_GET['id_proyecto']."'";
        //echo  $sql;
        $result=pg_query($conexion, $sql);
        $row=pg_fetch_array($result,0);
	if($row['im_proyecto']==null){
		$im = imagecreatetruecolor(300, 200);
		imagefilledrectangle($im, 0, 0, 300, 200, 0xFFFFFF);
		imagestring($im, 1, 5, 5, 'IMAGEN NO DISPONIBLE', 0x190707);
		header('Content-type: image/gif');
		imagegif($im);
		imagedestroy($im);
	}
	else{
		$file=pg_unescape_bytea($row['im_proyecto']);
		header('Content-Type: '.$row['mime_proyecto'].'');
		echo $file;
	}
}elseif($_GET['op']==2){
        $cadena="host=".SERVIDOR." port=5432 dbname=".BASEDEDATOS." user=".USUARIO." password=".CLAVE."";
        $conexion = pg_connect($cadena);

        $sql = "select im_satelital, mime_satelital from t36_proyecto_imagen where id_proyecto = '".$_GET['id_proyecto']."'";
        //echo  $sql;
        $result=pg_query($conexion, $sql);
        $row=pg_fetch_array($result,0);
	if($row['im_satelital']==null){
		$im = imagecreatetruecolor(300, 200);
		imagefilledrectangle($im, 0, 0, 300, 200, 0xFFFFFF);
		imagestring($im, 1, 5, 5, 'IMAGEN NO DISPONIBLE', 0x190707);
		header('Content-type: image/gif');
		imagegif($im);
		imagedestroy($im);
	}
	else{
		$file=pg_unescape_bytea($row['im_satelital']);
		header('Content-Type: '.$row['mime_satelital'].'');
		echo $file;
	}
}elseif($_GET['op']==3){
        $cadena="host=".SERVIDOR." port=5432 dbname=".BASEDEDATOS." user=".USUARIO." password=".CLAVE."";
        $conexion = pg_connect($cadena);

        $sql = "select im_proyecto, mime_proyecto from proyecto_seguimiento.tab_proyecto_imagen where id_tab_proyecto = '".$_GET['id_proyecto']."'";
        //echo  $sql;
        $result=pg_query($conexion, $sql);
        $row=pg_fetch_array($result,0);
	if($row['im_proyecto']==null){
		$im = imagecreatetruecolor(300, 200);
		imagefilledrectangle($im, 0, 0, 300, 200, 0xFFFFFF);
		imagestring($im, 1, 5, 5, 'IMAGEN NO DISPONIBLE', 0x190707);
		header('Content-type: image/gif');
		imagegif($im);
		imagedestroy($im);
	}
	else{
		$file=pg_unescape_bytea($row['im_proyecto']);
		header('Content-Type: '.$row['mime_proyecto'].'');
		echo $file;
	}
}elseif($_GET['op']==4){
        $cadena="host=".SERVIDOR." port=5432 dbname=".BASEDEDATOS." user=".USUARIO." password=".CLAVE."";
        $conexion = pg_connect($cadena);

        $sql = "select im_satelital, mime_satelital from proyecto_seguimiento.tab_proyecto_imagen where id_tab_proyecto = '".$_GET['id_proyecto']."'";
        //echo  $sql;
        $result=pg_query($conexion, $sql);
        $row=pg_fetch_array($result,0);
	if($row['im_satelital']==null){
		$im = imagecreatetruecolor(300, 200);
		imagefilledrectangle($im, 0, 0, 300, 200, 0xFFFFFF);
		imagestring($im, 1, 5, 5, 'IMAGEN NO DISPONIBLE', 0x190707);
		header('Content-type: image/gif');
		imagegif($im);
		imagedestroy($im);
	}
	else{
		$file=pg_unescape_bytea($row['im_satelital']);
		header('Content-Type: '.$row['mime_satelital'].'');
		echo $file;
	}
}elseif($_GET['op']==5){
        $cadena="host=".SERVIDOR." port=5432 dbname=".BASEDEDATOS." user=".USUARIO." password=".CLAVE."";
        $conexion = pg_connect($cadena);

        $sql = "select im_meta, mime_meta from proyecto_seguimiento.tab_meta_imagen where id = '".$_GET['codigo']."'";
        //echo  $sql;
        $result=pg_query($conexion, $sql);
        $row=pg_fetch_array($result,0);
	if($row['im_meta']==null){
		$im = imagecreatetruecolor(300, 200);
		imagefilledrectangle($im, 0, 0, 300, 200, 0xFFFFFF);
		imagestring($im, 1, 5, 5, 'IMAGEN NO DISPONIBLE', 0x190707);
		header('Content-type: image/gif');
		imagegif($im);
		imagedestroy($im);
	}
	else{
		$file=pg_unescape_bytea($row['im_meta']);
		header('Content-Type: '.$row['mime_meta'].'');
		echo $file;
	}
}elseif($_GET['op']==6){
        $cadena="host=".SERVIDOR." port=5432 dbname=".BASEDEDATOS." user=".USUARIO." password=".CLAVE."";
        $conexion = pg_connect($cadena);

        $sql = "select im_meta, mime_meta from ac_seguimiento.tab_meta_imagen where id = '".$_GET['codigo']."'";
        //echo  $sql;
        $result=pg_query($conexion, $sql);
        $row=pg_fetch_array($result,0);
	if($row['im_meta']==null){
		$im = imagecreatetruecolor(300, 200);
		imagefilledrectangle($im, 0, 0, 300, 200, 0xFFFFFF);
		imagestring($im, 1, 5, 5, 'IMAGEN NO DISPONIBLE', 0x190707);
		header('Content-type: image/gif');
		imagegif($im);
		imagedestroy($im);
	}
	else{
		$file=pg_unescape_bytea($row['im_meta']);
		header('Content-Type: '.$row['mime_meta'].'');
		echo $file;
	}
}
?>
