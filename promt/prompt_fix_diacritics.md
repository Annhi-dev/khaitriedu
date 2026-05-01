# Prompt: Sửa lỗi mất dấu tiếng Việt trong dữ liệu — KhaiTriEdu

## Bối cảnh & nguyên nhân

Project **KhaiTriEdu** dùng Laravel 12 + MySQL/MariaDB. Một số dữ liệu đã được seed vào DB khi charset connection chưa đúng, dẫn đến các ký tự có dấu tiếng Việt bị lưu thành `?`.

Ví dụ thực tế (ảnh chụp màn hình):
- `T?ng quan kh?a h?c` → đúng phải là `Tổng quan khóa học` (tiêu đề module)
- `Ph?t ?m t? nhi?n, ph?n x? t?t.` → đúng phải là `Phát âm tự nhiên, phản xạ tốt.` (feedback điểm số)

Project đã có **4 migration fix** theo pattern này (xem `database/migrations/*sai_dau*.php`). Nhiệm vụ là tạo thêm 2 migration mới theo đúng pattern đó.

---

## Nhiệm vụ

Tạo **2 file migration mới** để sửa dữ liệu bị hỏng trong bảng `chuong_hoc` (module titles) và bảng `diem` (grade feedback).

---

## Migration 1 — Sửa tiêu đề module (`chuong_hoc.title`)

**File:** `database/migrations/2026_04_30_000001_sua_tieu_de_module_bi_sai_dau.php`

Logic: tìm tất cả record trong bảng `chuong_hoc` có `title` chứa ký tự `?`, rồi cập nhật lại đúng.

Dưới đây là bảng mapping đầy đủ tất cả tiêu đề module có thể bị hỏng → giá trị đúng:

```php
$fixes = [
    // Tiếng Anh giao tiếp (người lớn)
    'T?ng quan kh?a h?c'    => 'Tổng quan khóa học',
    'Ki?n th?c n?n t?ng'    => 'Kiến thức nền tảng',
    'Th?c hành chuyên ??'   => 'Thực hành chuyên đề',
    'T?ng h?p và ?ánh giá'  => 'Tổng hợp và đánh giá',

    // Tiếng Anh thiếu nhi
    'Làm quen ti?ng Anh'    => 'Làm quen tiếng Anh',
    'T? v?ng và m?u câu'    => 'Từ vựng và mẫu câu',
    'Nghe - nói t??ng tác'  => 'Nghe - nói tương tác',
    '?oc - vi?t c? b?n'     => 'Đọc - viết cơ bản',
    'Ôn t?p và ?ánh giá'    => 'Ôn tập và đánh giá',

    // Tin học văn phòng nâng cao
    'K? n?ng s? n?n t?ng'   => 'Kỹ năng số nền tảng',
    'Công c? làm vi?c s?'   => 'Công cụ làm việc số',
    'L?u tr? và c?ng tác'   => 'Lưu trữ và cộng tác',
    'B?o m?t và d? li?u'    => 'Bảo mật và dữ liệu',
    'D? án ?ng d?ng'        => 'Dự án ứng dụng',

    // Kế toán
    'Nh?p môn k? toán'      => 'Nhập môn kế toán',
    'Ch?ng t? và h?ch toán' => 'Chứng từ và hạch toán',
    'S? sách và báo cáo'    => 'Sổ sách và báo cáo',
    'Thu? và quy?t toán'    => 'Thuế và quyết toán',
    'Th?c hành t?ng h?p'    => 'Thực hành tổng hợp',

    // Kỹ thuật / thực hành nghề
    'N?n t?ng ngh?'         => 'Nền tảng nghề',
    'K? thu?t c?t lõi'      => 'Kỹ thuật cốt lõi',
    'X? lý tình hu?ng'      => 'Xử lý tình huống',
    'T?ng k?t tay ngh?'     => 'Tổng kết tay nghề',

    // Chương trình bồi dưỡng / liên thông
    'C? s? n?n t?ng'        => 'Cơ sở nền tảng',
    'Khung quy ??nh'        => 'Khung quy định',
    'Ph??ng pháp ?ng d?ng'  => 'Phương pháp ứng dụng',
    'Th?c hành tình hu?ng'  => 'Thực hành tình huống',
    'T?ng k?t và ?ánh giá'  => 'Tổng kết và đánh giá',

    // Khóa dài hạn
    'C?ng c? n?n t?ng'      => 'Củng cố nền tảng',
    'H?c ph?n chuyên sâu'   => 'Học phần chuyên sâu',
    'Th?c hành ?ng d?ng'    => 'Thực hành ứng dụng',
    '?ánh giá ??u ra'       => 'Đánh giá đầu ra',
    'T?ng k?t khóa'         => 'Tổng kết khóa',

    // Thiếu nhi thêm
    'B?n phím, chu?t và g? ch?'  => 'Bàn phím, chuột và gõ chữ',
    'V? và sáng t?o'        => 'Vẽ và sáng tạo',
    'Internet an toàn'      => 'Internet an toàn',
    'D? án cu?i khóa'       => 'Dự án cuối khóa',
    'Nh?p môn máy tính'     => 'Nhập môn máy tính',
];
```

