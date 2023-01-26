<?php

namespace App\Http\Requests\Images;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class IndexSystemImageTypeRequest extends FormRequest
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
            'idTypeImage' => 'nullable|integer|exists:system_image_types,id',
        ];
    }

    public function prepareForValidation(){
        $this->merge([
            'pag' => $this->filled('pag')? $this->pag : 10
        ]);
    }
}
