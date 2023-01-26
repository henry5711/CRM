<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Config;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
                'filename'       => $this->resource->filename,
                'url'            => $this->resource->pathS3 ? $this->getImageAttribute($this->resource->pathS3.$this->resource->filename) : null,
                'imageable_type' => $this->resource->imageable_type,
                'imageable_id'   => $this->resource->imageable_id,
                'tag'            => $this->resource->tag,
                'name'            => $this->resource->name,
                'description'     => $this->resource->description,
                'category'       => $this->resource->category,
                'created_at'     => $this->resource->created_at,
                'updated_at'     => $this->resource->updated_at,
            ],
        ];
    }
}
