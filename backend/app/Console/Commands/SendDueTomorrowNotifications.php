<?php
namespace App\Console\Commands;

use App\Domain\Notifications\Services\NotificationService;
use App\Models\FinancialNotification;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDueTomorrowNotifications extends Command {
    protected $signature   = 'notify:due-tomorrow';
    protected $description = 'Send notifications for transactions due tomorrow';

    public function __construct(private NotificationService $notificationService) {
        parent::__construct();
    }

    public function handle(): void {
        $tomorrow = today()->addDay();

        Transaction::with(['group.members', 'transactionName', 'responsible'])
            ->where('status', 'pending')
            ->whereDate('due_date', $tomorrow)
            ->whereNull('deleted_at')
            ->where('notifications_muted', false)
            ->chunk(100, function ($transactions) {
                foreach ($transactions as $transaction) {
                    $users = $transaction->group->members;
                    if ($transaction->responsible_id) {
                        $users = $users->where('id', $transaction->responsible_id);
                    }

                    foreach ($users as $user) {
                        if ($this->notificationService->hasBeenSent($user, $transaction, 'due_tomorrow')) continue;

                        $this->notificationService->createInApp($user, [
                            'group_id'       => $transaction->group_id,
                            'transaction_id' => $transaction->id,
                            'type'           => 'due_tomorrow',
                            'title'          => "{$transaction->transactionName->name} vence amanhã",
                            'body'           => 'R$ ' . number_format($transaction->amount, 2, ',', '.'),
                            'action_url'     => "/months/{$transaction->reference_month}",
                        ]);

                        if (config('services.notifications.email_enabled')) {
                            Mail::raw(
                                "A conta '{$transaction->transactionName->name}' vence amanha.\nValor: R$ " . number_format($transaction->amount, 2, ',', '.') . "\nAcesse: /months/{$transaction->reference_month}",
                                function ($message) use ($user, $transaction) {
                                    $message->to($user->email, $user->name)
                                        ->subject("Conta vence amanha: {$transaction->transactionName->name}");
                                }
                            );
                        }

                        $this->notificationService->logSent($user, $transaction, 'due_tomorrow');
                    }
                }
            });

        $this->info('Due-tomorrow notifications sent.');
    }
}
