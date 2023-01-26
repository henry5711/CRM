<?php

namespace App\Http\Requests\Users;

use App\Traits\CustomResponseFormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'name'       => ['required', 'string'],
            'lastname'   => ['required', 'string'],
            'email'      => [
                'required',
                'email',
                Rule::unique('users')->whereNull('deleted_at')
            ],
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id'
            ],
            'password'   => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\^$*.\[\]{}\(\)?\-\"!@#%&\/,><\':;|_~`])\S{8,99}$/'
            ],
            'code_phone' => ['required', 'min:1', 'max:5' ],
            'phone'      => ['required', 'min:5', 'max:12']

        ];
    }
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $message = $validator->errors()->first();
        $response = custom_response_error(422, 'error validation', $message,422);
        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
    public function messages(){
        return [
            'password.regex' => trans('validation.password_regex' ),
        ];
    }
}
