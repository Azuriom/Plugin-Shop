<?php

namespace Azuriom\Plugin\Shop\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Package extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'description' => $this->description,
            'image' => $this->imageUrl(),
            'price' => $this->price,
        ];
    }
}
