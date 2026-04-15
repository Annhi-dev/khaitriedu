<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->enum('role', ['admin', 'giang_vien', 'hoc_vien'])->default('hoc_vien')->change();
        });
    }

    
    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->string('role')->default('hoc_vien')->change();
        });
    }
};
