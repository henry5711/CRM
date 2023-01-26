<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CrmObserveResource extends JsonResource
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
                'client_id'           => $this->resource->client_id,
                'contend'     => $this->resource->contend,
                'updated_at'     => $this->resource->updated_at,
                'created_at'     => $this->resource->created_at,
            ],
        ];
    }
}
