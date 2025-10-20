<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use JasperPHP\JasperPHP as JasperPHP;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class jasperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function prueba()
    {

        // Crear el objeto JasperPHP
        $jasper = new JasperPHP();

        $output = base_path().'/resources/jasper/'.time().'_formato';
        $ext = "pdf";

        // Compilar el reporte para generar .jasper
        //$jasper->compile(base_path() . '/resources/jasper/hello_world.jrxml')->execute();

        $jasper->process(
            base_path() . '/resources/jasper/hello_world.jasper',
            $output,
            array($ext),
            array("php_version" => phpversion())
            /*array(
              'driver' => 'postgres',
              'host' => 'localhost',
              'username' => 'postgres',
              'password' => '1234',
              'database' => 'formulacion',
              'port' => '5432',
            )*/
        )->execute();

        // List the parameters from a Jasper file.
        /*$array = $jasper->list_parameters(
            base_path() . '/resources/jasper/hello_world.jasper'
        )->execute();*/

        // Depuración de errores
        // exec($jasper->output().' 2>&1', $output);
        // print_r($output);
        //print_r($array);

        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename='.time().'_formato.'.$ext);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . filesize($output.'.'.$ext));
        flush();
        readfile($output.'.'.$ext);
        unlink($output.'.'.$ext);

    }

}
