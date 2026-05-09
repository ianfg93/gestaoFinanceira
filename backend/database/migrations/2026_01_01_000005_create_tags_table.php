<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->char('color', 7)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['group_id', 'name']);
        });
    }
    public function down(): void { Schema::dropIfExists('tags'); }
};
