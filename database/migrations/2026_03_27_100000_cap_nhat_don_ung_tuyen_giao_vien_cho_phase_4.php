<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('don_ung_tuyen_giao_vien', function (Blueprint $table) {
            if (! Schema::hasColumn('don_ung_tuyen_giao_vien', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('status');
            }

            if (! Schema::hasColumn('don_ung_tuyen_giao_vien', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('admin_note');
            }
        });

        if (Schema::hasColumn('don_ung_tuyen_giao_vien', 'status') && DB::getDriverName() !== 'sqlite') {
            Schema::table('don_ung_tuyen_giao_vien', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('don_ung_tuyen_giao_vien', function (Blueprint $table) {
            if (Schema::hasColumn('don_ung_tuyen_giao_vien', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }

            if (Schema::hasColumn('don_ung_tuyen_giao_vien', 'admin_note')) {
                $table->dropColumn('admin_note');
            }
        });
    }
};