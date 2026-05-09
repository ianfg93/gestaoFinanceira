<?php
namespace App\Console\Commands;

use App\Domain\Notifications\Services\NotificationService;
use App\Models\Group;
use App\Models\Transaction;
use Illuminate\Console\Command;

class SendMonthlySummary extends Command {
    protected $signature   = 'notify:monthly-summary';
    protected $description = 'Send monthly financial summary on the 1st of each month';

    public function __construct(private NotificationService $notificationService) {
        parent::__construct();
    }

    public function handle(): void {
        $lastMonth = now()->subMonth()->format('Y-m');

        Group::with('members')->chunk(50, function ($groups) use ($lastMonth) {
            foreach ($groups as $group) {
                $income     = Transaction::where('group_id', $group->id)->where('reference_month', $lastMonth)->where('type', 'income')->sum('amount');
                $expense    = Transaction::where('group_id', $group->id)->where('reference_month', $lastMonth)->where('type', 'expense')->sum('amount');
                $investment = Transaction::where('group_id', $group->id)->where('reference_month', $lastMonth)->where('type', 'investment')->sum('amount');

                foreach ($group->members as $user) {
                    if ($this->notificationService->hasBeenSent($user, null, 'monthly_summary', now())) continue;

                    $this->notificationService->createInApp($user, [
                        'group_id'   => $group->id,
                        'type'       => 'monthly_summary',
                        'title'      => 'Resumo de ' . now()->subMonth()->locale('pt_BR')->monthName,
                        'body'       => "Receitas: R$ " . number_format($income, 2, ',', '.') . " | Despesas: R$ " . number_format($expense, 2, ',', '.') . " | Investimentos: R$ " . number_format($investment, 2, ',', '.'),
                        'action_url' => "/dashboard",
                    ]);

                    $this->notificationService->logSent($user, null, 'monthly_summary');
                }
            }
        });

        $this->info('Monthly summaries sent.');
    }
}
