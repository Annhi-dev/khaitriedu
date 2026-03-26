<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dang_ky', function (Blueprint $table) {
            if (!Schema::hasColumn('dang_ky', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->constrained('mon_hoc')->nullOnDelete()->after('user_id');
            }
            if (Schema::hasColumn('dang_ky', 'course_id')) {
                $table->foreignId('course_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dang_ky', function (Blueprint $table) {
            if (Schema::hasColumn('dang_ky', 'subject_id')) {
                $table->dropForeign(['subject_id']);
                $table->dropColumn('subject_id');
            }
            if (Schema::hasColumn('dang_ky', 'course_id')) {
                $table->foreignId('course_id')->change();
            }
        });
    }
};