<?php

namespace matriz\Http\Controllers\Autenticacion;

//*******agregar esta linea******//
use Captcha;
use DB;
use View;
//*******************************//
use Illuminate\Http\Request;

use matriz\Http\Requests;
use matriz\Http\Controllers\Controller;

class externoController extends Controller
{
    public function captcha()
    {
        return Captcha::create('default');
    }
}
