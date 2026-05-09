<?php
namespace App\Domain\Transactions\Actions;

use App\Domain\Transactions\DTOs\SeriesDTO;
use App\Domain\Transactions\Services\RecurrenceService;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use App\Models\TransactionName;
use App\Models\TransactionSeries;
use Illuminate\Support\Facades\DB;

class CreateTransactionAction {
    public function __construct(private RecurrenceService $recurrenceService) {}

    public function execute(array $data, int $userId): Transaction|TransactionSeries {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;

            $transactionName = TransactionName::resolve(
                $data['group_id'],
                $data['name']
            );
            $data['transaction_name_id'] = $transactionName->id;

            if (!empty($data['is_series']) || !empty($data['is_installment'])) {
                return $this->createSeries($data, $userId, $transactionName);
            }

            return $this->createSingle($data, $userId);
        });
    }

    private function createSingle(array $data, int $userId): Transaction {
        $data['reference_month'] = substr($data['due_date'], 0, 7);
        $t = Transaction::create($data);

        TransactionHistory::create([
            'transaction_id' => $t->id,
            'user_id'        => $userId,
            'action'         => 'created',
            'new_value'      => json_encode($t->toArray()),
        ]);

        return $t;
    }

    private function createSeries(array $data, int $userId, TransactionName $name): TransactionSeries {
        $seriesType = !empty($data['is_installment']) ? 'installment' : 'recurrence';

        $series = TransactionSeries::create([
            'group_id'            => $data['group_id'],
            'series_type'         => $seriesType,
            'recurrence_type'     => $data['recurrence_type'] ?? 'monthly',
            'interval_days'       => $data['interval_days'] ?? null,
            'starts_at'           => $data['due_date'],
            'ends_at'             => $data['ends_at'] ?? null,
            'total_installments'  => $data['total_installments'] ?? null,
            'base_amount'         => $data['amount'],
            'transaction_name_id' => $name->id,
            'category_id'         => $data['category_id'] ?? null,
            'responsible_id'      => $data['responsible_id'] ?? null,
            'notes'               => $data['notes'] ?? null,
            'created_by'          => $userId,
        ]);

        $dto = SeriesDTO::fromArray(array_merge($data, [
            'transaction_name_id' => $name->id,
            'series_type'         => $seriesType,
            'starts_at'           => $data['due_date'],
            'created_by'          => $userId,
        ]));

        if ($seriesType === 'installment') {
            $this->recurrenceService->generateInstallments($dto, $series);
        } else {
            $this->recurrenceService->generate($dto, $series);
        }

        return $series;
    }
}
