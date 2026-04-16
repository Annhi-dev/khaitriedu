<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lop_hoc')) {
            return;
        }

        Schema::table('lop_hoc', function (Blueprint $table) {
            if (! Schema::hasColumn('lop_hoc', 'grade_weights')) {
                $table->json('grade_weights')
                    ->nullable()
                    ->after('note');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lop_hoc') || ! Schema::hasColumn('lop_hoc', 'grade_weights')) {
            return;
        }

        Schema::table('lop_hoc', function (Blueprint $table) {
            $table->dropColumn('grade_weights');
        });
    }
};
