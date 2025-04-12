<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_id', 'manager_id', 'role_id']);
        });
    }
};
