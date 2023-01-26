<?php

namespace App\Http\Requests\Client;

use App\Traits\CustomResponseFormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    use CustomResponseFormRequestTrait;
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
            'name'                  => 'required|string',
            'email'                 => 'required|email|unique:clients',
            'country_id'            => 'required|integer|exists:countries,id',
            'phone'                 => 'required|numeric|unique:clients',
            'code_phone'    => 'nullable|numeric|required_with:phone',
            'document'                  => 'required|string|unique:clients',
        ];
    }
}
