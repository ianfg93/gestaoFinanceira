<?php
namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class MarkOverdueTransactions extends Command {
    protected $signature   = 'transactions:mark-overdue';
    protected $description = 'Mark pending transactions past due_date as overdue';

    public function handle(): void {
        $count = Transaction::where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->whereNull('deleted_at')
            ->update(['status' => 'overdue', 'updated_at' => now()]);

        $this->info("Marked {$count} transactions as overdue.");
    }
}
