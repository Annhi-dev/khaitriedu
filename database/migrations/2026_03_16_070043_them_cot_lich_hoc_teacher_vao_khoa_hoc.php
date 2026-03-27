<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('khoa_hoc')) {
            return;
        }

        Schema::table('khoa_hoc', function (Blueprint $table) {
            if (!Schema::hasColumn('khoa_hoc', 'schedule')) {
                $table->string('schedule')->nullable();
            }

            if (!Schema::hasColumn('khoa_hoc', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('khoa_hoc')) {
            return;
        }

        Schema::table('khoa_hoc', function (Blueprint $table) {
            if (Schema::hasColumn('khoa_hoc', 'teacher_id')) {
                $table->dropForeign(['teacher_id']);
            }

            $dropColumns = array_filter(['schedule', 'teacher_id'], fn ($column) => Schema::hasColumn('khoa_hoc', $column));
            if ($dropColumns) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
