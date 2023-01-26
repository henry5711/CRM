<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriversRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (isLocalOrTesting()) {
            return true;
        } else {
            return \App\Services\AuthCognito::user()->hasRole('Admin') || \App\Services\AuthCognito::user()->can('users.updateAdm');
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'vehicle_model'        => 'nullable|integer|exists:vehicle_models,id',
            'vehicle_make'        => 'nullable|integer|exists:vehicle_brands,id',
            'vehicle_number'          => 'nullable|string',
            'vehicle_colour'          => 'nullable|string',
            'vehicle_type'            => 'nullable',
            'city'                    => 'nullable',
            'postal_code'             => 'nullable',
            'area'                    => 'nullable',
            'service'                 => 'nullable',
            'name'                    => 'nullable',
            'surname'                 => 'nullable',
            'date_birth'              => 'nullable',
            // 'identification_document' => 'nullable|file',
            'license'                 => 'nullable|file',
            'vehicle_photo'           => 'nullable|file',
            'vehicle_plate'           => 'nullable|file',
            'property_card'           => 'nullable|file',
        ];
    }
}
