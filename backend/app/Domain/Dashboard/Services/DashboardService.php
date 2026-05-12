<?php
namespace App\Domain\Dashboard\Services;

use App\Models\Group;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService {
    private function cacheVersion(Group $group): int {
        return (int) Cache::get("dashboard.version.{$group->id}", 1);
    }

    public function monthly(Group $group, string $month): array {
        $version = $this->cacheVersion($group);
        $cacheKey = "dashboard.{$group->id}.v{$version}.{$month}";

        return Cache::remember($cacheKey, 300, function () use ($group, $month) {
            $base = fn() => Transaction::query()
                ->from('transactions')
                ->where('reference_month', $month)
                ->where('group_id', $group->id)
                ->whereNull('deleted_at');

            $totalsByType = $base()
                ->select('type', DB::raw('sum(amount) as total'))
                ->groupBy('type')
                ->pluck('total', 'type');

            $income = (float) ($totalsByType['income'] ?? 0);
            $expense = (float) ($totalsByType['expense'] ?? 0);
            $investment = (float) ($totalsByType['investment'] ?? 0);

            return [
                'month'   => $month,
                'totals'  => [
                    'income'     => $income,
                    'expense'    => $expense,
                    'investment' => $investment,
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
        $version = $this->cacheVersion($group);
        $cacheKey = "dashboard.evolution.{$group->id}.v{$version}.{$fromMonth}.{$toMonth}";

        return Cache::remember($cacheKey, 300, function () use ($group, $fromMonth, $toMonth) {
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
        });
    }

    public function invalidate(Group $group, string $month): void {
        Cache::increment("dashboard.version.{$group->id}");
    }
}
