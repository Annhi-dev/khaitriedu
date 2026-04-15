<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chuong_hoc')) {
            return;
        }

        Schema::table('chuong_hoc', function (Blueprint $table) {
            if (! Schema::hasColumn('chuong_hoc', 'session_count')) {
                $table->integer('session_count')->nullable()->after('content');
            }
        });
    }

    public function down(): void
    {
    }
};
