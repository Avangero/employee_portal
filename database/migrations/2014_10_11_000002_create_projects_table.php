<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('leader_id')->nullable();
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['project_id', 'user_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
    }
};
