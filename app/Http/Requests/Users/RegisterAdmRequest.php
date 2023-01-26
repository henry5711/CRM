<?php

namespace App\Http\Requests\Users;

use App\Traits\CustomResponseFormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RegisterAdmRequest extends FormRequest
{
    use CustomResponseFormRequestTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return \App\Services\AuthCognito::user()->hasRole('Admin');
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
            'name'                  => 'required|string',
            'lastname'              => 'required|string',
            'email'                 => 'required|email|unique:users',
            'country_id'            => 'required|integer|exists:countries,id',
            'password'   => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\^$*.\[\]{}\(\)?\-\"!@#%&\/,><\':;|_~`])\S{8,99}$/'
            ],
            'role_id'               => 'required|integer|exists:roles,id',
            'phone'                 => 'nullable|numeric',
            'phone_country_code'    => 'nullable|numeric|required_with:phone',
        ];
    }
    public function messages(){
        return [
            'password.regex' => trans('validation.password_regex' )
        ];
    }
}
