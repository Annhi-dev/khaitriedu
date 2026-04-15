<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('danh_muc')) {
            return;
        }

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

        DB::table('danh_muc')->whereNull('status')->update(['status' => 'active']);
    }

    public function down(): void
    {
    }
};