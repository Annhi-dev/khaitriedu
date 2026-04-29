<?php

use App\Models\ThongBaoChung;
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
            ThongBaoChung::query()
                ->whereKey($id)
                ->update($payload);
        }
    }

    public function down(): void
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
            ThongBaoChung::query()
                ->whereKey($id)
                ->update($payload);
        }
    }
};
