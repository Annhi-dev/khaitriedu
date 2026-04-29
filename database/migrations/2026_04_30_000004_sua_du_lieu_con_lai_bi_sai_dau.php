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

        $this->repairCurriculumTables();
        $this->repairTextTable('attendance_records', ['note']);
        $this->repairGrades();
        $this->repairTextTable('don_ung_tuyen_giao_vien', ['name', 'experience', 'message', 'admin_note', 'rejection_reason']);
        $this->repairTextTable('phong_ban', ['name', 'description']);
        $this->repairTextTable('slot_registrations', ['note']);
        $this->repairTextTable('schedule_change_requests', ['current_schedule', 'reason', 'admin_note']);
        $this->repairTextTable('teacher_evaluations', ['comments']);
        $this->repairTextTable('dang_ky', ['schedule']);
        $this->repairTextTable('khoa_hoc', ['schedule']);

        DB::table('danh_muc')
            ->where('slug', 'the-duc-the-thao')
            ->update([
                'name' => 'Thể dục thể thao',
            ]);
    }

    public function down(): void
    {
    }

    protected function repairCurriculumTables(): void
    {
        $this->repairTextTable('chuong_hoc', ['title', 'content']);
        $this->repairTextTable('bai_hoc', ['title', 'description', 'content']);
    }

    protected function repairGrades(): void
    {
        foreach (DB::table('diem')->where('test_name', 'like', '%?%')->orderBy('id')->get() as $row) {
            if (! is_string($row->test_name) || $row->test_name === '') {
                continue;
            }

            $fixedTestName = $this->fixBrokenVietnameseText($row->test_name);

            if ($fixedTestName === $row->test_name) {
                continue;
            }

            $duplicateExists = DB::table('diem')
                ->where('id', '!=', $row->id)
                ->where('enrollment_id', $row->enrollment_id)
                ->where('module_id', $row->module_id)
                ->where('class_room_id', $row->class_room_id)
                ->where('student_id', $row->student_id)
                ->where('teacher_id', $row->teacher_id)
                ->where('score', $row->score)
                ->where('grade', $row->grade)
                ->where('feedback', $row->feedback)
                ->where('test_name', $fixedTestName)
                ->exists();

            if ($duplicateExists) {
                DB::table('diem')
                    ->where('id', $row->id)
                    ->delete();

                continue;
            }

            DB::table('diem')
                ->where('id', $row->id)
                ->update([
                    'test_name' => $fixedTestName,
                ]);
        }
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
            'Ph??ng ph?p ?ng d?ng' => 'Phương pháp ứng dụng',
            'Th?c h?nh t?nh hu?ng' => 'Thực hành tình huống',
            'C?ng c? n?n t?ng' => 'Củng cố nền tảng',
            'H?c ph?n chuy?n s?u' => 'Học phần chuyên sâu',
            'Th?c h?nh ?ng d?ng' => 'Thực hành ứng dụng',
            'T?ng k?t kh?a' => 'Tổng kết khóa',
            'Khung quy ??nh' => 'Khung quy định',
            'C? s? n?n t?ng' => 'Cơ sở nền tảng',
            'Ki?n th?c n?n t?ng' => 'Kiến thức nền tảng',
            'Nh?p m?n' => 'Nhập môn',
            'Th?c h?nh 1' => 'Thực hành 1',
            'Th?c h?nh 2' => 'Thực hành 2',
            'Th? ' => 'Thứ ',
            'Bu?i' => 'Buổi',
            'N?i dung' => 'Nội dung',
            'thu?c' => 'thuộc',
            't?p trung' => 'tập trung',
            'v?o' => 'vào',
            'v?' => 'và',
            'c?a' => 'của',
            'L?m b?i ki?m tra m? ph?ng' => 'Làm bài kiểm tra mô phỏng',
            'ch?a l?i' => 'chữa lỗi',
            'ch?t k? n?ng' => 'chốt kỹ năng',
            'c?n c?i thi?n' => 'cần cải thiện',
            'N?m quy tr?nh' => 'Nắm quy trình',
            'v?n b?n' => 'văn bản',
            'y?u c?u' => 'yêu cầu',
            'nghi?p v?' => 'nghiệp vụ',
            'li?n quan' => 'liên quan',
            '?p d?ng' => 'Áp dụng',
            'ki?n th?c' => 'kiến thức',
            'h? s?' => 'hồ sơ',
            'b?i t?p' => 'bài tập',
            'th?c t?' => 'thực tế',
            'X? l?' => 'Xử lý',
            'ho?n thi?n' => 'hoàn thiện',
            'chu?n ??u ra' => 'chuẩn đầu ra',
            '?n l?i' => 'Ôn lại',
            'c?t l?i' => 'cốt lõi',
            'l? tr?nh' => 'lộ trình',
            'd?i h?n' => 'dài hạn',
            'cu?i kh?a' => 'cuối khóa',
            'h?c t?p' => 'học tập',
            'ch??ng tr?nh' => 'chương trình',
            '??i s?u' => 'Đi sâu',
            '?n t?p' => 'Ôn tập',
            'm? ph?ng' => 'mô phỏng',
            'c?u tr?c' => 'cấu trúc',
            'm?c ti?u' => 'mục tiêu',
            'L?m vi?c nh?m' => 'Làm việc nhóm',
            'Ph? tr?ch' => 'Phụ trách',
            'chuy?n m?n' => 'chuyên môn',
            'gi?ng d?y' => 'giảng dạy',
            '??nh gi?' => 'đánh giá',
            'ki?m ??nh' => 'kiểm định',
            'n?i b?' => 'nội bộ',
            'V?n h?nh' => 'Vận hành',
            't?i nguy?n' => 'tài nguyên',
            'k? thu?t' => 'kỹ thuật',
            'h? tr?' => 'hỗ trợ',
            'Tham gia ??y ??' => 'Tham gia đầy đủ',
            '??n tr? 10 ph?t do k?t xe' => 'Đến trễ 10 phút do kẹt xe',
            '??n tr? 7 ph?t' => 'Đến trễ 7 phút',
            'C? m?t s?m 5 ph?t' => 'Có mặt sớm 5 phút',
            'C? xin ph?p tr??c' => 'Có xin phép trước',
            'Ho?n th?nh b?i t?p mi?ng' => 'Hoàn thành bài tập miệng',
            'V?ng kh?ng b?o tr??c' => 'Vắng không báo trước',
            'T?ch c?c ph?t bi?u' => 'Tích cực phát biểu',
            'Chu?n b? b?i t?t' => 'Chuẩn bị bài tốt',
            'Ngh? c? ph?p v? c?ng t?c' => 'Nghỉ có phép vì công tác',
            'Ho?n th?nh ?? ho?t ??ng' => 'Hoàn thành đủ hoạt động',
            'Th?c h?nh Word t?t' => 'Thực hành Word tốt',
            'Ho?n th?nh b?i Excel' => 'Hoàn thành bài Excel',
            'V?o l?p mu?n' => 'Vào lớp muộn',
            'L?m b?i th?c h?nh ??y ??' => 'Làm bài thực hành đầy đủ',
            'Thao t?c ph?n m?m th?nh th?o, n?p b?i ??ng h?n v? th?i ?? t?ch c?c' => 'Thao tác phần mềm thành thạo, nộp bài đúng hạn và thái độ tích cực',
            'C? ti?n b? qua t?ng bu?i, c?n ch? ??ng h?i th?m khi g?p l?i kh?' => 'Có tiến bộ qua từng buổi, cần chủ động hỏi thêm khi gặp lỗi khó',
            'N?m ch?c quy tr?nh nghi?p v?, b?i l?m ch?nh x?c v? c? tinh th?n h?c t?p cao' => 'Nắm chắc quy trình nghiệp vụ, bài làm chính xác và có tinh thần học tập cao',
            'Minh H?' => 'Minh Hà',
            'Ph?m Mai Trang' => 'Phạm Mai Trang',
            'Tr?n Qu?c ??t' => 'Trần Quốc Đạt',
            '8 n?m gi?ng d?y ti?ng Anh giao ti?p v? luy?n thi ??u ra' => '8 năm giảng dạy tiếng Anh giao tiếp và luyện thi đầu ra',
            'Mong ???c tham gia c?c l?p bu?i t?i v? cu?i tu?n' => 'Mong được tham gia các lớp buổi tối và cuối tuần',
            '5 n?m k? to?n doanh nghi?p v? d?ch v? thu?' => '5 năm kế toán doanh nghiệp và dịch vụ thuế',
            'C? th? b? sung h? s? s? ph?m n?u c?n' => 'Có thể bổ sung hồ sơ sư phạm nếu cần',
            'Vui l?ng b? sung ch?ng ch? s? ph?m tr??c khi x?t duy?t ti?p' => 'Vui lòng bổ sung chứng chỉ sư phạm trước khi xét duyệt tiếp',
            '3 n?m h? tr? ??o t?o n?i b?' => '3 năm hỗ trợ đào tạo nội bộ',
            'Mu?n ph?t tri?n sang m?ng gi?ng d?y b?n h?ng' => 'Muốn phát triển sang mảng giảng dạy bán hàng',
            'Ch?a ??p ?ng y?u c?u kinh nghi?m ??ng l?p' => 'Chưa đáp ứng yêu cầu kinh nghiệm đứng lớp',
            'Ph?ng ??o t?o' => 'Phòng Đào tạo',
            'Ph?ng Kh?o th? - Ch?t l??ng' => 'Phòng Khảo thí - Chất lượng',
            'Ph?ng C?ng ngh? gi?o d?c' => 'Phòng Công nghệ giáo dục',
            'Ph? tr?ch chuy?n m?n ??o t?o v? ph?n c?ng gi?ng d?y.' => 'Phụ trách chuyên môn đào tạo và phân công giảng dạy.',
            'Theo d?i ch?t l??ng gi?ng d?y, ??nh gi? v? ki?m ??nh n?i b?.' => 'Theo dõi chất lượng giảng dạy, đánh giá và kiểm định nội bộ.',
            'V?n h?nh n?n t?ng, t?i nguy?n s? v? h? tr? k? thu?t h?c t?p.' => 'Vận hành nền tảng, tài nguyên số và hỗ trợ kỹ thuật học tập.',
            'th? d?c th? thao' => 'Thể dục thể thao',
            'B?i ki?m tra gi?a kh?a' => 'Bài kiểm tra giữa khóa',
            'B?i ki?m tra Word v? Excel' => 'Bài kiểm tra Word và Excel',
            'B?i th?c h?nh ch?ng t?' => 'Bài thực hành chứng từ',
            '??i ca sang ph?ng m?y d? ph?ng ?? t?ng tr?i nghi?m th?c h?nh' => 'Đổi ca sang phòng máy dự phòng để tăng trải nghiệm thực hành',
            '?? xu?t chuy?n sang ca cu?i tu?n nh?ng ph?ng m?y ?? k?n l?ch' => 'Đề xuất chuyển sang ca cuối tuần nhưng phòng máy đã kín lịch',
            '?? xu?t chuy?n sang ph?ng m?y kh?c nh?ng l?ch ph?ng ?? k?n' => 'Đề xuất chuyển sang phòng máy khác nhưng lịch phòng đã kín',
            '?? duy?t theo ?? xu?t c?a gi?ng vi?n' => 'Đã duyệt theo đề xuất của giảng viên',
            'Gi? nguy?n l?ch c? ?? kh?ng ?nh h??ng l?p kh?c' => 'Giữ nguyên lịch cũ để không ảnh hưởng lớp khác',
            'Mu?n h?c t?i th? 2, 4, 6 ?? d? s?p x?p c?ng vi?c' => 'Muốn học tối thứ 2, 4, 6 để dễ sắp xếp công việc',
            '?? ghi nh?n nhu c?u h?c bu?i t?i sau gi? l?m' => 'Đã ghi nhận nhu cầu học buổi tối sau giờ làm',
            '?? x?p v?o nh?m h?c t?i trong tu?n' => 'Đã xếp vào nhóm học tối trong tuần',
            'C?n ch?n l?i v? tr?ng ca v?i c?ng vi?c hi?n t?i' => 'Cần chọn lại vì trùng ca với công việc hiện tại',
            'Ch?a ?? ?i?u ki?n ??u v?o n?n t?m t? ch?i' => 'Chưa đủ điều kiện đầu vào nên tạm từ chối',
            'Ch? ?? 5 h?c vi?n ?? m? l?p' => 'Chờ đủ 5 học viên để mở lớp',
        ];

        uksort($replacements, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));

        return str_replace(array_keys($replacements), array_values($replacements), $value);
    }
};
