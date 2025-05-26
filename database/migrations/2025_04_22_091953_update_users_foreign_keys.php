<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'manager_id')) {
                $foreignKeys = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableForeignKeys('users');

                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('manager_id', $foreignKey->getLocalColumns())) {
                        $table->dropForeign($foreignKey->getName());
                    }
                }

                $table->foreign('manager_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'manager_id')) {
                $foreignKeys = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableForeignKeys('users');

                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('manager_id', $foreignKey->getLocalColumns())) {
                        $table->dropForeign($foreignKey->getName());
                    }
                }

                $table->foreign('manager_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('restrict');
            }
        });
    }
};
