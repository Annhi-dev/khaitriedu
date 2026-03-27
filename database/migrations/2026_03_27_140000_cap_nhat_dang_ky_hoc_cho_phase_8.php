<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dang_ky', function (Blueprint $table) {
            if (! Schema::hasColumn('dang_ky', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('note')->constrained('nguoi_dung')->nullOnDelete();
            }

            if (! Schema::hasColumn('dang_ky', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE dang_ky MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('dang_ky', function (Blueprint $table) {
            if (Schema::hasColumn('dang_ky', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
                $table->dropColumn('reviewed_by');
            }

            if (Schema::hasColumn('dang_ky', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
        });
    }
};