<?php
require_once (__DIR__.'/../../../vendor/autoload.php');
Dotenv::load(__DIR__.'/../../../');

define( 'USUARIO', 'postgres' );
define( 'CLAVE', '1234' );
define( 'BASEDEDATOS', 'formulacion' );
define( 'SERVIDOR', 'localhost' );
define( 'PUERTO', '5432' );
define( 'GESTOR_DATABASE', 'postgres' );
define( 'DRIVER', 'org.postgresql.Driver' );
define( 'JDBC_TYPE', 'postgresql' );
define( 'JAVA_BRIDGE', 'http://localhost:8080/JavaBridge621' );
define( 'RAIZ_WEB', '/formulacion' );
