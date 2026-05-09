<?php
namespace App\Domain\Notifications\Services;

use App\Models\EmailNotificationLog;
use App\Models\FinancialNotification;
use App\Models\Transaction;
use App\Models\User;

class NotificationService {
    public function createInApp(User $user, array $data): FinancialNotification {
        return FinancialNotification::create(array_merge(['user_id' => $user->id], $data));
    }

    public function markAsRead(int $userId, ?int $id = null): void {
        $query = FinancialNotification::where('user_id', $userId)->whereNull('read_at');
        if ($id) $query->where('id', $id);
        $query->update(['read_at' => now()]);
    }

    public function hasBeenSent(User $user, ?Transaction $transaction, string $type, ?\DateTimeInterface $date = null): bool {
        return EmailNotificationLog::where('user_id', $user->id)
            ->where('transaction_id', $transaction?->id)
            ->where('type', $type)
            ->where('reference_date', ($date ?? today())->format('Y-m-d'))
            ->exists();
    }

    public function logSent(User $user, ?Transaction $transaction, string $type, ?\DateTimeInterface $date = null): void {
        EmailNotificationLog::firstOrCreate([
            'user_id'        => $user->id,
            'transaction_id' => $transaction?->id,
            'type'           => $type,
            'reference_date' => ($date ?? today())->format('Y-m-d'),
        ]);
    }
}
