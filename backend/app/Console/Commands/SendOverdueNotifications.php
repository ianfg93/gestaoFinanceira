<?php
namespace App\Console\Commands;

use App\Domain\Notifications\Services\NotificationService;
use App\Models\Transaction;
use Illuminate\Console\Command;

class SendOverdueNotifications extends Command {
    protected $signature   = 'notify:overdue';
    protected $description = 'Send daily notifications for overdue transactions';

    public function __construct(private NotificationService $notificationService) {
        parent::__construct();
    }

    public function handle(): void {
        Transaction::with(['group.members', 'transactionName'])
            ->where('status', 'overdue')
            ->whereNull('deleted_at')
            ->where('notifications_muted', false)
            ->chunk(100, function ($transactions) {
                foreach ($transactions as $transaction) {
                    foreach ($transaction->group->members as $user) {
                        if ($this->notificationService->hasBeenSent($user, $transaction, 'overdue')) continue;

                        $this->notificationService->createInApp($user, [
                            'group_id'       => $transaction->group_id,
                            'transaction_id' => $transaction->id,
                            'type'           => 'overdue',
                            'title'          => "{$transaction->transactionName->name} está atrasada",
                            'body'           => 'Venceu em ' . $transaction->due_date->format('d/m/Y') . ' — R$ ' . number_format($transaction->amount, 2, ',', '.'),
                            'action_url'     => "/months/{$transaction->reference_month}",
                        ]);

                        $this->notificationService->logSent($user, $transaction, 'overdue');
                    }
                }
            });

        $this->info('Overdue notifications sent.');
    }
}
