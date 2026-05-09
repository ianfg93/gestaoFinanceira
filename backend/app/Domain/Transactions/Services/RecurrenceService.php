<?php
namespace App\Domain\Transactions\Services;

use App\Domain\Transactions\DTOs\SeriesDTO;
use App\Models\Transaction;
use App\Models\TransactionSeries;
use Carbon\Carbon;

class RecurrenceService {
    public function generate(SeriesDTO $dto, TransactionSeries $series): void {
        $dates = $this->buildDates($dto);
        $chunks = array_chunk($dates, 500);
        $now = now();

        foreach ($chunks as $chunk) {
            $rows = array_map(fn($date) => [
                'group_id'            => $dto->groupId,
                'series_id'           => $series->id,
                'type'                => $dto->type,
                'transaction_name_id' => $dto->transactionNameId,
                'category_id'         => $dto->categoryId,
                'amount'              => $dto->amount,
                'status'              => 'pending',
                'due_date'            => $date,
                'reference_month'     => substr($date, 0, 7),
                'responsible_id'      => $dto->responsibleId,
                'notes'               => $dto->notes,
                'is_recurring'        => true,
                'created_by'          => $dto->createdBy,
                'created_at'          => $now,
                'updated_at'          => $now,
            ], $chunk);

            Transaction::insert($rows);
        }
    }

    public function generateInstallments(SeriesDTO $dto, TransactionSeries $series): void {
        $total = $dto->totalInstallments ?? 1;
        $cursor = Carbon::parse($dto->startsAt);
        $rows = [];
        $now = now();

        for ($i = 1; $i <= $total; $i++) {
            $rows[] = [
                'group_id'            => $dto->groupId,
                'series_id'           => $series->id,
                'installment_number'  => $i,
                'total_installments'  => $total,
                'type'                => $dto->type,
                'transaction_name_id' => $dto->transactionNameId,
                'category_id'         => $dto->categoryId,
                'amount'              => $dto->amount,
                'status'              => 'pending',
                'due_date'            => $cursor->toDateString(),
                'reference_month'     => $cursor->format('Y-m'),
                'responsible_id'      => $dto->responsibleId,
                'notes'               => $dto->notes,
                'is_recurring'        => false,
                'created_by'          => $dto->createdBy,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
            $cursor->addMonth();
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Transaction::insert($chunk);
        }
    }

    private function buildDates(SeriesDTO $dto): array {
        $dates  = [];
        $cursor = Carbon::parse($dto->startsAt);
        $end    = $dto->endsAt ? Carbon::parse($dto->endsAt) : null;
        $max    = 120;

        while ($max-- > 0) {
            if ($end && $cursor->gt($end)) break;
            $dates[] = $cursor->toDateString();

            match ($dto->recurrenceType) {
                'weekly'    => $cursor->addWeek(),
                'biweekly'  => $cursor->addWeeks(2),
                'yearly'    => $cursor->addYear(),
                'custom'    => $cursor->addDays($dto->intervalDays ?? 30),
                default     => $cursor->addMonth(),
            };
        }

        return $dates;
    }

    public function applyEdit(Transaction $transaction, array $data, string $scope): void {
        match ($scope) {
            'future' => $this->editFuture($transaction, $data),
            'all'    => $this->editAll($transaction, $data),
            default  => $transaction->update($data),
        };
    }

    private function editFuture(Transaction $t, array $data): void {
        Transaction::where('series_id', $t->series_id)
            ->where('due_date', '>=', $t->due_date)
            ->whereNull('deleted_at')
            ->update(array_merge($data, ['updated_at' => now()]));
    }

    private function editAll(Transaction $t, array $data): void {
        Transaction::where('series_id', $t->series_id)
            ->whereNull('deleted_at')
            ->update(array_merge($data, ['updated_at' => now()]));

        $seriesFields = array_intersect_key($data, array_flip(['amount','category_id','responsible_id','notes']));
        if (!empty($seriesFields)) {
            TransactionSeries::find($t->series_id)?->update($seriesFields);
        }
    }
}
