<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreign('leader_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('leader_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('project_user', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['leader_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_id', 'manager_id', 'role_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['team_id', 'leader_id']);
        });

        Schema::table('project_user', function (Blueprint $table) {
            $table->dropForeign(['project_id', 'user_id']);
        });
    }
};
