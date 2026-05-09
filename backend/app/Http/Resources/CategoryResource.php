<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'color'      => $this->color,
            'icon'       => $this->icon,
            'type'       => $this->type,
            'sort_order' => $this->sort_order,
            'parent_id'  => $this->parent_id,
            'children'   => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
