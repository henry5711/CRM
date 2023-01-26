<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryFaqResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'attributes' => [
                'title'  => $this->resource->title,
                'body'   => $this->resource->body,
            ],
            'relationships' => [
                'faqs' => $this->whenLoaded(
                    'faqs', function () {
                        return CategoryFaqResource::collection($this->resource->faqs);
                    }
                ),
                'images' => $this->whenLoaded(
                    'images', function () {
                        return ImageResource::collection($this->resource->images);
                    }
                ),
            ],
        ];
    }
}
