<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('danh_muc', function (Blueprint $table) {
            if (! Schema::hasColumn('danh_muc', 'program')) {
                $table->string('program')->nullable()->after('image_path');
            }

            if (! Schema::hasColumn('danh_muc', 'level')) {
                $table->string('level')->nullable()->after('program');
            }

            if (! Schema::hasColumn('danh_muc', 'status')) {
                $table->string('status')->default('active')->after('level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('danh_muc', function (Blueprint $table) {
            if (Schema::hasColumn('danh_muc', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('danh_muc', 'level')) {
                $table->dropColumn('level');
            }

            if (Schema::hasColumn('danh_muc', 'program')) {
                $table->dropColumn('program');
            }
        });
    }
};