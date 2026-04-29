<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chuong_hoc') || ! Schema::hasTable('bai_hoc')) {
            return;
        }

        $this->repairTextTable('attendance_records', ['note']);
        $this->repairTextTable('chuong_hoc', ['title', 'content']);
        $this->repairTextTable('bai_hoc', ['title', 'description', 'content']);
        $this->repairTextTable('schedule_change_requests', ['current_schedule', 'reason', 'admin_note']);
    }

    public function down(): void
    {
    }

    protected function repairTextTable(string $table, array $columns): void
    {
        $query = DB::table($table);

        $query->where(function ($builder) use ($columns): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $builder->{$method}($column, 'like', '%?%');
            }
        });

        foreach ($query->orderBy('id')->get() as $row) {
            $updates = [];

            foreach ($columns as $column) {
                if (! property_exists($row, $column) || ! is_string($row->{$column}) || $row->{$column} === '') {
                    continue;
                }

                $fixed = $this->fixBrokenVietnameseText($row->{$column});

                if ($fixed !== $row->{$column}) {
                    $updates[$column] = $fixed;
                }
            }

            if ($updates !== []) {
                DB::table($table)
                    ->where('id', $row->id)
                    ->update($updates);
            }
        }
    }

    protected function fixBrokenVietnameseText(string $value): string
    {
        $replacements = [
            'C?ng c? l? thuy?t, thu?t ng? v? khung ki?n th?c chung c?a kh?a h?c.' => 'Củng cố lý thuyết, thuật ngữ và khung kiến thức chung của khóa học.',
            'N?m quy tr?nh, v?n b?n v? y?u c?u nghi?p v? li?n quan.' => 'Nắm quy trình, văn bản và yêu cầu nghiệp vụ liên quan.',
            '?p d?ng ki?n th?c v?o t?nh hu?ng, h? s? v? b?i t?p th?c t?.' => 'Áp dụng kiến thức vào tình huống, hồ sơ và bài tập thực tế.',
            'X? l? case study v? ho?n thi?n b?i t?p theo chu?n ??u ra.' => 'Xử lý case study và hoàn thiện bài tập theo chuẩn đầu ra.',
            '?n l?i ki?n th?c c?t l?i cho l? tr?nh h?c t?p d?i h?n.' => 'Ôn lại kiến thức cốt lõi cho lộ trình học tập dài hạn.',
            '?n t?p v? ho?n thi?n h? s? cu?i kh?a.' => 'Ôn tập và hoàn thiện hồ sơ cuối khóa.',
            'bu?i' => 'buổi',
            't?t' => 'tốt',
            'Ch? nh?t' => 'Chủ nhật',
            'kh?a h?c' => 'khóa học',
            'h?c ph?n' => 'học phần',
            'c?c' => 'các',
            'tr?ng t?m' => 'trọng tâm',
            '?i s?u' => 'Đi sâu',
            '?ng d?ng' => 'ứng dụng',
            'L?m' => 'Làm',
            'l?m' => 'làm',
            'Ph?ng ' => 'Phòng ',
            'Th? ' => 'Thứ ',
            'v?' => 'và',
            'b?i t?p' => 'bài tập',
            'h?c t?p' => 'học tập',
            'c?t l?i' => 'cốt lõi',
            'd?i h?n' => 'dài hạn',
        ];

        uksort($replacements, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));

        return str_replace(array_keys($replacements), array_values($replacements), $value);
    }
};
