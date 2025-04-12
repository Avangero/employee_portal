<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pull_request_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pull_request_id')->constrained('pull_requests')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users');
            $table->enum('status', ['approved', 'changes_requested', 'returned'])->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('pull_request_reviews');
    }
};
