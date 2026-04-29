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
                'title' => 'L?p Ti?ng Anh giao ti?p ?? ?? 12 h?c vi?n',
                'message' => 'L?p hi?n ?? ??t s? s? t?i ?a t?i ph?ng l? thuy?t A1. H? th?ng s?n s?ng chuy?n sang tr?ng th?i m? l?p.',
            ],
            2 => [
                'title' => 'Tin h?c v?n ph?ng c? l?ch m?i c?n ki?m tra',
                'message' => 'M?t s? h?c vi?n v?a ch?n l?i khung gi? ph? h?p h?n cho l?p Tin h?c v?n ph?ng.',
            ],
            3 => [
                'title' => 'Thi?t k? Web c? y?u c?u ??i ph?ng',
                'message' => 'Ph?ng m?y c?a l?p Thi?t k? Web ?? c? ?? xu?t ??i ca ?? t?ng th?i gian th?c h?nh.',
            ],
            4 => [
                'title' => 'B?ng ?i?m K? to?n th?c h?nh ?? c?p nh?t',
                'message' => '?i?m gi?a kh?a c?a nh?m K? to?n th?c h?nh ?? ???c nh?p ??y ??.',
            ],
            5 => [
                'title' => 'B?n ?? ???c x?p v?o l?p Ti?ng Anh giao ti?p',
                'message' => 'L?p h?c c?a b?n ?? c? l?ch c? ??nh, c? th? xem ngay trong trang kh?a h?c.',
            ],
            6 => [
                'title' => 'Tin h?c v?n ph?ng ?? c? l?ch h?c m?i',
                'message' => 'Khung gi? h?c c?a l?p Tin h?c v?n ph?ng ?? ???c ch?t tr?n ph?ng m?y t?nh 1.',
            ],
            7 => [
                'title' => 'Ch?ng ch? B?o c?o thu? s?n s?ng c?p',
                'message' => 'B?n ?? ho?n t?t kh?a B?o c?o thu? v? ?? ?i?u ki?n nh?n ch?ng ch?.',
            ],
            8 => [
                'title' => '?ang ch? gh?p l?p cu?i tu?n',
                'message' => 'H? th?ng ?? ghi nh?n mong mu?n h?c cu?i tu?n c?a b?n, vui l?ng ch? admin s?p x?p.',
            ],
            9 => [
                'title' => 'C? y?u c?u xin ph?p ngh? m?i',
                'message' => 'H?c vi?n Nguy?n Th? An ?? g?i xin ph?p cho l?p KhaiTriEdu 2026 - L?p tr?nh Python c? b?n (Ph?ng Th?c h?nh 201) v?o ng?y 17/04/2026.',
            ],
            10 => [
                'title' => 'Y?u c?u xin ph?p ?? ???c ghi nh?n',
                'message' => 'Gi?ng vi?n ?? ghi nh?n y?u c?u xin ph?p c?a b?n cho l?p KhaiTriEdu 2026 - L?p tr?nh Python c? b?n (Ph?ng Th?c h?nh 201).',
            ],
            11 => [
                'title' => 'Y?u c?u xin ph?p ?? ???c ch?p nh?n',
                'message' => 'Gi?ng vi?n ?? ch?p nh?n y?u c?u xin ph?p c?a b?n cho l?p KhaiTriEdu 2026 - L?p tr?nh Python c? b?n (Ph?ng Th?c h?nh 201).',
            ],
            12 => [
                'title' => 'L?p ?ang ch? m?',
                'message' => 'B?n ?? ???c gh?p v?o B?i d??ng gi?o vi?n ph? th?ng - Kh?a h?c 1. Hi?n l?p c? 1/5 h?c vi?n. Admin s? ch?t ng?y b?t ??u v? ng?y k?t th?c khi l?p ?? ng??i.',
            ],
        ];

        foreach ($rows as $id => $payload) {
            ThongBao::query()
                ->whereKey($id)
                ->update($payload);
        }
    }
};