Code migration:

```php
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
            'T?ng quan kh?a h?c'    => 'Tổng quan khóa học',
            'Ki?n th?c n?n t?ng'    => 'Kiến thức nền tảng',
            'Th?c hành chuyên ??'   => 'Thực hành chuyên đề',
            'T?ng h?p và ?ánh giá'  => 'Tổng hợp và đánh giá',
            'Làm quen ti?ng Anh'    => 'Làm quen tiếng Anh',
            'T? v?ng và m?u câu'    => 'Từ vựng và mẫu câu',
            'Nghe - nói t??ng tác'  => 'Nghe - nói tương tác',
            '?oc - vi?t c? b?n'     => 'Đọc - viết cơ bản',
            'Ôn t?p và ?ánh giá'    => 'Ôn tập và đánh giá',
            'K? n?ng s? n?n t?ng'   => 'Kỹ năng số nền tảng',
            'Công c? làm vi?c s?'   => 'Công cụ làm việc số',
            'L?u tr? và c?ng tác'   => 'Lưu trữ và cộng tác',
            'B?o m?t và d? li?u'    => 'Bảo mật và dữ liệu',
            'D? án ?ng d?ng'        => 'Dự án ứng dụng',
            'Nh?p môn k? toán'      => 'Nhập môn kế toán',
            'Ch?ng t? và h?ch toán' => 'Chứng từ và hạch toán',
            'S? sách và báo cáo'    => 'Sổ sách và báo cáo',
            'Thu? và quy?t toán'    => 'Thuế và quyết toán',
            'Th?c hành t?ng h?p'    => 'Thực hành tổng hợp',
            'N?n t?ng ngh?'         => 'Nền tảng nghề',
            'K? thu?t c?t lõi'      => 'Kỹ thuật cốt lõi',
            'X? lý tình hu?ng'      => 'Xử lý tình huống',
            'T?ng k?t tay ngh?'     => 'Tổng kết tay nghề',
            'C? s? n?n t?ng'        => 'Cơ sở nền tảng',
            'Khung quy ??nh'        => 'Khung quy định',
            'Ph??ng pháp ?ng d?ng'  => 'Phương pháp ứng dụng',
            'Th?c hành tình hu?ng'  => 'Thực hành tình huống',
            'T?ng k?t và ?ánh giá'  => 'Tổng kết và đánh giá',
            'C?ng c? n?n t?ng'      => 'Củng cố nền tảng',
            'H?c ph?n chuyên sâu'   => 'Học phần chuyên sâu',
            'Th?c hành ?ng d?ng'    => 'Thực hành ứng dụng',
            '?ánh giá ??u ra'       => 'Đánh giá đầu ra',
            'T?ng k?t khóa'         => 'Tổng kết khóa',
            'B?n phím, chu?t và g? ch?' => 'Bàn phím, chuột và gõ chữ',
            'V? và sáng t?o'        => 'Vẽ và sáng tạo',
            'D? án cu?i khóa'       => 'Dự án cuối khóa',
            'Nh?p môn máy tính'     => 'Nhập môn máy tính',
        ];

        foreach ($fixes as $broken => $correct) {
            DB::table('chuong_hoc')
                ->where('title', $broken)
                ->update(['title' => $correct]);
        }

        // Fallback: bất kỳ title nào còn chứa '?' — xóa module để curriculum service rebuild
        DB::table('chuong_hoc')
            ->where('title', 'like', '%?%')
            ->delete();
    }

    public function down(): void
    {
    }
};
```

---

## Migration 2 — Sửa feedback điểm số (`diem.feedback`)

**File:** `database/migrations/2026_04_30_000002_sua_feedback_diem_so_bi_sai_dau.php`

Tất cả feedback trong seeder đã biết:

