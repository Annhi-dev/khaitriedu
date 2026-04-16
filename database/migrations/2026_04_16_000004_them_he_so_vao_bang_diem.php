<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('diem')) {
            return;
        }

        Schema::table('diem', function (Blueprint $table) {
            if (! Schema::hasColumn('diem', 'weight')) {
                $table->unsignedTinyInteger('weight')
                    ->default(1)
                    ->after('score');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('diem') || ! Schema::hasColumn('diem', 'weight')) {
            return;
        }

        Schema::table('diem', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
