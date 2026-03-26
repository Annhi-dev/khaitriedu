<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('dang_ky', 'schedule')) {
            Schema::table('dang_ky', function (Blueprint $table) {
                $table->string('schedule')->nullable()->after('assigned_teacher_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dang_ky', 'schedule')) {
            Schema::table('dang_ky', function (Blueprint $table) {
                $table->dropColumn('schedule');
            });
        }
    }
};
