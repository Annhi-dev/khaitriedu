<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mon_hoc')) {
            return;
        }

        Schema::table('mon_hoc', function (Blueprint $table) {
            if (!Schema::hasColumn('mon_hoc', 'price')) {
                $table->decimal('price', 10, 2)->default(0);
            }

            if (!Schema::hasColumn('mon_hoc', 'image')) {
                $table->string('image')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('mon_hoc')) {
            return;
        }

        Schema::table('mon_hoc', function (Blueprint $table) {
            $dropColumns = array_filter(['price', 'image'], fn ($column) => Schema::hasColumn('mon_hoc', $column));
            if ($dropColumns) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};