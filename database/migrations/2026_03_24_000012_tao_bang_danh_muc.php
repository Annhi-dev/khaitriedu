<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('danh_muc')) {
            Schema::create('danh_muc', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->string('program')->nullable();
                $table->string('level')->nullable();
                $table->string('status')->default('active');
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('mon_hoc') && ! Schema::hasColumn('mon_hoc', 'category_id')) {
            Schema::table('mon_hoc', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->constrained('danh_muc')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mon_hoc') && Schema::hasColumn('mon_hoc', 'category_id')) {
            Schema::table('mon_hoc', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        Schema::dropIfExists('danh_muc');
    }
};