<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transaction_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('series_type', 20)->default('recurrence'); // recurrence|installment
            $table->string('recurrence_type', 20)->nullable(); // monthly|weekly|biweekly|yearly|custom
            $table->unsignedInteger('interval_days')->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->unsignedInteger('total_installments')->nullable();
            $table->decimal('base_amount', 15, 2)->nullable();
            $table->foreignId('transaction_name_id')->nullable()->constrained('transaction_names')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('transaction_series'); }
};
