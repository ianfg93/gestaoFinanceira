<?php
namespace App\Domain\Transactions\DTOs;

class SeriesDTO {
    public function __construct(
        public readonly int $groupId,
        public readonly string $type,
        public readonly int $transactionNameId,
        public readonly float $amount,
        public readonly string $startsAt,
        public readonly string $seriesType = 'recurrence',
        public readonly ?string $recurrenceType = 'monthly',
        public readonly ?string $endsAt = null,
        public readonly ?int $totalInstallments = null,
        public readonly ?int $intervalDays = null,
        public readonly ?int $categoryId = null,
        public readonly ?int $responsibleId = null,
        public readonly ?string $notes = null,
        public readonly int $createdBy = 0,
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            groupId: $data['group_id'],
            type: $data['type'],
            transactionNameId: $data['transaction_name_id'],
            amount: (float) $data['amount'],
            startsAt: $data['starts_at'],
            seriesType: $data['series_type'] ?? 'recurrence',
            recurrenceType: $data['recurrence_type'] ?? 'monthly',
            endsAt: $data['ends_at'] ?? null,
            totalInstallments: $data['total_installments'] ?? null,
            intervalDays: $data['interval_days'] ?? null,
            categoryId: $data['category_id'] ?? null,
            responsibleId: $data['responsible_id'] ?? null,
            notes: $data['notes'] ?? null,
            createdBy: $data['created_by'] ?? 0,
        );
    }
}
