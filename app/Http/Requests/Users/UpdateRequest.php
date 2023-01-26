<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRequest extends FormRequest
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
            'name'                        => 'nullable|string',
            'lastname'                    => 'nullable|string',
            'email'                       => 'nullable|email|unique:users,email,' . $this->id,
            'new_password'                => 'nullable|min:8|required_with:new_password_confirmation|same:new_password_confirmation|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\^$*.\[\]{}\(\)?\-\"!@#%&\/,><\':;|_~`])\S{8,99}$/',
            'new_password_confirmation'   => 'nullable|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\^$*.\[\]{}\(\)?\-\"!@#%&\/,><\':;|_~`])\S{8,99}$/',
            'country_id'                  => 'nullable|integer|exists:countries,id',
            'tp_document_id'              => 'nullable|integer|exists:tp_documents,id',
            'gender_id'                   => 'nullable|integer|exists:genders,id',
            'profile_background_image_id' => 'nullable|integer|exists:images,id',
            'document'                    => 'nullable|string|unique:user_details,document,' . $this->id,
            'address'                     => 'nullable|string',
            'birth'                       => 'nullable|date_format:Y/m/d',
            'phone'                       => 'nullable|numeric',
            'code_phone'                  => 'nullable|numeric|required_with:phone',
        ];
    }
    public function messeges(){
        return [
            'new_password.regex' => trans('validation.password_regex' ),
            'new_password_confirmation.regex' => trans('validation.password_regex' )
        ];
    }

    public function prepareForValidation(){
        //TEST
        if(isLocalOrTesting()){
            $this->merge([
                'id' => \App\Services\AuthCognito::userTesting($this->header("Authorization"))->id
            ]);
        }else{
            $this->merge([
                'id' => \App\Services\AuthCognito::user()->id
            ]);
        }
    }
}
