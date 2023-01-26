<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TakCommentResource extends JsonResource
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
                'content' => $this->resource->content,
                'tak_id' => $this->resource->tak_id,
                'user_id'          => $this->resource->user_id,
                'created_at'     => $this->resource->created_at,
                'updated_at'     => $this->resource->updated_at,
            ],

            'relationships' => [
                'getUser' => $this->whenLoaded('getUser', function () {
                    return UserResource::make($this->resource->getUser);
                }),
                'geTak' => $this->whenLoaded('geTak', function () {
                    return TakResource::make($this->resource->geTak);
                }),
            ],
        ];
    }
}
