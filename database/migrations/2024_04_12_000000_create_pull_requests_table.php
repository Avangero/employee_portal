<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pull_requests', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('team_id')->constrained('teams');
            $table->enum('status', ['pending', 'reviewing', 'changes_requested', 'approved'])->default('pending');
            $table->integer('approvals_count')->default(0);
            $table->integer('required_approvals')->default(2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pull_requests');
    }
};
