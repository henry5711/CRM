<?php

namespace App\Http\Requests\Users;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIdentityVerificationImagesAdmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \App\Services\AuthCognito::user()->hasRole('Admin') || \App\Services\AuthCognito::user()->can('users.updateIdentityVerificationImagesAdm');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'frontal_status_id' => 'nullable|integer|exists:request_status,id',
            'back_status_id'    => 'nullable|integer|exists:request_status,id',
            'selfie_status_id'  => 'nullable|integer|exists:request_status,id',
            'address_status_id' => 'nullable|integer|exists:request_status,id',
        ];
    }
}
