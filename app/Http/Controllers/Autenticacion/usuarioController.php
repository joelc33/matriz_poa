<?php

namespace matriz\Http\Controllers\Autenticacion;

//*******agregar esta linea******//
use matriz\Models\Autenticacion\tab_usuarios;
use matriz\Models\Mantenimiento\tab_funcionario;
use matriz\Models\Autenticacion\tab_rol;
use matriz\Models\Mantenimiento\tab_cargo;
use matriz\Models\Mantenimiento\tab_documento;
use matriz\Models\Autenticacion\tab_usuario_rol;
use matriz\Models\Mantenimiento\tab_ejecutores;
use View;
use Validator;
use Input;
use Response;
use DB;
use Auth;
use Crypt;
use Mail;
use Hash;
use Session;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class usuarioController extends Controller
{
    protected $tab_usuarios;

    public function __construct(tab_usuarios $tab_usuarios)
    {
        $this->middleware('auth');
        $this->tab_usuarios = $tab_usuarios;
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
    public function contrasena()
    {
        return View::make('autenticar.usuario.contrasena');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function cambioContrasena()
    {
        DB::beginTransaction();
        try {

            $clave_actual = tab_usuarios::find(Auth::user()->id);
            $valido = Hash::check(Input::get("contraseña_actual"), $clave_actual->da_password);

            $datos = array(
                "valido" => $valido,
                "contraseña_actual" => Input::get("contraseña_actual"),
                "contraseña" => Input::get("contraseña"),
                "contraseña_confirmation" => Input::get("contraseña_confirmation")
            );
            $mensajes = array(
                'valido.in'=>'La Contraseña ingresada no coincide.'
            );

            $validator = Validator::make($datos, tab_usuarios::$validarContrasena, $mensajes);
            if ($validator->fails()) {
                return Response::json(array(
                    'success' => false,
                    'msg' => $validator->getMessageBag()->toArray()
                ));
            }

            $usuario = tab_usuarios::find(Auth::user()->id);
            $usuario->da_password = bcrypt(Input::get("contraseña"));
            $usuario->da_pass_recuperar = Crypt::encrypt(Input::get("contraseña"));
            $usuario->save();

            //DB::commit();

            $cuentaUsr = tab_usuarios::findOrFail(Auth::user()->id);

            /*try{
                Mail::send('correo.cambioContrasena', ['usuario' => $cuentaUsr ], function ($message) use ($cuentaUsr) {
                    $message->to($cuentaUsr->da_email, $cuentaUsr->da_email)
                    ->subject('SPE - CAMBIO DE CONTRASEÑA');
                });

            }catch(\Exception $e){

                return Response::json(array(
                    'success' => false,
                    'msg' => array('ERROR ('.$e->getCode().'):'=> 'Error al enviar Correo Electronico. Intente mas tarde.')
                ));
            }*/

            DB::commit();

            return Response::json(array(
                'success' => true,
                'msg' => 'La Contraseña se cambio Satisfactoriamente!'
            ));

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return Response::json(array(
                'success' => false,
                'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
            ));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function datos()
    {
        $data = tab_usuarios::join('autenticacion.tab_usuario_rol as t01', 'autenticacion.tab_usuarios.id', '=', 't01.id_tab_usuarios')
        ->join('mantenimiento.tab_funcionario as t02', 'autenticacion.tab_usuarios.id', '=', 't02.id_tab_usuarios')
        ->select('da_login', 'da_email', 'id_tab_rol', 't02.id as id_funcionario', 'id_tab_documento', 'nu_cedula', 'nb_funcionario', 'ap_funcionario', 'id_tab_cargo', 'tx_direccion', 'tx_telefono')
        ->where('autenticacion.tab_usuarios.id', '=', Auth::user()->id)
        ->first();

        $ejecutor = tab_ejecutores::select('id', 'de_correo', 'de_telefono', 'in_verificado')
        ->where('id_ejecutor', '=', Session::get('ejecutor'))
        ->first();

        return View::make('autenticar.usuario.datos')->with('data', $data)->with('ejecutor', $ejecutor);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function cambios()
    {
        DB::beginTransaction();
        try {
            $validarFuncionario = Validator::make(Input::all(), tab_funcionario::$validarEditar);
            if ($validarFuncionario->fails()) {
                return Response::json(array(
                  'success' => false,
                  'msg' => $validarFuncionario->getMessageBag()->toArray()
                ));
            }
            $usuario = tab_usuarios::find(Auth::user()->id);
            $usuario->da_email = Input::get("correo_funcionario");
            $usuario->save();

            $usuario_funcionario = tab_funcionario::find(Input::get("id_funcionario"));
            $usuario_funcionario->id_tab_documento = Input::get("documenton");
            $usuario_funcionario->nu_cedula = Input::get("cedula");
            $usuario_funcionario->nb_funcionario = Input::get("nombre");
            $usuario_funcionario->ap_funcionario = Input::get("apellido");
            $usuario_funcionario->id_tab_cargo = Input::get("cargo");
            $usuario_funcionario->tx_direccion = Input::get("direccion");
            $usuario_funcionario->tx_telefono = Input::get("telefono_funcionario");
            $usuario_funcionario->tx_email = Input::get("correo_funcionario");
            $usuario_funcionario->save();

            $tabla = tab_ejecutores::updateOrCreate(array('id_ejecutor' => Session::get('ejecutor')));
            $tabla->de_correo = Input::get("correo");
            $tabla->de_telefono = Input::get("telefono");
            $tabla->in_verificado = true;
            $tabla->save();

            DB::commit();
            return Response::json(array(
              'success' => true,
              'msg' => 'Datos Editados con Exito!'
            ));

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return Response::json(array(
              'success' => false,
              'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
            ));
        }
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function lista()
    {
        return View::make('autenticar.usuario.lista');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function storeLista()
    {
        try {
            $start  = Input::get('start', 0);
            $limit  = Input::get('limit', 20);
            $variable = Input::get('variable');

            $tab_usuarios = $this->tab_usuarios
            ->join('mantenimiento.tab_funcionario as t01', 't01.id_tab_usuarios', '=', 'autenticacion.tab_usuarios.id')
            ->join('mantenimiento.tab_ejecutores as t02', 't02.id', '=', 't01.id_tab_ejecutores')
            ->join('autenticacion.tab_usuario_rol as t03', 't03.id_tab_usuarios', '=', 'autenticacion.tab_usuarios.id')
            ->join('autenticacion.tab_rol as t04', 't04.id', '=', 't03.id_tab_rol')
            ->join('mantenimiento.tab_documento as t05', 't05.id', '=', 't01.id_tab_documento')
            ->select(
                'autenticacion.tab_usuarios.id',
                'da_login',
                DB::raw("inicial||'-'||nu_cedula as nu_cedula"),
                'nb_funcionario',
                'ap_funcionario',
                'autenticacion.tab_usuarios.in_estatus',
                'de_rol',
                'tx_ejecutor'
            );

            if (Input::get("BuscarBy")=="true") {

                if($variable!="") {
                    $tab_usuarios->where('da_login', 'ILIKE', "%$variable%")
                        ->orWhere('nb_funcionario', 'ILIKE', "%$variable%")
                        ->orWhere('ap_funcionario', 'ILIKE', "%$variable%")
                        ->orWhere('tx_ejecutor', 'ILIKE', "%$variable%")
                        ->orWhere(DB::raw('nu_cedula::text'), 'ILIKE', "%$variable%");
                }

                $response['success']  = 'true';
                $response['total'] = $tab_usuarios->count();
                $tab_usuarios->skip($start)->take($limit);
                $response['data']  = $tab_usuarios->orderby('id', 'ASC')->get()->toArray();
            } else {
                $response['success']  = 'true';
                $response['total'] = $tab_usuarios->count();
                $tab_usuarios->skip($start)->take($limit);
                $response['data']  = $tab_usuarios->orderby('id', 'ASC')->get()->toArray();
            }

            return Response::json($response, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::json(array('success' => false, 'message' => utf8_encode($e->getMessage())), 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function nuevo()
    {
        $data = json_encode(array("id" => ""));
        return View::make('autenticar.usuario.editar')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function editar($id)
    {
        $data = tab_usuarios::join('autenticacion.tab_usuario_rol as t01', 'autenticacion.tab_usuarios.id', '=', 't01.id_tab_usuarios')
        ->join('mantenimiento.tab_funcionario as t02', 'autenticacion.tab_usuarios.id', '=', 't02.id_tab_usuarios')
        ->select(
            'autenticacion.tab_usuarios.id',
            'da_login',
            'da_email',
            'id_tab_rol',
            't02.id as id_funcionario',
            'id_tab_documento',
            'nu_cedula',
            'nb_funcionario',
            'ap_funcionario',
            'id_tab_cargo',
            'tx_direccion',
            'tx_telefono',
            'id_tab_ejecutores'
        )
        ->where('autenticacion.tab_usuarios.id', '=', $id)
        ->first();
        return View::make('autenticar.usuario.editar')->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function guardar($id = null)
    {
        DB::beginTransaction();
        if($id!=''||$id!=null) {

            try {
                $validarUsuario = Validator::make(Input::all(), tab_usuarios::$validarEditar);
                $validarFuncionario = Validator::make(Input::all(), tab_funcionario::$validarEditarFuncionario);
                if ($validarUsuario->fails() || $validarFuncionario->fails()) {

                    $validacion = array_merge_recursive(
                        $validarUsuario->getMessageBag()->toArray(),
                        $validarFuncionario->getMessageBag()->toArray()
                    );

                    return Response::json(array(
                      'success' => false,
                      'msg' => $validacion
                    ));
                }
                $usuario = tab_usuarios::find($id);
                $usuario->da_login = Input::get("usuario");
                $usuario->da_email = Input::get("correo_funcionario");
                $usuario->save();

                $usuario_rol = tab_usuario_rol::find($id);
                $usuario_rol->id_tab_rol = Input::get("rol");
                $usuario_rol->save();

                $usuario_funcionario = tab_funcionario::find(Input::get("id_funcionario"));
                $usuario_funcionario->id_tab_documento = Input::get("documenton");
                $usuario_funcionario->nu_cedula = Input::get("cedula");
                $usuario_funcionario->nb_funcionario = self::acomodar(Input::get("nombre"));
                $usuario_funcionario->ap_funcionario = self::acomodar(Input::get("apellido"));
                $usuario_funcionario->id_tab_cargo = Input::get("cargo");
                $usuario_funcionario->id_tab_ejecutores = Input::get("ejecutor");
                $usuario_funcionario->tx_direccion = Input::get("direccion");
                $usuario_funcionario->tx_telefono = Input::get("telefono_funcionario");
                $usuario_funcionario->tx_email = Input::get("correo_funcionario");
                $usuario_funcionario->save();

                DB::commit();
                return Response::json(array(
                  'success' => true,
                  'msg' => 'Usuario Editado con Exito!'
                ));

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollback();
                return Response::json(array(
                  'success' => false,
                  'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
                ));
            }

        } else {

            try {
                $validarUsuario = Validator::make(Input::all(), tab_usuarios::$validarCrear);
                $validarFuncionario = Validator::make(Input::all(), tab_funcionario::$validarCrear);
                if ($validarUsuario->fails() || $validarFuncionario->fails()) {

                    $validacion = array_merge_recursive(
                        $validarUsuario->getMessageBag()->toArray(),
                        $validarFuncionario->getMessageBag()->toArray()
                    );

                    return Response::json(array(
                      'success' => false,
                      'msg' => $validacion
                    ));
                }

                $clave = self::genera_clave();

                $usuario = new tab_usuarios();
                $usuario->da_login = Input::get("usuario");
                $usuario->da_email = Input::get("correo_funcionario");
                $usuario->da_password = bcrypt(123456);
                $usuario->da_pass_recuperar = Crypt::encrypt(123456);
                $usuario->in_estatus = 'TRUE';
                $usuario->save();

                $usuario_rol = new tab_usuario_rol();
                $usuario_rol->id_tab_usuarios = $usuario->id;
                $usuario_rol->id_tab_rol = Input::get("rol");
                $usuario_rol->in_estatus = 'TRUE';
                $usuario_rol->save();

                $usuario_funcionario = new tab_funcionario();
                $usuario_funcionario->id_tab_usuarios = $usuario->id;
                $usuario_funcionario->id_tab_documento = Input::get("documenton");
                $usuario_funcionario->nu_cedula = Input::get("cedula");
                $usuario_funcionario->nb_funcionario = self::acomodar(Input::get("nombre"));
                $usuario_funcionario->ap_funcionario = self::acomodar(Input::get("apellido"));
                $usuario_funcionario->id_tab_cargo = Input::get("cargo");
                $usuario_funcionario->id_tab_ejecutores = Input::get("ejecutor");
                $usuario_funcionario->tx_direccion = Input::get("direccion");
                $usuario_funcionario->tx_telefono = Input::get("telefono_funcionario");
                $usuario_funcionario->tx_email = strtolower(Input::get("correo_funcionario"));
                $usuario_funcionario->in_activo = 'TRUE';
                $usuario_funcionario->save();

                $cuentaUsr = tab_usuarios::findOrFail($usuario->id);

                /*try{
                  Mail::send('correo.usuarioAdministrativo', ['usuario' => $cuentaUsr ], function ($message) use ($cuentaUsr) {
                    $message->to($cuentaUsr->da_email, $cuentaUsr->da_email)
                    ->subject('SPE - CREACION DE USUARIO ADMINISTRATIVO');
                  });

                }catch(\Exception $e){

                  return Response::json(array(
                    'success' => false,
                    'msg' => array('ERROR ('.$e->getCode().'):'=> 'Error al enviar Correo Electronico. Intente mas tarde.')
                  ));
                }*/

                DB::commit();

                return Response::json(array(
                  'success' => true,
                  'msg' => 'Usuario Guardado con Exito!'
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

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function deshabilitar()
    {
        DB::beginTransaction();
        try {
            $usuario = tab_usuarios::find(Input::get("id"));
            $usuario->in_estatus = 'FALSE';
            $usuario->save();
            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Usuario Deshabilitado con Exito!';
            return Response::json($response, 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function habilitar()
    {
        DB::beginTransaction();
        try {
            $usuario = tab_usuarios::find(Input::get("id"));
            $usuario->in_estatus = 'TRUE';
            $usuario->save();
            DB::commit();

            $response['success']  = 'true';
            $response['msg']  = 'Usuario Habilitado con Exito!';
            return Response::json($response, 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function resetear()
    {
        DB::beginTransaction();
        try {
            $clave = self::genera_clave();

            $usuario = tab_usuarios::find(Input::get("id"));
            $usuario->da_password = bcrypt($clave);
            $usuario->da_pass_recuperar = Crypt::encrypt($clave);
            $usuario->save();
            //DB::commit();

            $cuentaUsr = tab_usuarios::findOrFail(Input::get("id"));

            try {
                Mail::send('correo.usuario.contrasena', ['usuario' => $cuentaUsr ], function ($message) use ($cuentaUsr) {
                    $message->to($cuentaUsr->da_email, $cuentaUsr->da_email)
                    ->subject('SPE - CAMBIO DE CONTRASEÑA');
                });

            } catch(\Exception $e) {

                return Response::json(array(
                  'success' => false,
                  //'msg' => 'Error al enviar Correo Electronico. Intente mas tarde.',
                  'msg' => array('ERROR ('.$e->getCode().'):'=> $e->getMessage())
                ));
            }

              DB::commit();

            return Response::json(array(
              'success' => true,
              'msg' => 'La Contraseña se cambio Satisfactoriamente!'
            ));

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();

            $response['success']  = 'false';
            $response['msg']  = array('ERROR ('.$e->getCode().'):'=> $e->getMessage());
            return Response::json($response, 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function cambioClave($id)
    {
        $data = tab_usuarios::select('id', 'da_login')
        ->where('id', '=', $id)
        ->first();
        return View::make('autenticar.usuario.resetear')->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function guardarCambioClave()
    {
        DB::beginTransaction();
        try {

            $datos = array(
              "contraseña" => Input::get("contraseña"),
              "contraseña_confirmation" => Input::get("contraseña_confirmation")
            );

            $validator = Validator::make($datos, tab_usuarios::$validarReseteo);
            if ($validator->fails()) {
                return Response::json(array(
                  'success' => false,
                  'msg' => $validator->getMessageBag()->toArray()
                ));
            }

            $usuario = tab_usuarios::find(Input::get("codigo"));
            $usuario->da_password = bcrypt(Input::get("contraseña"));
            $usuario->da_pass_recuperar = Crypt::encrypt(Input::get("contraseña"));
            $usuario->save();

            DB::commit();

            return Response::json(array(
              'success' => true,
              'msg' => 'La Contraseña se cambio Satisfactoriamente!'
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
