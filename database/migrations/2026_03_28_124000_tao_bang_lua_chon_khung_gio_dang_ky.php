<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_registration_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_registration_id')->constrained('slot_registrations')->cascadeOnDelete();
            $table->foreignId('course_time_slot_id')->constrained('course_time_slots')->cascadeOnDelete();
            $table->unsignedTinyInteger('priority');
            $table->timestamps();

            $table->unique(['slot_registration_id', 'course_time_slot_id'], 'slot_reg_choice_unique');
            $table->unique(['slot_registration_id', 'priority'], 'slot_reg_priority_unique');
            $table->index(['course_time_slot_id', 'priority'], 'slot_reg_slot_priority_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_registration_choices');
    }
};
