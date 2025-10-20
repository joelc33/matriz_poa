<?php
session_start();
require_once 'configuracion/config.php';
$raiz = __DIR__;
if( $_SESSION['estatus'] !== 'OK' ) {
    http_response_code(403);
	die();
}
require_once $raiz . '/configuracion/ConexionComun.php';
require_once $raiz . '/vendor/autoload.php';

$comunes = new ConexionComun();
$usuario = (object) $_SESSION;

date_default_timezone_set('America/Caracas');

