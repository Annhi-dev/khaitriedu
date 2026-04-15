<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mon_hoc')) {
            return;
        }

        Schema::table('mon_hoc', function (Blueprint $table) {
            if (! Schema::hasColumn('mon_hoc', 'duration')) {
                $table->integer('duration')->nullable()->after('price');
            }

            if (! Schema::hasColumn('mon_hoc', 'status')) {
                $table->string('status')->default('open')->after('duration');
            }
        });

        DB::table('mon_hoc')->whereNull('status')->update(['status' => 'open']);
    }

    public function down(): void
    {
    }
};