<?php

namespace matriz\Http\Controllers\Autenticacion;

//*******agregar esta linea******//
use matriz\Models\Auditoria\tab_login_acceso;
use matriz\Models\Autenticacion\tab_usuarios;
use matriz\Models\Autenticacion\tab_privilegio_menu;
use Auth;
use View;
use Redirect;
use Session;
use Captcha;
use Response;
use Validator;
use URL;
use DB;
use Input;
use Crypt;
use Mail;
//*******************************//
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;
use matriz\Http\Requests\loginRequest;

class autenticarController extends Controller
{
    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->middleware('optimizar');
        $this->middleware('guest', ['except' => 'salir']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Verificamos si hay sesión activa
        if (Auth::check()) {
            // Si tenemos sesión activa mostrará la página de inicio
            return Redirect::to('/');
        }
        // Si no hay sesión activa mostramos el formulario
        return View::make('autenticar.login.form');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function validar(loginRequest $request)
    {
        $regla =  array('captcha' => ['required', 'captcha']);
        $validator = Validator::make(['captcha' => $request->codigoseg], $regla);
        //if ($validator->passes()) 
        if(1==1)
        {
            if ($this->auth->attempt(['da_login' => $request->usuario,
              'password' => $request->contraseña,
              'in_estatus' => 1])) {
                $usuario_login = new tab_login_acceso();
                $usuario_login->id_tab_usuarios = Auth::user()->id;
                $usuario_login->id_tap_tipo_accion = 1;
                $usuario_login->de_login_accion = 'Acceso al Sistema';
                $usuario_login->ip_cliente = $_SERVER['REMOTE_ADDR'];
                $usuario_login->save();

                $data = tab_usuarios::join('mantenimiento.tab_funcionario as t29', 't29.id_tab_usuarios', '=', 'autenticacion.tab_usuarios.id')
                ->join('mantenimiento.tab_ejecutores as t24', 't24.id', '=', 't29.id_tab_ejecutores')
                ->join('autenticacion.tab_usuario_rol as t05', 't05.id_tab_usuarios', '=', 'autenticacion.tab_usuarios.id')
                ->select(
                    'autenticacion.tab_usuarios.id',
                    'id_ejecutor',
                    'id_tab_rol',
                    DB::raw('t29.id_tab_ejecutores'),
                    'da_login',
                    'da_password'
                )
                ->where('autenticacion.tab_usuarios.id', '=', Auth::user()->id)
                ->where('autenticacion.tab_usuarios.in_estatus', '=', true)
                ->first();

                $credencial = tab_privilegio_menu::join('autenticacion.tab_privilegio as t01', 't01.id', '=', 'autenticacion.tab_privilegio_menu.id_tab_privilegio')
                ->join('autenticacion.tab_menu as t02', 't02.id', '=', 't01.id_tab_menu')
                ->join('autenticacion.tab_rol_menu as t03', 't03.id', '=', 'autenticacion.tab_privilegio_menu.id_tab_rol_menu')
                ->select('de_privilegio', DB::raw("autenticacion.tab_privilegio_menu.in_estatus as in_habilitado"))
                ->where('id_tab_rol', '=', $data->id_tab_rol)->get()->toArray();

                Session::put('usuario', $data->id);
                Session::put('rol', $data->id_tab_rol);
                Session::put('ejecutor', $data->id_ejecutor);
                Session::put('id_tab_ejecutores', $data->id_tab_ejecutores);
                Session::put(array('credencial' => $credencial));

                /*Uso para poa*/
                //ini_set('session.save_path',realpath(dirname(storage_path()) . '/formulacion'));
                // server should keep session data for AT LEAST 1 hour
                ini_set('session.gc_maxlifetime', 3600);
                // each client should remember their session id for EXACTLY 1 hour
                session_set_cookie_params(3600);
                session_start();
                $_SESSION['estatus'] = 'OK';
                $_SESSION['co_usuario'] = $data->id;
                $_SESSION['co_rol'] = $data->id_tab_rol;
                $_SESSION['id_ejecutor'] = $data->id_ejecutor;
                $_SESSION['co_ejecutores'] = $data->id_tab_ejecutores;

                $_SESSION['spe_session']=array();
                array_push($_SESSION['spe_session'], array( 'estatus' => 'OK', 'co_usuario' => $data->id, 'co_rol' => $data->id_tab_rol, 'id_ejecutor' => $data->id_ejecutor, 'co_ejecutores' => $data->id_tab_ejecutores, $credencial));
                session_write_close();
                /*fin*/

                return Response::json(array(
                  'success' => true,
                  'msg' => 'Usuario Validado!',
                  'url' => URL::to('ejercicio')
                ));
            } else {
                return Response::json(array(
                  'success' => false,
                  'msg' => 'Las credenciales que has introducido no coinciden con nuestros registros. Intente de Nuevo'
                ));
            }
        } else {
            return Response::json(array(
              'success' => false,
              'msg' => 'El captcha ingresado es incorrecto.'
            ));
        }
    }

    public function salir()
    {
        // Cerramos la sesión
        Auth::logout();
        Session::flush();

        /*Uso para poa*/
        session_start();
        $_SESSION['estatus']='Off';
        session_write_close();
        /*fin*/
        // redirect
        return Redirect::to('/');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    protected function genera_clave()
    {
        $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $longitudCadena=strlen($cadena);
        $pass = "";
        $longitudPass=8;
        for($i=1 ; $i<=$longitudPass ; $i++) {
            $pos=rand(0, $longitudCadena-1);
            $pass .= substr($cadena, $pos, 1);
        }
        return $pass;
    }

    public function recuperar()
    {

        $mensajes = array(
            'usuario.exists'=>'El usuario que has introducido no coinciden con nuestros registros. Intente de nuevo!.',
            'correo.exists'=>'El correo que has introducido no coinciden con nuestros registros. Intente de nuevo!.',
        );

        $validator = Validator::make(Input::all(), tab_usuarios::$validarCorreo, $mensajes);
        if ($validator->fails()) {
            return Response::json(array(
              'success' => false,
              'msg' => $validator->getMessageBag()->toArray()
            ));
        }

        DB::beginTransaction();
        try {
            $clave = self::genera_clave();

            $usuario = tab_usuarios::updateOrCreate(array('da_login' => Input::get("usuario"), 'da_email' => Input::get("correo")));
            //$usuario = tab_usuarios::find(Input::get("id"));
            $usuario->da_password = bcrypt($clave);
            $usuario->da_pass_recuperar = Crypt::encrypt($clave);
            $usuario->save();

            //$cuentaUsr = tab_usuarios::findOrFail($usuario->id);

            $cuentaUsr = tab_usuarios::select('da_login', 'da_email', 'da_pass_recuperar')
            ->where('da_login', '=', Input::get("usuario"))
            ->where('da_email', '=', Input::get("correo"))
            ->first();

            try {
                Mail::send('correo.usuario.contrasena', ['usuario' => $cuentaUsr ], function ($message) use ($cuentaUsr) {
                    $message->to($cuentaUsr->da_email, $cuentaUsr->da_email)
                    ->subject('SPE - RECUPERAR CONTRASEÑA');
                });

            } catch(\Exception $e) {

                return Response::json(array(
                  'success' => false,
                  'msg' => array('msg' => 'Error al enviar Correo Electronico. Intente mas tarde.'),
                  //'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
                ));
            }

              DB::commit();

            return Response::json(array(
              'success' => true,
              'msg' => 'Correo enviado.'
            ));

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            //$response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> 'Error en transaccion. Intente mas tarde.');
            return Response::json($response, 200);
        }

    }

}
