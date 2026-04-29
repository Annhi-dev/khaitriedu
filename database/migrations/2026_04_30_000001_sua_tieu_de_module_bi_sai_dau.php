<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chuong_hoc')) {
            return;
        }

        $fixes = [
            'T?ng quan kh?a h?c' => 'Tổng quan khóa học',
            'Ki?n th?c n?n t?ng' => 'Kiến thức nền tảng',
            'Th?c h?nh chuy?n ??' => 'Thực hành chuyên đề',
            'T?ng h?p v? ?nh gi?' => 'Tổng hợp và đánh giá',
            'L?m quen ti?ng Anh' => 'Làm quen tiếng Anh',
            'T? v?ng v? m?u c?u' => 'Từ vựng và mẫu câu',
            'Nghe - n?i t??ng t?c' => 'Nghe - nói tương tác',
            '?c - vi?t c? b?n' => 'Đọc - viết cơ bản',
            'Ôn t?p v? ?nh gi?' => 'Ôn tập và đánh giá',
            'K? n?ng s? n?n t?ng' => 'Kỹ năng số nền tảng',
            'C?ng c? l?m vi?c s?' => 'Công cụ làm việc số',
            'L?u tr? v? c?ng t?c' => 'Lưu trữ và cộng tác',
            'B?o m?t v? d? li?u' => 'Bảo mật và dữ liệu',
            'D? án ?ng d?ng' => 'Dự án ứng dụng',
            'Nh?p m?n k? to?n' => 'Nhập môn kế toán',
            'Ch?ng t? v? h?ch to?n' => 'Chứng từ và hạch toán',
            'S? s?ch v? b?o c?o' => 'Sổ sách và báo cáo',
            'Thu? v? quy?t to?n' => 'Thuế và quyết toán',
            'Th?c h?nh t?ng h?p' => 'Thực hành tổng hợp',
            'N?n t?ng ngh?' => 'Nền tảng nghề',
            'K? thu?t c?t l?i' => 'Kỹ thuật cốt lõi',
            'X? l? t?nh hu?ng' => 'Xử lý tình huống',
            'T?ng k?t tay ngh?' => 'Tổng kết tay nghề',
            'C? s? n?n t?ng' => 'Cơ sở nền tảng',
            'Khung quy ??nh' => 'Khung quy định',
            'Ph??ng ph?p ?ng d?ng' => 'Phương pháp ứng dụng',
            'Th?c h?nh t?nh hu?ng' => 'Thực hành tình huống',
            'T?ng k?t v? ?nh gi?' => 'Tổng kết và đánh giá',
            'C?ng c? n?n t?ng' => 'Củng cố nền tảng',
            'H?c ph?n chuy?n s?u' => 'Học phần chuyên sâu',
            'Th?c h?nh ?ng d?ng' => 'Thực hành ứng dụng',
            '?nh gi? ??u ra' => 'Đánh giá đầu ra',
            'T?ng k?t kh?a' => 'Tổng kết khóa',
            'B?n ph?m, chu?t v? g? ch?' => 'Bàn phím, chuột và gõ chữ',
            'V? v? s?ng t?o' => 'Vẽ và sáng tạo',
            'Internet an to?n' => 'Internet an toàn',
            'D? án cu?i kh?a' => 'Dự án cuối khóa',
            'Nh?p m?n m?y t?nh' => 'Nhập môn máy tính',
        ];

        foreach ($fixes as $broken => $correct) {
            DB::table('chuong_hoc')
                ->where('title', $broken)
                ->update(['title' => $correct]);
        }

        DB::table('chuong_hoc')
            ->where('title', 'like', '%?%')
            ->delete();
    }

    public function down(): void
    {
    }
};
