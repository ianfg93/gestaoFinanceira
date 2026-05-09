<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('email_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50);
            $table->timestamp('sent_at')->useCurrent();
            $table->date('reference_date')->nullable();
            $table->unique(['user_id', 'transaction_id', 'type', 'reference_date'], 'notif_log_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('email_notification_logs'); }
};
