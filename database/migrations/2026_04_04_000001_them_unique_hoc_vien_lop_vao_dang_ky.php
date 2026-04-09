<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dang_ky') || ! Schema::hasColumn('dang_ky', 'lop_hoc_id')) {
            return;
        }

        $duplicates = DB::table('dang_ky')
            ->select('user_id', 'lop_hoc_id', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->whereNotNull('lop_hoc_id')
            ->groupBy('user_id', 'lop_hoc_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('dang_ky')
                ->where('user_id', $duplicate->user_id)
                ->where('lop_hoc_id', $duplicate->lop_hoc_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('dang_ky', function (Blueprint $table) {
            $table->unique(['user_id', 'lop_hoc_id'], 'dang_ky_user_lop_hoc_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('dang_ky')) {
            return;
        }

        Schema::table('dang_ky', function (Blueprint $table) {
            $table->dropUnique('dang_ky_user_lop_hoc_unique');
        });
    }
};

