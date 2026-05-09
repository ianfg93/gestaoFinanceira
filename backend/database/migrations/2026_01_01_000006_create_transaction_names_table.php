<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transaction_names', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('normalized', 200);
            $table->string('type', 20)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedInteger('usage_count')->default(1);
            $table->timestamps();
            $table->unique(['group_id', 'normalized']);
        });
    }
    public function down(): void { Schema::dropIfExists('transaction_names'); }
};
