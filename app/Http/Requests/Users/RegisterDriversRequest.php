<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDriversRequest extends FormRequest
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

            'vehicle_model'        => 'required|integer|exists:vehicle_models,id',
            'vehicle_make'        => 'required|integer|exists:vehicle_brands,id',
            'vehicle_number'          => 'required|string',
            'vehicle_colour'          => 'required|string',
            'vehicle_type'            => 'required',
            'city'                    => 'required',
            'postal_code'             => 'required',
            'area'                    => 'required',
            'service'                 => 'required',
            'name'                    => 'required',
            'surname'                 => 'required',
            'date_birth'              => 'required',
            'license'                 => 'required|file',
            'vehicle_photo'           => 'required|file',
            'vehicle_plate'           => 'required|file',
            'property_card'           => 'required|file',

        ];
    }

}
