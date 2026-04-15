<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('diem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('dang_ky')->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained('chuong_hoc')->onDelete('cascade');
            $table->decimal('score', 5, 2)->nullable();
            $table->string('grade')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('diem');
    }
};
