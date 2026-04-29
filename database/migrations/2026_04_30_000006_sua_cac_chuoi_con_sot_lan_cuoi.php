<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chuong_hoc') || ! Schema::hasTable('schedule_change_requests')) {
            return;
        }

        foreach (DB::table('chuong_hoc')->where('content', 'like', '%?%')->orderBy('id')->get() as $row) {
            $fixed = $this->fixBrokenVietnameseText($row->content);

            if ($fixed !== $row->content) {
                DB::table('chuong_hoc')
                    ->where('id', $row->id)
                    ->update(['content' => $fixed]);
            }
        }

        foreach (DB::table('schedule_change_requests')->where('reason', 'like', '%?%')->orderBy('id')->get() as $row) {
            $fixed = $this->fixBrokenVietnameseText($row->reason);

            if ($fixed !== $row->reason) {
                DB::table('schedule_change_requests')
                    ->where('id', $row->id)
                    ->update(['reason' => $fixed]);
            }
        }
    }

    public function down(): void
    {
    }

    protected function fixBrokenVietnameseText(string $value): string
    {
        $replacements = [
            'C?ng c? l? thuy?t, thu?t ng? và khung kiến thức chung của khóa học.' => 'Củng cố lý thuyết, thuật ngữ và khung kiến thức chung của khóa học.',
            'Áp dụng kiến thức vào t?nh hu?ng, hồ sơ và bài tập thực tế.' => 'Áp dụng kiến thức vào tình huống, hồ sơ và bài tập thực tế.',
            '??i sang ph?ng m?y d? ph?ng ?? t?ng tr?i nghi?m th?c h?nh.' => 'Đổi sang phòng máy dự phòng để tăng trải nghiệm thực hành.',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $value);
    }
};
