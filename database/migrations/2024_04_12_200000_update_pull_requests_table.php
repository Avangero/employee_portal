<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Создаем новый тип ENUM для статусов
        DB::statement('DROP TYPE IF EXISTS pull_request_status_enum CASCADE');
        DB::statement("CREATE TYPE pull_request_status_enum AS ENUM (
            'created',
            'in_review',
            'changes_requested',
            'disputed',
            'updated',
            'approved',
            'returned'
        )");

        // Добавляем временный столбец нового типа
        DB::statement('ALTER TABLE pull_requests ADD COLUMN new_status pull_request_status_enum');

        // Копируем и преобразуем данные
        DB::statement("UPDATE pull_requests SET new_status =
            CASE status
                WHEN 'pending' THEN 'created'::pull_request_status_enum
                WHEN 'changes_requested' THEN 'changes_requested'::pull_request_status_enum
                WHEN 'approved' THEN 'approved'::pull_request_status_enum
            END");

        // Удаляем старый столбец и переименовываем новый
        DB::statement('ALTER TABLE pull_requests DROP COLUMN status');
        DB::statement('ALTER TABLE pull_requests RENAME COLUMN new_status TO status');
        DB::statement("ALTER TABLE pull_requests ALTER COLUMN status SET DEFAULT 'created'");

        // Добавляем returns_count
        Schema::table('pull_requests', function (Blueprint $table) {
            $table->integer('returns_count')->default(0);
        });
    }

    public function down(): void
    {
        // Создаем старый тип ENUM
        DB::statement('DROP TYPE IF EXISTS old_pull_request_status_enum CASCADE');
        DB::statement("CREATE TYPE old_pull_request_status_enum AS ENUM (
            'pending',
            'changes_requested',
            'approved'
        )");

        // Добавляем временный столбец старого типа
        DB::statement('ALTER TABLE pull_requests ADD COLUMN new_status old_pull_request_status_enum');

        // Копируем и преобразуем данные обратно
        DB::statement("UPDATE pull_requests SET new_status =
            CASE status
                WHEN 'created' THEN 'pending'::old_pull_request_status_enum
                WHEN 'in_review' THEN 'pending'::old_pull_request_status_enum
                WHEN 'changes_requested' THEN 'changes_requested'::old_pull_request_status_enum
                WHEN 'disputed' THEN 'pending'::old_pull_request_status_enum
                WHEN 'updated' THEN 'pending'::old_pull_request_status_enum
                WHEN 'approved' THEN 'approved'::old_pull_request_status_enum
            END");

        // Удаляем новый столбец и переименовываем старый
        DB::statement('ALTER TABLE pull_requests DROP COLUMN status');
        DB::statement('ALTER TABLE pull_requests RENAME COLUMN new_status TO status');
        DB::statement("ALTER TABLE pull_requests ALTER COLUMN status SET DEFAULT 'pending'");

        // Удаляем returns_count
        Schema::table('pull_requests', function (Blueprint $table) {
            $table->dropColumn('returns_count');
        });

        // Удаляем новый тип ENUM
        DB::statement('DROP TYPE IF EXISTS pull_request_status_enum CASCADE');
    }
};
