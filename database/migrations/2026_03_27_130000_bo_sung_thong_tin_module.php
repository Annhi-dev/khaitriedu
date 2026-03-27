<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chuong_hoc')) {
            return;
        }

        Schema::table('chuong_hoc', function (Blueprint $table) {
            if (! Schema::hasColumn('chuong_hoc', 'duration')) {
                $table->integer('duration')->nullable()->after('content');
            }

            if (! Schema::hasColumn('chuong_hoc', 'status')) {
                $table->string('status')->default('published')->after('duration');
            }
        });

        DB::table('chuong_hoc')->whereNull('status')->update(['status' => 'published']);
    }

    public function down(): void
    {
        // Compatibility migration: do not drop shared columns used by the module management flow.
    }
};