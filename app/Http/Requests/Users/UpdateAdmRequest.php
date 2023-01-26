<?php

namespace App\Http\Requests\Users;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \App\Services\AuthCognito::user()->hasRole('Admin') || \App\Services\AuthCognito::user()->can('users.updateAdm');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'                          => 'nullable|string',
                'lastname'                  => 'nullable|string',
                'email'                     => 'nullable|email|unique:users,email,' . $this->id,
                'country_id'                => 'nullable|integer|exists:countries,id',
                'tp_document_id'            => 'nullable|integer|exists:tp_documents,id',
                'gender_id'                 => 'nullable|integer|exists:genders,id',
                'document'                  => 'nullable|string',
                'address'                   => 'nullable|string',
                'birth'                     => 'nullable|date_format:Y/m/d',
                'phone'                     => 'nullable|numeric',
                'phone_country_code'        => 'nullable|numeric|required_with:phone',
                'role_id'                   => 'nullable|integer|exists:roles,id',
        ];
    }
}
