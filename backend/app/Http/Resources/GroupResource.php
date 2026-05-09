<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'currency'    => $this->currency,
            'owner_id'    => $this->owner_id,
            'members'     => UserResource::collection($this->whenLoaded('members')),
            'created_at'  => $this->created_at,
        ];
    }
}
