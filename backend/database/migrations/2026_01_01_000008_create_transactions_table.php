<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('series_id')->nullable()->constrained('transaction_series')->nullOnDelete();
            $table->unsignedInteger('installment_number')->nullable();
            $table->unsignedInteger('total_installments')->nullable();
            $table->string('type', 20); // expense|income|investment
            $table->foreignId('transaction_name_id')->constrained('transaction_names');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('status', 20)->default('pending'); // pending|paid|overdue|cancelled|partial
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->char('reference_month', 7); // YYYY-MM
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->unsignedInteger('notify_before_days')->default(1);
            $table->boolean('notifications_muted')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_id', 'reference_month']);
            $table->index('due_date');
            $table->index('status');
            $table->index('series_id');
            $table->index('transaction_name_id');
            $table->index('reference_month');
        });
    }
    public function down(): void { Schema::dropIfExists('transactions'); }
};
