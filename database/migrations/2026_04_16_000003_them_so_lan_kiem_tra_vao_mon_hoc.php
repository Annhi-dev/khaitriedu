<?php

use App\Models\MonHoc;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mon_hoc')) {
            return;
        }

        Schema::table('mon_hoc', function (Blueprint $table) {
            if (! Schema::hasColumn('mon_hoc', 'test_count')) {
                $table->unsignedTinyInteger('test_count')
                    ->default(MonHoc::DEFAULT_TEST_COUNT)
                    ->after('duration');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mon_hoc') || ! Schema::hasColumn('mon_hoc', 'test_count')) {
            return;
        }

        Schema::table('mon_hoc', function (Blueprint $table) {
            $table->dropColumn('test_count');
        });
    }
};
