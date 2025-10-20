<?php

namespace matriz\Http\Requests;

use matriz\Http\Requests\Request;

class loginRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'usuario' => 'required', 'contraseña' => 'required',
        ];
    }
}
