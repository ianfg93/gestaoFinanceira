<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(
                ['group_id', 'reference_month', 'status', 'due_date', 'deleted_at'],
                'transactions_dashboard_lookup_idx'
            );
        });
    }

    public function down(): void {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_dashboard_lookup_idx');
        });
    }
};
