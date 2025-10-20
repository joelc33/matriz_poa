<?php

namespace matriz\Http\Middleware;

//*******agregar esta linea******//
use matriz\Models\Autenticacion\tab_usuarios;
use matriz\Models\Autenticacion\tab_privilegio_menu;
use Auth;
use DB;
use Session;
//*******************************//
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class Poa
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        session_start();

        if($_SESSION['estatus']!='OK') {
            // Cerramos la sesión
            Auth::logout();
            Session::flush();

            return response('Sin Autorizacion.', 403);

        } else {

            if ($this->auth->check()) {

                return $next($request);

            } else {

                $data = tab_usuarios::join('mantenimiento.tab_funcionario as t29', 't29.id_tab_usuarios', '=', 'autenticacion.tab_usuarios.id')
                ->join('mantenimiento.tab_ejecutores as t24', 't24.id', '=', 't29.id_tab_ejecutores')
                ->join('autenticacion.tab_usuario_rol as t05', 't05.id_tab_usuarios', '=', 'autenticacion.tab_usuarios.id')
                ->select(
                    'autenticacion.tab_usuarios.id',
                    'id_ejecutor',
                    'id_tab_rol',
                    DB::raw('t24.id as co_ejecutores'),
                    'da_login',
                    'da_password'
                )
                ->where('autenticacion.tab_usuarios.id', '=', $_SESSION['co_usuario'])
                ->where('autenticacion.tab_usuarios.in_estatus', '=', true)
                ->first();

                if (tab_usuarios::where('da_login', '=', $data->da_login)->where('da_password', '=', $data->da_password)->exists()) {
                    $usuario = tab_usuarios::where('da_login', '=', $data->da_login)->where('da_password', '=', $data->da_password)->first();
                    Auth::login($usuario);
                }

                $credencial = tab_privilegio_menu::join('autenticacion.tab_privilegio as t01', 't01.id', '=', 'autenticacion.tab_privilegio_menu.id_tab_privilegio')
                ->join('autenticacion.tab_menu as t02', 't02.id', '=', 't01.id_tab_menu')
                ->join('autenticacion.tab_rol_menu as t03', 't03.id', '=', 'autenticacion.tab_privilegio_menu.id_tab_rol_menu')
                ->select('de_privilegio', DB::raw("autenticacion.tab_privilegio_menu.in_estatus as in_habilitado"))
                ->where('id_tab_rol', '=', $data->id_tab_rol)->get()->toArray();

                Session::put('usuario', $data->id);
                Session::put('rol', $data->id_tab_rol);
                Session::put('ejecutor', $data->id_ejecutor);
                Session::put('id_tab_ejecutores', $data->co_ejecutores);
                Session::put(array('credencial' => $credencial));

            }

        }

        return $next($request);
    }
}
