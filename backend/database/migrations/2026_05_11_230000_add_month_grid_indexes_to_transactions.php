<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(
                ['group_id', 'reference_month', 'deleted_at', 'due_date'],
                'transactions_month_grid_idx'
            );

            $table->index(
                ['group_id', 'reference_month', 'deleted_at', 'status', 'type'],
                'transactions_month_filters_idx'
            );
        });
    }

    public function down(): void {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_month_grid_idx');
            $table->dropIndex('transactions_month_filters_idx');
        });
    }
};
