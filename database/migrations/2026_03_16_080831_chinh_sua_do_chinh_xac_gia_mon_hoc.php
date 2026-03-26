<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mon_hoc') || !Schema::hasColumn('mon_hoc', 'price')) {
            return;
        }

        Schema::table('mon_hoc', function (Blueprint $table) {
            $table->decimal('price', 13, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('mon_hoc') || !Schema::hasColumn('mon_hoc', 'price')) {
            return;
        }

        Schema::table('mon_hoc', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->change();
        });
    }
};