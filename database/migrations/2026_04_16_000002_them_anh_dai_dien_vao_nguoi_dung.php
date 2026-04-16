<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('nguoi_dung', 'avatar_path')) {
            Schema::table('nguoi_dung', function (Blueprint $table) {
                $table->string('avatar_path')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nguoi_dung', 'avatar_path')) {
            Schema::table('nguoi_dung', function (Blueprint $table) {
                $table->dropColumn('avatar_path');
            });
        }
    }
};
