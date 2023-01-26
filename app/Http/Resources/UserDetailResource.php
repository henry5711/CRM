<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'attributes' => [
                'user_id'                   => $this->resource->user_id,
                'country_id'                => $this->resource->country_id,
                'tp_document_id'            => $this->resource->tp_document_id,
                'gender_id'                 => $this->resource->gender_id,
                'broker_id'                 => $this->resource->broker_id,
                // 'ip'                        => $this->resource->ip,
                'document'                  => $this->resource->document,
                'address'                   => $this->resource->address,
                'birth'                     => $this->resource->birth,
                'profile_background_image_id' => $this->resource->profile_background_image_id,
                'code_phone'                => $this->resource->code_phone,
                'phone'                     => $this->resource->phone,
                // 'confirmation_code_phone'   => $this->resource->confirmation_code_phone,
                // 'phone_verified_at'         => $this->resource->phone_verified_at,
                'phone_valid'               => $this->resource->phone_verified_at ? true : false,
                // 'deleted_at'                => $this->resource->deleted_at,
                // 'created_at'                => $this->resource->created_at,
                // 'updated_at'                => $this->resource->updated_at,
                'name_card'                => $this->resource->name_card,
                'code_phone_card'                => $this->resource->code_phone_card,
                'phone_card'                => $this->resource->phone_card,
                'email_card'                => $this->resource->email_card,

            ],
            'relationships' => [
                'country' => $this->whenLoaded('country', function() {
                    return CountryResource::make($this->resource->country);
                }),
                'gender' => $this->whenLoaded('gender', function() {
                    return new GenderResource($this->resource->gender);
                }),
                'tpDocument' => $this->whenLoaded('tpDocument', function() {
                    return new TpDocumentResource($this->resource->tpDocument);
                }),
                'identityVerification' => $this->whenLoaded('identityVerification', function() {
                    return new IdentityVerificationResource($this->resource->identityVerification);
                }),
                'images' => $this->whenLoaded('images', function() {
                    return ImageResource::collection($this->resource->images);
                }),
                'broker' => $this->whenLoaded('broker', function() {
                    return BrokerCountryResource::make($this->resource->broker);
                })
            ],
        ];
    }
}
