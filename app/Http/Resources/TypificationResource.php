<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TypificationResource extends JsonResource
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
                'name'    => $this->resource->name,
                'description'     => $this->resource->description,
                'type_typification_id'=> $this->resource->type_typification_id,
                'created_at'     => $this->resource->created_at,
                'updated_at'     => $this->resource->updated_at,
            ],

            'relationships' => [
                'type' => $this->whenLoaded('type', function() {
                    return TypeTypificationResource::make($this->resource->type);
                }),
            ],
        ];
    }
}
