<?php

namespace matriz\Http\Controllers\Auxiliar;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_partidas;
use Validator;
use Input;
use Response;
use DB;
use Session;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class buscarController extends Controller
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
    public function partida()
    {
        $mensajes = array(
                  'partida.exists'=>'Codigo :attribute no encontrado en periodo fiscal '.Session::get('ejercicio').'.',
          );

        //$validator = Validator::make(Input::all(), tab_partidas::$validarBusqueda, $mensajes);
        $validator = Validator::make(Input::all(), tab_partidas::validarBusqueda(Session::get('ejercicio')), $mensajes);
        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'msg' => $validator->getMessageBag()->toArray()
            ));
        }

        $data = tab_partidas::select('co_partida', 'tx_nombre')
        ->where('co_partida', '=', Input::get('partida'))
        ->where('id_tab_ejercicio_fiscal', '=', Session::get('ejercicio'))
        ->first();

        return Response::json(array(
          'success' => true,
          'valido' => true,
          'data' => $data
        ));

    }

}
