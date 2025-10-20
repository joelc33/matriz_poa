<?php

namespace matriz\Http\Controllers\Reporte;

//*******agregar esta linea******//
use View;
use Validator;
use Input;
use Response;
use DB;
use Session;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class reporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {

        $iconos = "{ title: 'Libros ".Session::get('ejercicio')."', opciones: [
        {
          id: 'libro_ley',
          text: 'Ley de Presupuesto.',
          url: 'reporte/libro/ley',
          icon: 'zulia_escudo.png',
          desc: 'Libro de Ley de Presupuesto para el Ejercicio Fiscal ".Session::get('ejercicio')."',
          estatus: 'nuevo',
          iconCls: 'icon-pdf'
        },
        {
          id: 'libro_ley',
          text: 'Distribucion General de Presupuestos y Gastos.',
          url: 'reporte/libro/distribucion',
          icon: 'distribucion_presupuesto.jpg',
          desc: 'Libro de Distribucion General de Presupuestos y Gastos para el Ejercicio Fiscal ".Session::get('ejercicio')."',
          estatus: 'nuevo',
          iconCls: 'icon-pdf'
        },
      ]}";

        return View::make('reporte.libro.lista')->with('iconos', $iconos);
    }
}
