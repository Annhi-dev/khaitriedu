<?php

use App\Models\Announcement;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            1 => [
                'title' => 'Khai giảng lớp Tiếng Anh giao tiếp tháng 4',
                'message' => 'Lớp Tiếng Anh giao tiếp buổi tối đã sẵn sàng, học viên có thể theo dõi lịch học và tài liệu ngay trên hệ thống.',
            ],
            2 => [
                'title' => 'Mở đăng ký khóa Tin học văn phòng',
                'message' => 'Khóa Tin học văn phòng có lịch học tối 3 buổi/tuần, phù hợp cho học viên đi làm muốn nâng cao kỹ năng văn phòng.',
            ],
            3 => [
                'title' => 'Lịch học Kế toán thực hành đã được cập nhật',
                'message' => 'Phòng Đào tạo đã chốt lịch học và phân phòng cho lớp Kế toán thực hành khóa hiện tại.',
            ],
            4 => [
                'title' => 'Thông báo lớp Thiết kế Web',
                'message' => 'Lớp Thiết kế Web tuần này chuyển sang phòng máy tính 301 để thuận tiện thực hành.',
            ],
            5 => [
                'title' => 'Bồi dưỡng giáo viên phổ thông mở thêm nhóm tối',
                'message' => 'Trung tâm mở thêm nhóm buổi tối cho lớp bồi dưỡng giáo viên phổ thông để hỗ trợ học viên đang công tác.',
            ],
            6 => [
                'title' => 'Học viên đủ điều kiện nhận chứng chỉ đợt 1',
                'message' => 'Danh sách học viên hoàn thành khóa học và đủ điều kiện nhận chứng chỉ đã được cập nhật trong hệ thống.',
            ],
        ];

        foreach ($rows as $id => $payload) {
            Announcement::query()
                ->whereKey($id)
                ->update($payload);
        }
    }

    public function down(): void
    {
        $rows = [
            1 => [
                'title' => 'Khai gi?ng l?p Ti?ng Anh giao ti?p th?ng 4',
                'message' => 'L?p Ti?ng Anh giao ti?p bu?i t?i ?? s?n s?ng, h?c vi?n c? th? theo d?i l?ch h?c v? t?i li?u ngay tr?n h? th?ng.',
            ],
            2 => [
                'title' => 'M? ??ng k? kh?a Tin h?c v?n ph?ng',
                'message' => 'Kh?a Tin h?c v?n ph?ng c? l?ch h?c t?i 3 bu?i/tu?n, ph? h?p cho h?c vi?n ?i l?m mu?n n?ng cao k? n?ng v?n ph?ng.',
            ],
            3 => [
                'title' => 'L?ch h?c K? to?n th?c h?nh ?? ???c c?p nh?t',
                'message' => 'Ph?ng ??o t?o ?? ch?t l?ch h?c v? ph?n ph?ng cho l?p K? to?n th?c h?nh kh?a hi?n t?i.',
            ],
            4 => [
                'title' => 'Th?ng b?o l?p Thi?t k? Web',
                'message' => 'L?p Thi?t k? Web tu?n n?y chuy?n sang ph?ng m?y t?nh 301 ?? thu?n ti?n th?c h?nh.',
            ],
            5 => [
                'title' => 'B?i d??ng gi?o vi?n ph? th?ng m? th?m nh?m t?i',
                'message' => 'Trung t?m m? th?m nh?m bu?i t?i cho l?p b?i d??ng gi?o vi?n ph? th?ng ?? h? tr? h?c vi?n ?ang c?ng t?c.',
            ],
            6 => [
                'title' => 'H?c vi?n ?? ?i?u ki?n nh?n ch?ng ch? ??t 1',
                'message' => 'Danh s?ch h?c vi?n ho?n th?nh kh?a h?c v? ?? ?i?u ki?n nh?n ch?ng ch? ?? ???c c?p nh?t trong h? th?ng.',
            ],
        ];

        foreach ($rows as $id => $payload) {
            Announcement::query()
                ->whereKey($id)
                ->update($payload);
        }
    }
};
