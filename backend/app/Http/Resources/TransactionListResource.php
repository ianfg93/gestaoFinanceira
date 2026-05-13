<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'series_id' => $this->series_id,
            'installment_number' => $this->installment_number,
            'total_installments' => $this->total_installments,
            'type' => $this->type,
            'name' => $this->transactionName?->name,
            'transaction_name_id' => $this->transaction_name_id,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                if (!$this->category) {
                    return null;
                }

                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'color' => $this->category->color,
                ];
            }),
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'paid_date' => $this->paid_date?->format('Y-m-d'),
            'reference_month' => $this->reference_month,
            'responsible_id' => $this->responsible_id,
            'responsible' => $this->whenLoaded('responsible', function () {
                if (!$this->responsible) {
                    return null;
                }

                return [
                    'id' => $this->responsible->id,
                    'name' => $this->responsible->name,
                ];
            }),
            'notes' => $this->notes,
            'is_recurring' => (bool) $this->is_recurring,
            'notifications_muted' => (bool) $this->notifications_muted,
            'tags' => [],
            'attachments_count' => (int) ($this->attachments_count ?? 0),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
