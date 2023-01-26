<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class IndexRolUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \App\Services\AuthCognito::user()->hasRole('Admin') || \App\Services\AuthCognito::user()->can('users.list') || \App\Services\AuthCognito::user()->hasRole('Soporte') || \App\Services\AuthCognito::user()->hasRole('Agente/Moderador')|| \App\Services\AuthCognito::user()->hasRole('Agente/Coorporativo');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'role_id ' => 'nullable|numeric|exists:roles,id',
        ];
    }
}