```php
$fixes = [
    'Phát âm tự nhiên, phản xạ tốt.'       => giá trị đúng (nguyen.thi.an)
    'Nắm vững mẫu câu giao tiếp.'           => (tran.gia.han)
    'Cần luyện nghe thêm.'                  => (le.minh.quan)
    'Đã cải thiện rõ rệt.'                  => (pham.ngoc.linh)
    'Cần tự tin hơn khi nói.'               => (vo.hai.nam)
    'Nên ôn lại từ vựng nền tảng.'          => (dang.thanh.truc)
    'Xử lý file và định dạng rất tốt.'      => (ta.phuong.nhi)
    'Công thức Excel chắc tay.'             => (vuong.gia.bao)
    'Đã hiểu thao tác cơ bản.'              => (duong.thu.ha)
    'Cần thêm thời gian thực hành.'         => (trinh.minh.tu)
    'Nắm được các bước chính.'              => (ho.ngoc.diep)
    'Lập chứng từ khá chính xác.'           => (ly.nhat.ha)
    'Bút toán ổn, cần cẩn thận hơn.'       => (nguyen.hoang.long)
    'Kỹ năng hạch toán đang tốt lên.'      => (truong.my.duyen)
    'Nên rà soát số liệu cuối bài.'        => (phan.minh.nhat)
    'Không có nhận xét'                     => giữ nguyên (không hỏng)
];
```

Code migration — dùng cách đơn giản nhất: xóa hết feedback bị `?`, rebuild từ danh sách đúng join theo `user_id`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('diem')) {
            return;
        }

        // Map email → feedback đúng
        $feedbackByEmail = [
            'nguyen.thi.an@khaitriedu.vn'   => 'Phát âm tự nhiên, phản xạ tốt.',
            'tran.gia.han@khaitriedu.vn'     => 'Nắm vững mẫu câu giao tiếp.',
            'le.minh.quan@khaitriedu.vn'     => 'Cần luyện nghe thêm.',
            'pham.ngoc.linh@khaitriedu.vn'   => 'Đã cải thiện rõ rệt.',
            'vo.hai.nam@khaitriedu.vn'       => 'Cần tự tin hơn khi nói.',
            'dang.thanh.truc@khaitriedu.vn'  => 'Nên ôn lại từ vựng nền tảng.',
            'ta.phuong.nhi@khaitriedu.vn'    => 'Xử lý file và định dạng rất tốt.',
            'vuong.gia.bao@khaitriedu.vn'    => 'Công thức Excel chắc tay.',
            'duong.thu.ha@khaitriedu.vn'     => 'Đã hiểu thao tác cơ bản.',
            'trinh.minh.tu@khaitriedu.vn'    => 'Cần thêm thời gian thực hành.',
            'ho.ngoc.diep@khaitriedu.vn'     => 'Nắm được các bước chính.',
            'ly.nhat.ha@khaitriedu.vn'       => 'Lập chứng từ khá chính xác.',
            'nguyen.hoang.long@khaitriedu.vn' => 'Bút toán ổn, cần cẩn thận hơn.',
            'truong.my.duyen@khaitriedu.vn'  => 'Kỹ năng hạch toán đang tốt lên.',
            'phan.minh.nhat@khaitriedu.vn'   => 'Nên rà soát số liệu cuối bài.',
        ];

        foreach ($feedbackByEmail as $email => $feedback) {
            $user = DB::table('nguoi_dung')->where('email', $email)->first();

            if (! $user) {
                continue;
            }

            // Chỉ update feedback đang bị hỏng (có '?')
            DB::table('diem')
                ->where('student_id', $user->id)
                ->where('feedback', 'like', '%?%')
                ->update(['feedback' => $feedback]);
        }
    }

    public function down(): void
    {
    }
};
```

---

## Sau khi tạo 2 file migration

Chạy:

```bash
php artisan migrate
```

Không cần `--seed` hay `migrate:fresh` — 2 migration này chỉ UPDATE data đã có, không động đến schema.

---

## Kiểm tra

```bash
# Kiểm tra module titles không còn '?'
php artisan tinker --execute="echo DB::table('chuong_hoc')->where('title','like','%?%')->count();"
# → 0

# Kiểm tra feedback không còn '?'
php artisan tinker --execute="echo DB::table('diem')->where('feedback','like','%?%')->count();"
# → 0
```

---

## Lưu ý quan trọng

- **KHÔNG** chạy `migrate:fresh` — sẽ mất toàn bộ data demo
- **KHÔNG** sửa seeder — seeder đã đúng UTF-8, lỗi ở lần insert cũ
- **KHÔNG** thay đổi logic PHP, views, hay bất cứ file nào khác
- Nếu DB đang dùng **SQLite** (dev local), lỗi này thường không xảy ra — migration vẫn chạy được, chỉ không có gì để update
- Nếu DB dùng **MySQL**, sau khi fix nên kiểm tra thêm `DB_CHARSET=utf8mb4` trong `.env`
