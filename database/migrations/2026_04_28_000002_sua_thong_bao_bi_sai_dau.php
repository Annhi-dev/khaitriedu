<?php

use App\Models\ThongBao;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            1 => [
                'title' => 'Lớp Tiếng Anh giao tiếp đã đủ 12 học viên',
                'message' => 'Lớp hiện đã đạt sĩ số tối đa tại phòng lý thuyết A1. Hệ thống sẵn sàng chuyển sang trạng thái mở lớp.',
            ],
            2 => [
                'title' => 'Tin học văn phòng có lịch mới cần kiểm tra',
                'message' => 'Một số học viên vừa chọn lại khung giờ phù hợp hơn cho lớp Tin học văn phòng.',
            ],
            3 => [
                'title' => 'Thiết kế Web có yêu cầu đổi phòng',
                'message' => 'Phòng máy của lớp Thiết kế Web đã có đề xuất đổi ca để tăng thời gian thực hành.',
            ],
            4 => [
                'title' => 'Bảng điểm Kế toán thực hành đã cập nhật',
                'message' => 'Điểm giữa khóa của nhóm Kế toán thực hành đã được nhập đầy đủ.',
            ],
            5 => [
                'title' => 'Bạn đã được xếp vào lớp Tiếng Anh giao tiếp',
                'message' => 'Lớp học của bạn đã có lịch cố định, có thể xem ngay trong trang khóa học.',
            ],
            6 => [
                'title' => 'Tin học văn phòng đã có lịch học mới',
                'message' => 'Khung giờ học của lớp Tin học văn phòng đã được chốt trên phòng máy tính 1.',
            ],
            7 => [
                'title' => 'Chứng chỉ Báo cáo thuế sẵn sàng cấp',
                'message' => 'Bạn đã hoàn tất khóa Báo cáo thuế và đủ điều kiện nhận chứng chỉ.',
            ],
            8 => [
                'title' => 'Đang chờ ghép lớp cuối tuần',
                'message' => 'Hệ thống đã ghi nhận mong muốn học cuối tuần của bạn, vui lòng chờ admin sắp xếp.',
            ],
            9 => [
                'title' => 'Có yêu cầu xin phép nghỉ mới',
                'message' => 'Học viên Nguyễn Thị An đã gửi xin phép cho lớp KhaiTriEdu 2026 - Lập trình Python cơ bản (Phòng Thực hành 201) vào ngày 17/04/2026.',
            ],
            10 => [
                'title' => 'Yêu cầu xin phép đã được ghi nhận',
                'message' => 'Giảng viên đã ghi nhận yêu cầu xin phép của bạn cho lớp KhaiTriEdu 2026 - Lập trình Python cơ bản (Phòng Thực hành 201).',
            ],
            11 => [
                'title' => 'Yêu cầu xin phép đã được chấp nhận',
                'message' => 'Giảng viên đã chấp nhận yêu cầu xin phép của bạn cho lớp KhaiTriEdu 2026 - Lập trình Python cơ bản (Phòng Thực hành 201).',
            ],
            12 => [
                'title' => 'Lớp đang chờ mở',
                'message' => 'Bạn đã được ghép vào Bồi dưỡng giáo viên phổ thông - Khóa học 1. Hiện lớp có 1/5 học viên. Admin sẽ chốt ngày bắt đầu và ngày kết thúc khi lớp đủ người.',
            ],
        ];

        foreach ($rows as $id => $payload) {
            ThongBao::query()
                ->whereKey($id)
                ->update($payload);
        }
    }

    public function down(): void
    {
        $rows = [
            1 => [
                'title' => 'Lớp Tiếng Anh giao tiếp đã đủ 12 học viên',
                'message' => 'Lớp hiện đã đạt sĩ số tối đa tại phòng lý thuyết A1. Hệ thống sẵn sàng chuyển sang trạng thái mở lớp.',
            ],
            2 => [
                'title' => 'Tin học văn phòng có lịch mới cần kiểm tra',
                'message' => 'Một số học viên vừa chọn lại khung giờ phù hợp hơn cho lớp Tin học văn phòng.',
            ],
            3 => [
                'title' => 'Thiết kế Web có yêu cầu đổi phòng',
                'message' => 'Phòng máy của lớp Thiết kế Web đã có đề xuất đổi ca để tăng thời gian thực hành.',
            ],
            4 => [
                'title' => 'Bảng điểm Kế toán thực hành đã cập nhật',
                'message' => 'Điểm giữa khóa của nhóm Kế toán thực hành đã được nhập đầy đủ.',
            ],
            5 => [
                'title' => 'Bạn đã được xếp vào lớp Tiếng Anh giao tiếp',
                'message' => 'Lớp học của bạn đã có lịch cố định, có thể xem ngay trong trang khóa học.',
            ],
            6 => [
                'title' => 'Tin học văn phòng đã có lịch học mới',
                'message' => 'Khung giờ học của lớp Tin học văn phòng đã được chốt trên phòng máy tính 1.',
            ],
            7 => [
                'title' => 'Chứng chỉ Báo cáo thuế sẵn sàng cấp',
                'message' => 'Bạn đã hoàn tất khóa Báo cáo thuế và đủ điều kiện nhận chứng chỉ.',
            ],
            8 => [
                'title' => 'Đang chờ ghép lớp cuối tuần',
                'message' => 'Hệ thống đã ghi nhận mong muốn học cuối tuần của bạn, vui lòng chờ admin sắp xếp.',
            ],
            9 => [
                'title' => 'Có yêu cầu xin phép nghỉ mới',
                'message' => 'Học viên Nguyễn Thị An đã gửi xin phép cho lớp KhaiTriEdu 2026 - Lập trình Python cơ bản (Phòng Thực hành 201) vào ngày 17/04/2026.',
            ],
            10 => [
                'title' => 'Yêu cầu xin phép đã được ghi nhận',
                'message' => 'Giảng viên đã ghi nhận yêu cầu xin phép của bạn cho lớp KhaiTriEdu 2026 - Lập trình Python cơ bản (Phòng Thực hành 201).',
            ],
            11 => [
                'title' => 'Yêu cầu xin phép đã được chấp nhận',
                'message' => 'Giảng viên đã chấp nhận yêu cầu xin phép của bạn cho lớp KhaiTriEdu 2026 - Lập trình Python cơ bản (Phòng Thực hành 201).',
            ],
            12 => [
                'title' => 'Lớp đang chờ mở',
                'message' => 'Bạn đã được ghép vào Bồi dưỡng giáo viên phổ thông - Khóa học 1. Hiện lớp có 1/5 học viên. Admin sẽ chốt ngày bắt đầu và ngày kết thúc khi lớp đủ người.',
            ],
        ];

        foreach ($rows as $id => $payload) {
            ThongBao::query()
                ->whereKey($id)
                ->update($payload);
        }
    }
};
