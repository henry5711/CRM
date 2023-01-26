<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class CustomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \App\Services\AuthCognito::user()->hasRole('Admin') || \App\Services\AuthCognito::user()->can('api.v1.users.index.getIdentityVerificationRequests')|| \App\Services\AuthCognito::user()->hasRole('Soporte');
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

    public function prepareForValidation(){
        $this->merge([
            'pag' => $this->filled('pag')? $this->pag : 10
        ]);
    }
}
