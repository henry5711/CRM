<?php

namespace App\Http\Requests\Users;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \App\Services\AuthCognito::user()->hasRole('Admin') || \App\Services\AuthCognito::user()->can('users.detailed')  || \App\Services\AuthCognito::user()->hasRole('Soporte') || \App\Services\AuthCognito::user()->hasRole('Agente/Moderador')|| \App\Services\AuthCognito::user()->hasRole('Agente/Coorporativo');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
