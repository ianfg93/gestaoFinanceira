<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'                   => $this->id,
            'group_id'             => $this->group_id,
            'series_id'            => $this->series_id,
            'installment_number'   => $this->installment_number,
            'total_installments'   => $this->total_installments,
            'type'                 => $this->type,
            'name'                 => $this->transactionName?->name,
            'transaction_name_id'  => $this->transaction_name_id,
            'category_id'          => $this->category_id,
            'category'             => new CategoryResource($this->whenLoaded('category')),
            'amount'               => (float) $this->amount,
            'status'               => $this->status,
            'due_date'             => $this->due_date?->format('Y-m-d'),
            'paid_date'            => $this->paid_date?->format('Y-m-d'),
            'reference_month'      => $this->reference_month,
            'responsible_id'       => $this->responsible_id,
            'responsible'          => new UserResource($this->whenLoaded('responsible')),
            'notes'                => $this->notes,
            'is_recurring'         => $this->is_recurring,
            'notifications_muted'  => $this->notifications_muted,
            'tags'                 => TagResource::collection($this->whenLoaded('tags')),
            'attachments_count'    => (int) ($this->attachments_count ?? 0),
            'created_by'           => $this->created_by,
            'updated_by'           => $this->updated_by,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
