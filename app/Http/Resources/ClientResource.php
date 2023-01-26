<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
                'document'    => $this->resource->document,
                'country_id'     => $this->resource->country_id,
                'name'          =>$this->resource->name,
                'email'          =>$this->resource->email,
                'code_phone'          =>$this->resource->code_phone,
                'phone'          =>$this->resource->phone,
                'origin_id'          =>$this->resource->origin_id,
                'segmento'          =>$this->resource->segmento,
                'tipification'          =>$this->resource->tipification,
                'calender'          => $this->resource->calender,
                'observe'          => $this->resource->observe,
                'status_id'          => $this->resource->status_id,
                'user_id'          => $this->resource->user_id,
                'created_at'     => $this->resource->created_at,
                'updated_at'     => $this->resource->updated_at,
            ],

            'relationships' => [
                'getCountry' => $this->whenLoaded('getCountry', function() {
                    return CountryResource::make($this->resource->getCountry);
                }),
                'getUser' => $this->whenLoaded('getUser', function() {
                    return UserResource::make($this->resource->getUser);
                }),
                'getStatus' => $this->whenLoaded('getStatus', function() {
                    return StatusesResource::make($this->resource->getStatus);
                }),
                'getOrigin' => $this->whenLoaded('getOrigin', function() {
                    return OriginResource::make($this->resource->getOrigin);
                }),
                'getObserve' => $this->whenLoaded('getObserve', function() {
                    return CrmObserveResource::collection($this->resource->getObserve);
                }),
                'type' => $this->whenLoaded('type', function() {
                    return TypeTypificationResource::make($this->resource->type);
                }),
                'typification' => $this->whenLoaded('typification', function() {
                    return TypificationResource::make($this->resource->typification);
                })
            ],
        ];
    }
}
