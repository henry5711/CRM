<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LabelTaksResource extends JsonResource
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
                'name'       => $this->resource->name,
                'description'=>$this->resource->description,
                'created_at' => $this->resource->created_at,
                'updated_at' => $this->resource->updated_at,
            ],
        ];
    }
}
