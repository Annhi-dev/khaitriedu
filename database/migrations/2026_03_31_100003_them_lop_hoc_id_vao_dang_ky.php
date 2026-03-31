<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm lop_hoc_id vào dang_ky để student enroll vào lớp cụ thể
        Schema::table('dang_ky', function (Blueprint $table) {
            if (! Schema::hasColumn('dang_ky', 'lop_hoc_id')) {
                $table->foreignId('lop_hoc_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('lop_hoc')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dang_ky', function (Blueprint $table) {
            if (Schema::hasColumn('dang_ky', 'lop_hoc_id')) {
                $table->dropForeign(['lop_hoc_id']);
                $table->dropColumn('lop_hoc_id');
            }
        });
    }
};
