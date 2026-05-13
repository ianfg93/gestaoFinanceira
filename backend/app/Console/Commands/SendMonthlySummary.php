<?php
namespace App\Console\Commands;

use App\Domain\Notifications\Services\NotificationService;
use App\Models\Group;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMonthlySummary extends Command {
    protected $signature   = 'notify:monthly-summary';
    protected $description = 'Send monthly financial summary on the 1st of each month';

    public function __construct(private NotificationService $notificationService) {
        parent::__construct();
    }

    public function handle(): void {
        $lastMonth = now()->subMonth()->format('Y-m');
        $monthLabel = Carbon::createFromFormat('Y-m', $lastMonth)->locale('pt_BR')->translatedFormat('F \\d\\e Y');
        $generatedAt = now()->format('d/m/Y');

        Group::with('members')->chunk(50, function ($groups) use ($lastMonth, $monthLabel, $generatedAt) {
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

                    if (config('services.notifications.email_enabled')) {
                        $incomeBr = 'R$ ' . number_format($income, 2, ',', '.');
                        $expenseBr = 'R$ ' . number_format($expense, 2, ',', '.');
                        $investmentBr = 'R$ ' . number_format($investment, 2, ',', '.');
                        $balance = $income - $expense - $investment;
                        $balanceBr = 'R$ ' . number_format($balance, 2, ',', '.');
                        $balanceColor = $balance >= 0 ? '#166534' : '#991b1b';

                        $html = "
<div style='font-family:Arial,sans-serif;background:#f5f7fb;padding:24px'>
  <div style='max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden'>
    <div style='background:#0f766e;color:#ffffff;padding:20px 24px'>
      <h1 style='margin:0;font-size:22px'>Resumo Financeiro Mensal</h1>
      <p style='margin:8px 0 0 0;font-size:14px;opacity:.95'>{$monthLabel}</p>
    </div>
    <div style='padding:20px 24px;color:#111827'>
      <p style='margin:0 0 14px 0'>Olá, {$user->name}.</p>
      <p style='margin:0 0 20px 0;color:#4b5563'>Este é o fechamento financeiro do grupo <strong>{$group->name}</strong>.</p>
      <table style='width:100%;border-collapse:collapse'>
        <tr><td style='padding:10px;border-bottom:1px solid #e5e7eb'>Receitas</td><td style='padding:10px;border-bottom:1px solid #e5e7eb;text-align:right'>{$incomeBr}</td></tr>
        <tr><td style='padding:10px;border-bottom:1px solid #e5e7eb'>Despesas</td><td style='padding:10px;border-bottom:1px solid #e5e7eb;text-align:right'>{$expenseBr}</td></tr>
        <tr><td style='padding:10px;border-bottom:1px solid #e5e7eb'>Investimentos</td><td style='padding:10px;border-bottom:1px solid #e5e7eb;text-align:right'>{$investmentBr}</td></tr>
        <tr><td style='padding:12px 10px;font-weight:700'>Saldo do mês</td><td style='padding:12px 10px;text-align:right;font-weight:700;color:{$balanceColor}'>{$balanceBr}</td></tr>
      </table>
      <p style='margin:20px 0 0 0;color:#6b7280;font-size:12px'>Gerado em {$generatedAt}.</p>
    </div>
  </div>
</div>";

                        Mail::html(
                            $html,
                            function ($message) use ($user, $monthLabel) {
                                $message->to($user->email, $user->name)
                                    ->subject("Resumo mensal financeiro - {$monthLabel}");
                            }
                        );
                    }

                    $this->notificationService->logSent($user, null, 'monthly_summary');
                }
            }
        });

        $this->info('Monthly summaries sent.');
    }
}
