<?php

namespace App\Http\Requests\Users;

use App\Traits\CustomResponseFormRequestTrait;
use App\Enums\TypeDocumentEnum;
use App\Models\TpDocument;
use App\Services\AuthCognito;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\RequiredIf;

class RegisterIdentifyVerificationRequest extends FormRequest
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
        if(isLocalOrTesting()){
            $user = AuthCognito::userTesting($this->header('Authorization'));
        }else{
            $user = AuthCognito::user();
        }
        $bool = $user->userDetail->identityVerification;
        if($bool && $bool->status_general !== 3){
            return [];
        }

        $backFileBool = $this->type_document_name == TypeDocumentEnum::get('DNI');
        return [
            'country_id'                => 'required|integer|exists:countries,id',
            'document_number'           => 'required|string',
            'direction'                 => 'nullable|string',
            'document_type_id'          => 'required|integer|exists:tp_documents,id',
            'front_identity_document'   => 'required|image|mimes:jpeg,png,jpg|max:8192',
            'selfie_identity_document'  => 'required|image|mimes:jpeg,png,jpg|max:8192',
            'reverse_identity_document' => [
                new RequiredIf($backFileBool),
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:8192'
            ],
            'city'                      => 'required|string'
        ];
    }

    protected function prepareForValidation()
{
    if(gettype($this->reverse_identity_document) == 'string'){
        $val = '';
    }else{
        $val = $this->reverse_identity_document;
    }

    $this->merge([
        'type_document_name' => TpDocument::find($this->document_type_id)->tp_document_name,
        'reverse_identity_document' => $val
    ]);
}
}
