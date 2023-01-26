<?php

namespace App\Http\Requests\Users;

use App\Rules\VerifyCognitoUser;
use App\Traits\CustomResponseFormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
    /**
     * Rule para validar si usuario existe en cognito
     * Se comenta para la conexion con ebango
     * new VerifyCognitoUser()
     */
    public function rules()
    {
        return [
            'email'    => [
                    'required',
                    'email',
                    'exists:users'
                ],
            'password' => ['required'],
        ];
    }

}
