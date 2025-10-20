<?php

namespace matriz\Models\Autenticacion;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class tab_usuarios extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;

    //Nombre de la conexion que utitlizara este modelo
    protected $connection= 'local';

    public function getAuthPassword()
    {
        return $this->da_password;
    }

    //Todos los modelos deben extender la clase Eloquent
    protected $table = 'autenticacion.tab_usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['da_email', 'da_login', 'da_password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public static $validarContrasena = array(
      "valido" => "required|in:1",
      "contraseña_actual" => "required|alpha_dash|min:6|max:30",
      "contraseña" => "required|alpha_dash|min:6|max:30|confirmed",
      "contraseña_confirmation" => "required|alpha_dash|min:6|max:30"
    );

    public static $validarReseteo = array(
      "contraseña" => "required|alpha_dash|min:6|max:30|confirmed",
      "contraseña_confirmation" => "required|alpha_dash|min:6|max:30"
    );

    public static $validarCrear = array(
      "usuario"    => "required|alpha_dash|min:5|max:30|unique:local.autenticacion.tab_usuarios,da_login",
      "correo_funcionario"    => "required|email|unique:local.autenticacion.tab_usuarios,da_email",
      //"contraseña" => "required|alpha_dash|min:6|max:30",
      "rol"    => "required|integer"
    );

    public static $validarEditar = array(
      "usuario"    => "required|alpha_dash|min:5|max:30",
      "correo_funcionario"    => "required|email",
      //"contraseña" => "required|alpha_dash|min:6|max:30",
      "rol"    => "required|integer"
    );

    public static $validarCorreo = array(
      'usuario' => 'required|alpha_dash|min:5|max:30|exists:local.autenticacion.tab_usuarios,da_login',
      'correo' => 'required|email|exists:local.autenticacion.tab_usuarios,da_email'
    );
}
