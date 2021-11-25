<?php

namespace App\Providers\ResponseServiceProvider;

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class LoginUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    //Get the validation rules that apply to the request.

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',

        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }
}
