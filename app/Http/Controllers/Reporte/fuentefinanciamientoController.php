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

class fuentefinanciamientoController extends Controller
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
        $data = json_encode(array("id_ejecutor" => Session::get('ejecutor')));
        return View::make('reporte.poa.fuentefinanciamiento')->with('data', $data);
    }
}
