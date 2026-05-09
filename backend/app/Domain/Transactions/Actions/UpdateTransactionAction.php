<?php
namespace App\Domain\Transactions\Actions;

use App\Domain\Transactions\Services\RecurrenceService;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use Illuminate\Support\Facades\DB;

class UpdateTransactionAction {
    public function __construct(private RecurrenceService $recurrenceService) {}

    public function execute(Transaction $transaction, array $data, int $userId, string $scope = 'this'): Transaction {
        return DB::transaction(function () use ($transaction, $data, $userId, $scope) {
            $oldValues = $transaction->toArray();
            $data['updated_by'] = $userId;

            if ($transaction->series_id && $scope !== 'this') {
                $this->recurrenceService->applyEdit($transaction, $data, $scope);
            } else {
                $transaction->update($data);
            }

            // Log changed fields
            foreach ($data as $field => $newValue) {
                if (isset($oldValues[$field]) && (string)$oldValues[$field] !== (string)$newValue) {
                    TransactionHistory::create([
                        'transaction_id' => $transaction->id,
                        'user_id'        => $userId,
                        'action'         => 'updated',
                        'field_name'     => $field,
                        'old_value'      => (string)$oldValues[$field],
                        'new_value'      => (string)$newValue,
                    ]);
                }
            }

            return $transaction->fresh();
        });
    }
}
