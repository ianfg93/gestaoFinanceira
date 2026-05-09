<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'title'          => $this->title,
            'body'           => $this->body,
            'read_at'        => $this->read_at,
            'action_url'     => $this->action_url,
            'created_at'     => $this->created_at,
            'transaction_id' => $this->transaction_id,
        ];
    }
}
