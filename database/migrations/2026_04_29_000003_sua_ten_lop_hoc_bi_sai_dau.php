<?php

use App\Models\ClassRoom;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lop_hoc')) {
            return;
        }

        ClassRoom::query()
            ->with('course')
            ->where('name', 'like', '%?%')
            ->chunkById(100, function ($classRooms): void {
                foreach ($classRooms as $classRoom) {
                    if (! $classRoom->course?->title) {
                        continue;
                    }

                    $classRoom->update([
                        'name' => $classRoom->course->title,
                    ]);
                }
            });
    }

    public function down(): void
    {
    }
};
