<?php
namespace App\Domain\Dashboard\Services;

use App\Models\Group;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService {
    public function monthly(Group $group, string $month): array {
        $cacheKey = "dashboard.{$group->id}.{$month}";

        return Cache::remember($cacheKey, 300, function () use ($group, $month) {
            $base = fn() => Transaction::where('group_id', $group->id)
                ->where('reference_month', $month)
                ->whereNull('deleted_at');

            $income     = $base()->where('type', 'income')->sum('amount');
            $expense    = $base()->where('type', 'expense')->sum('amount');
            $investment = $base()->where('type', 'investment')->sum('amount');

            return [
                'month'   => $month,
                'totals'  => [
                    'income'     => (float) $income,
                    'expense'    => (float) $expense,
                    'investment' => (float) $investment,
                    'balance'    => (float) ($income - $expense - $investment),
                ],
                'by_status' => $base()
                    ->select('status', DB::raw('count(*) as qty'), DB::raw('sum(amount) as total'))
                    ->groupBy('status')
                    ->get(),
                'by_category' => $base()
                    ->where('type', 'expense')
                    ->whereNotNull('category_id')
                    ->join('categories', 'categories.id', '=', 'transactions.category_id')
                    ->select('categories.id', 'categories.name', 'categories.color', DB::raw('sum(transactions.amount) as total'))
                    ->groupBy('categories.id', 'categories.name', 'categories.color')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get(),
                'by_responsible' => $base()
                    ->whereNotNull('responsible_id')
                    ->join('users', 'users.id', '=', 'transactions.responsible_id')
                    ->select('users.id', 'users.name', 'transactions.type', DB::raw('sum(transactions.amount) as total'))
                    ->groupBy('users.id', 'users.name', 'transactions.type')
                    ->get(),
                'due_soon' => $base()
                    ->where('status', 'pending')
                    ->whereBetween('due_date', [today(), today()->addDays(7)])
                    ->with(['transactionName', 'category', 'responsible'])
                    ->orderBy('due_date')
                    ->limit(10)
                    ->get(),
                'overdue' => $base()
                    ->where('status', 'overdue')
                    ->with(['transactionName', 'category', 'responsible'])
                    ->orderBy('due_date')
                    ->limit(10)
                    ->get(),
                'recent_paid' => $base()
                    ->where('status', 'paid')
                    ->with(['transactionName', 'category'])
                    ->orderByDesc('paid_date')
                    ->limit(5)
                    ->get(),
            ];
        });
    }

    public function monthlyEvolution(Group $group, string $fromMonth, string $toMonth): array {
        return Transaction::where('group_id', $group->id)
            ->whereBetween('reference_month', [$fromMonth, $toMonth])
            ->whereNull('deleted_at')
            ->select('reference_month', 'type', DB::raw('sum(amount) as total'))
            ->groupBy('reference_month', 'type')
            ->orderBy('reference_month')
            ->get()
            ->groupBy('reference_month')
            ->map(fn($items) => $items->keyBy('type'))
            ->toArray();
    }

    public function invalidate(Group $group, string $month): void {
        Cache::forget("dashboard.{$group->id}.{$month}");
    }
}
