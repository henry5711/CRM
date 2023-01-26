<?php

namespace App\Http\Requests\Images;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \App\Services\AuthCognito::user()->hasRole('Admin') || \App\Services\AuthCognito::user()->can('images.registerImage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //|image|mimes:jpeg,png,jpg,gif,svg|max:8192
        return [
            'file'              => 'nullable',
            'idSystemImageType' => 'nullable|integer|exists:system_image_types,id',
            'name'              =>'nullable|string|max:255|unique:images,name,'.$this->id,
        ];
    }
}
