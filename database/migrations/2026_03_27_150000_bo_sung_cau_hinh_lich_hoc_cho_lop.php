<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('khoa_hoc', function (Blueprint $table) {
            if (! Schema::hasColumn('khoa_hoc', 'day_of_week')) {
                $table->string('day_of_week', 20)->nullable();
            }

            if (! Schema::hasColumn('khoa_hoc', 'start_date')) {
                $table->date('start_date')->nullable();
            }

            if (! Schema::hasColumn('khoa_hoc', 'end_date')) {
                $table->date('end_date')->nullable();
            }

            if (! Schema::hasColumn('khoa_hoc', 'start_time')) {
                $table->time('start_time')->nullable();
            }

            if (! Schema::hasColumn('khoa_hoc', 'end_time')) {
                $table->time('end_time')->nullable();
            }

            if (! Schema::hasColumn('khoa_hoc', 'capacity')) {
                $table->unsignedInteger('capacity')->default(20);
            }

            if (! Schema::hasColumn('khoa_hoc', 'status')) {
                $table->string('status', 20)->default('draft');
            }
        });
    }

    public function down(): void
    {
        Schema::table('khoa_hoc', function (Blueprint $table) {
            $columns = [];

            foreach (['day_of_week', 'start_date', 'end_date', 'start_time', 'end_time', 'capacity', 'status'] as $column) {
                if (Schema::hasColumn('khoa_hoc', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};