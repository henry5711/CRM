<?php

namespace App\Http\Requests\Tak;

use App\Traits\CustomResponseFormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class StoreTakRequest extends FormRequest
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
            'title' =>'required|string|max:255',
            'description' =>'nullable|string',
            'status_id'  => 'required|exists:statuses,id',
            'user_id' =>'required|integer|exists:users,id',
            'creator_id' =>'required|integer|exists:users,id',

        ];
    }
}
