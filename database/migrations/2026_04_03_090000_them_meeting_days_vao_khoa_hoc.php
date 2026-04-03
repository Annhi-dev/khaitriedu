<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('khoa_hoc', function (Blueprint $table) {
            if (! Schema::hasColumn('khoa_hoc', 'meeting_days')) {
                $table->json('meeting_days')->nullable()->after('day_of_week');
            }
        });
    }

    public function down(): void
    {
        Schema::table('khoa_hoc', function (Blueprint $table) {
            if (Schema::hasColumn('khoa_hoc', 'meeting_days')) {
                $table->dropColumn('meeting_days');
            }
        });
    }
};
