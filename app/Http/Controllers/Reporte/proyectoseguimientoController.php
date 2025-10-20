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

class proyectoseguimientoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
      /**
       * Display a listing of the resource.
       *
       * @return \Illuminate\Http\Response
       */
      public function reporte()
      {
          return View::make('reporte.seguimiento.proyecto');
      }
}
