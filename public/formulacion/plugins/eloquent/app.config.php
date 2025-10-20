<?php
require_once(__DIR__.'/../../configuracion/config.php');
/**Adapcion de ORM Eloquent y validator Stand Alone por Yoser Perez**/
require_once(__DIR__.'/vendor/autoload.php');

//Importamos el archivo autoload.php presente en nuestro directorio vendor require 'vendor/autoload.php';
$container = new Illuminate\Container\Container;
$dispatcher = new Illuminate\Events\Dispatcher;
$router = new Illuminate\Routing\Router($dispatcher);

//Después creamos archivos relativos para la confgiguracion principal
$container['path'] = __DIR__;
$container['path.lang'] = __DIR__.'/lang';

//Configuracion de los paquetes
$container['config'] = array(
    'app.locale'          => 'es',
    'app.fallback_locale' => 'es',

    'database.default' => 'principal',
    'database.fetch' => PDO::FETCH_CLASS,
    'database.connections' => array(

        'principal' => array(
            'driver'   => 'pgsql',
            'host'     => SERVIDOR,
	    'port'     => PUERTO,
            'database' => BASEDEDATOS,
            'username' => USUARIO,
            'password' => CLAVE,
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => array('public','auditoria','autenticacion','mantenimiento'),
        )
    ),
);

// Arreglo de providers
$providers = array(
    'Illuminate\Events\EventServiceProvider',
    'Illuminate\Filesystem\FilesystemServiceProvider',
    'Illuminate\Database\DatabaseServiceProvider',
    'Illuminate\Translation\TranslationServiceProvider',
    'Illuminate\Validation\ValidationServiceProvider'
);

// Registro de todos los providers
$registered = array();
foreach ($providers as $provider)
{
    $instance = new $provider($container);
    $instance->register();
    $registered[] = $instance;
}

//Arranque de las instancias
foreach ($registered as $instance)
{
    $instance->boot();
}

//Creacion de nuestro abstract facade
abstract class Facade {

    protected static $key;
    protected static $container;

    public static function setContainer($container)
    {
        static::$container = $container;
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::$container[static::$key];

        switch (count($args))
        {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array(array($instance, $method), $args);
        }
    }
}

//Seleccionas nuestro container de facade class
Facade::setContainer($container);

//Nuestras Clases Adstractas
class DB extends Facade { protected static $key = 'db'; }
class Lang extends Facade { protected static $key = 'translator'; }
class Validator extends Facade { protected static $key = 'validator'; }
