<?php

namespace matriz\Http\Controllers\Autenticacion;

//*******agregar esta linea******//
use matriz\Models\Mantenimiento\tab_ejercicio_fiscal;
use matriz\Models\Mantenimiento\tab_ejecutores;
use matriz\Models\Autenticacion\tab_usuarios;
use matriz\Models\Mantenimiento\tab_funcionario;
use Session;
use Response;
use Validator;
use DB;
use View;
use URL;
use Input;
use Auth;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class ejercicioController extends Controller
{
    public function __construct()
    {
        $this->middleware('optimizar');
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function lista()
    {
        $data = tab_ejecutores::select('id', 'de_correo', 'de_telefono', 'in_verificado')
        ->where('id_ejecutor', '=', Session::get('ejecutor'))
        ->first();

        $funcionario = tab_usuarios::join('autenticacion.tab_usuario_rol as t01', 'autenticacion.tab_usuarios.id', '=', 't01.id_tab_usuarios')
        ->join('mantenimiento.tab_funcionario as t02', 'autenticacion.tab_usuarios.id', '=', 't02.id_tab_usuarios')
        ->select('t02.id as id_funcionario', 'id_tab_documento', 'nu_cedula', 'nb_funcionario', 'ap_funcionario', 'tx_email', 'tx_direccion', 'tx_telefono')
        ->where('autenticacion.tab_usuarios.id', '=', Auth::user()->id)
        ->first();

        return View::make('autenticar.ejercicio.form')->with('data', $data)->with('funcionario', $funcionario);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function ejercicio()
    {
        $response['success']  = 'true';
        $response['data']  = tab_ejercicio_fiscal::select(
            'id',
            'in_activo',
            DB::raw('mantenimiento.sp_periodo_activo(id::integer) as de_estatus')
        )->orderby('id', 'ASC')->get()->toArray();
        return Response::json($response, 200);
    }

    public function acomodar($string)
    {
        $string =ucwords(strtolower($string));

        foreach (array('-', '\'') as $delimiter) {
            if (strpos($string, $delimiter)!==false) {
                $string =implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
            }
        }
        return $string;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function seleccionar()
    {

        $data = tab_ejecutores::select('id', 'de_correo', 'de_telefono', 'in_verificado')
        ->where('id_ejecutor', '=', Session::get('ejecutor'))
        ->first();

        if($data->in_verificado==true) {

            $validator= Validator::make(Input::all(), tab_ejercicio_fiscal::$seleccionar);
            if ($validator->fails()) {
                return Response::json(array(
                  'success' => false,
                  'msg' => $validator->getMessageBag()->toArray()
                ));
            }

            Session::put('ejercicio', Input::get('ejercicio'));

            /*Uso para poa*/
            //ini_set('session.save_path',realpath(dirname(storage_path()) . '/formulacion'));
            ini_set('session.gc_maxlifetime', 3600);
            // each client should remember their session id for EXACTLY 1 hour
            session_set_cookie_params(3600);
            session_start();
            $_SESSION['ejercicio_fiscal']=Input::get('ejercicio');
            session_write_close();
            /*fin*/

            return Response::json(array(
              'success' => true,
              'msg' => 'Ejercicio Seleccionado!',
              'url' => URL::to('inicio')
            ));

        } elseif($data->in_verificado==false) {

            DB::beginTransaction();
            try {

                $validator= Validator::make(Input::all(), tab_ejecutores::$datosEjecutor);
                if ($validator->fails()) {
                    return Response::json(array(
                      'success' => false,
                      'msg' => $validator->getMessageBag()->toArray()
                    ));
                }

                $tabla = tab_ejecutores::updateOrCreate(array('id_ejecutor' => Session::get('ejecutor')));
                $tabla->de_correo = Input::get("correo");
                $tabla->de_telefono = Input::get("telefono");
                $tabla->in_verificado = true;
                $tabla->save();

                $usuario_funcionario = tab_funcionario::find(Input::get("id_funcionario"));
                $usuario_funcionario->id_tab_documento = Input::get("documenton");
                $usuario_funcionario->nu_cedula = Input::get("cedula");
                $usuario_funcionario->nb_funcionario = self::acomodar(Input::get("nombre"));
                $usuario_funcionario->ap_funcionario = self::acomodar(Input::get("apellido"));
                $usuario_funcionario->tx_telefono = Input::get("telefono_funcionario");
                $usuario_funcionario->tx_email = strtolower(Input::get("correo_funcionario"));
                $usuario_funcionario->save();

                $usuario = tab_usuarios::find(Auth::user()->id);
                $usuario->da_email = strtolower(Input::get("correo_funcionario"));
                $usuario->save();

                Session::put('ejercicio', Input::get('ejercicio'));

                DB::commit();

                return Response::json(array(
                  'success' => true,
                  'msg' => 'Ejercicio Seleccionado!',
                  'url' => URL::to('inicio')
                ));

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollback();
                return Response::json(array(
                  'success' => false,
                  'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
                ));
            }

        }

    }

}
