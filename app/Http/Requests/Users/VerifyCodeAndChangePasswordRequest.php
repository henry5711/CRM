<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class VerifyCodeAndChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'email'    => 'required|email|exists:users',
            'code'    => 'required',
            'password'   => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\^$*.\[\]{}\(\)?\-\"!@#%&\/,><\':;|_~`])\S{8,99}$/'
            ],

        ];
    }
    public function messages(){
        return [
            'password.regex' => trans('validation.password_regex' )
        ];
    }
}
