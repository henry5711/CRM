<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TakResource extends JsonResource
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
                'title' => $this->resource->title,
                'description' => $this->resource->description,
                'creator_id' => $this->resource->creator_id,
                'status_id'          => $this->resource->status_id,
                'fec_ini' => $this->resource->fec_ini,
                'fec_end' => $this->resource->fec_end,
                'user_id'          => $this->resource->user_id,
                'created_at'     => $this->resource->created_at,
                'updated_at'     => $this->resource->updated_at,
            ],

            'relationships' => [
                'getUser' => $this->whenLoaded('getUser', function () {
                    return UserResource::make($this->resource->getUser);
                }),
                'getCreator' => $this->whenLoaded('getCreator', function () {
                    return UserResource::make($this->resource->getCreator);
                }),
                'getStatus' => $this->whenLoaded('getStatus', function () {
                    return StatusesResource::make($this->resource->getStatus);
                }),
                'getComments' => $this->whenLoaded('getComments', function () {
                    return TakCommentResource::make($this->resource->getComments);
                }),
                'labesTaks' => $this->whenLoaded('labesTaks', function () {
                    return LabelTaksResource::collection($this->resource->labesTaks);
                }),
                'images' => $this->whenLoaded('images', function() {
                    return ImageResource::collection($this->resource->images);
                }),
            ],
        ];
    }
}
