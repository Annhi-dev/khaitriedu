# Prompt: Đổi tên Model class sang tiếng Việt — KhaiTriEdu

## Bối cảnh

Đây là project Laravel 12 tên **KhaiTriEdu** — hệ thống quản lý trung tâm đào tạo.
Project đang chạy bình thường. Yêu cầu chỉ đổi **tên class PHP** của các Model (và file tương ứng) sang tiếng Việt (snake_case, không dấu), đồng bộ với tên bảng database và file đã có.

**KHÔNG thay đổi bất cứ thứ gì khác: logic, database, migrations, routes, views, config.**

---

## Mục tiêu

Sau khi đổi xong, mọi tham chiếu trong codebase phải dùng tên class tiếng Việt mới.  
Project phải `php artisan test` xanh toàn bộ như trước khi sửa.

---

## Bảng mapping — Tên cũ → Tên mới

| Tên class cũ (English) | Tên class mới (Tiếng Việt) | File cũ | File mới |
|---|---|---|---|
| `User` | `NguoiDung` | `app/Models/nguoi_dung.php` | `app/Models/NguoiDung.php` |
| `Course` | `KhoaHoc` | `app/Models/khoa_hoc.php` | `app/Models/KhoaHoc.php` |
| `ClassRoom` | `LopHoc` | `app/Models/lop_hoc.php` | `app/Models/LopHoc.php` |
| `Enrollment` | `GhiDanh` | `app/Models/ghi_danh.php` | `app/Models/GhiDanh.php` |
| `AttendanceRecord` | `DiemDanh` | `app/Models/diem_danh.php` | `app/Models/DiemDanh.php` |
| `Quiz` | `BaiKiemTra` | `app/Models/bai_kiem_tra.php` | `app/Models/BaiKiemTra.php` |
| `Certificate` | `ChungChi` | `app/Models/chung_chi.php` | `app/Models/ChungChi.php` |
| `Notification` | `ThongBao` | `app/Models/thong_bao_nguoi_dung.php` | `app/Models/ThongBao.php` |
| `Room` | `PhongHoc` | `app/Models/phong_hoc.php` | `app/Models/PhongHoc.php` |
| `Subject` | `MonHoc` | `app/Models/mon_hoc.php` | `app/Models/MonHoc.php` |
| `ClassSchedule` | `LichHoc` | `app/Models/lich_lop_hoc.php` | `app/Models/LichHoc.php` |
| `Grade` | `DiemSo` | `app/Models/diem_so.php` | `app/Models/DiemSo.php` |
| `Module` | `HocPhan` | `app/Models/hoc_phan.php` | `app/Models/HocPhan.php` |
| `Lesson` | `BaiHoc` | `app/Models/bai_hoc.php` | `app/Models/BaiHoc.php` |
| `Question` | `CauHoi` | `app/Models/cau_hoi.php` | `app/Models/CauHoi.php` |
| `Option` | `LuaChon` | `app/Models/lua_chon.php` | `app/Models/LuaChon.php` |
| `QuizAnswer` | `TraLoiBaiKiemTra` | `app/Models/tra_loi_bai_kiem_tra.php` | `app/Models/TraLoiBaiKiemTra.php` |
| `Announcement` | `ThongBaoChung` | `app/Models/thong_bao.php` | `app/Models/ThongBaoChung.php` |
| `Review` | `DanhGia` | `app/Models/danh_gia.php` | `app/Models/DanhGia.php` |
| `TeacherEvaluation` | `DanhGiaGiaoVien` | `app/Models/danh_gia_giao_vien.php` | `app/Models/DanhGiaGiaoVien.php` |
| `TeacherApplication` | `DonUngTuyenGiaoVien` | `app/Models/don_ung_tuyen_giao_vien.php` | `app/Models/DonUngTuyenGiaoVien.php` |
| `Department` | `PhongBan` | `app/Models/phong_ban.php` | `app/Models/PhongBan.php` |
| `Category` | `NhomHoc` | `app/Models/nhom_hoc.php` | `app/Models/NhomHoc.php` |
| `Role` | `VaiTro` | `app/Models/vai_tro.php` | `app/Models/VaiTro.php` |
| `ScheduleChangeRequest` | `YeuCauDoiLich` | `app/Models/yeu_cau_doi_lich.php` | `app/Models/YeuCauDoiLich.php` |
| `LeaveRequest` | `YeuCauXinPhep` | `app/Models/LeaveRequest.php` | `app/Models/YeuCauXinPhep.php` |
| `CourseTimeSlot` | `KhungGioKhoaHoc` | `app/Models/khung_gio_khoa_hoc.php` | `app/Models/KhungGioKhoaHoc.php` |
| `SlotRegistration` | `NguyenVongKhungGio` | `app/Models/nguyen_vong_khung_gio.php` | `app/Models/NguyenVongKhungGio.php` |
| `SlotRegistrationChoice` | `LuaChonNguyenVongKhungGio` | `app/Models/lua_chon_nguyen_vong_khung_gio.php` | `app/Models/LuaChonNguyenVongKhungGio.php` |
| `CustomScheduleRequest` | `YeuCauLichTuyChon` | `app/Models/yeu_cau_lich_tuy_chon.php` | `app/Models/YeuCauLichTuyChon.php` |
| `LessonProgress` | `TienDoBaiHoc` | `app/Models/tien_do_bai_hoc.php` | `app/Models/TienDoBaiHoc.php` |
| `Attachment` | `TepDinhKem` | `app/Models/tep_dinh_kem.php` | `app/Models/TepDinhKem.php` |
| `Comment` | `BinhLuan` | `app/Models/binh_luan.php` | `app/Models/BinhLuan.php` |

> **Ghi chú đặc biệt cho `NguoiDung`**: class này extend `Authenticatable` thay vì `Model`. Không được đổi class cha hay cấu hình auth. Chỉ đổi tên class và file.

---

## Các bước thực hiện (theo đúng thứ tự này)

### Bước 1 — Đổi tên file và class trong từng Model

Với mỗi dòng trong bảng mapping:

1. **Tạo file mới** với tên file mới (PascalCase), ví dụ `app/Models/NguoiDung.php`
2. **Sao chép toàn bộ nội dung** từ file cũ sang file mới
3. **Chỉ thay dòng** `class OldName` thành `class NewName`
4. **Không thay đổi bất cứ thứ gì khác** trong file: không đổi `$table`, không đổi quan hệ, không đổi logic
5. **Xóa file cũ** sau khi tạo xong file mới

Ví dụ cụ thể cho `NguoiDung`:
```php
// TRƯỚC (nguoi_dung.php):
class User extends Authenticatable { ... }

// SAU (NguoiDung.php):
class NguoiDung extends Authenticatable { ... }
```

---

### Bước 2 — Cập nhật tất cả `use` statements trong toàn bộ project

Tìm và thay thế trong tất cả file `.php` ở `app/`, `database/`, `routes/`, `tests/`:

```
use App\Models\User;                          → use App\Models\NguoiDung;
use App\Models\Course;                        → use App\Models\KhoaHoc;
use App\Models\ClassRoom;                     → use App\Models\LopHoc;
use App\Models\Enrollment;                    → use App\Models\GhiDanh;
use App\Models\AttendanceRecord;              → use App\Models\DiemDanh;
use App\Models\Quiz;                          → use App\Models\BaiKiemTra;
use App\Models\Certificate;                   → use App\Models\ChungChi;
use App\Models\Notification;                  → use App\Models\ThongBao;
use App\Models\Room;                          → use App\Models\PhongHoc;
use App\Models\Subject;                       → use App\Models\MonHoc;
use App\Models\ClassSchedule;                 → use App\Models\LichHoc;
use App\Models\Grade;                         → use App\Models\DiemSo;
use App\Models\Module;                        → use App\Models\HocPhan;
use App\Models\Lesson;                        → use App\Models\BaiHoc;
use App\Models\Question;                      → use App\Models\CauHoi;
use App\Models\Option;                        → use App\Models\LuaChon;
use App\Models\QuizAnswer;                    → use App\Models\TraLoiBaiKiemTra;
use App\Models\Announcement;                  → use App\Models\ThongBaoChung;
use App\Models\Review;                        → use App\Models\DanhGia;
use App\Models\TeacherEvaluation;             → use App\Models\DanhGiaGiaoVien;
use App\Models\TeacherApplication;            → use App\Models\DonUngTuyenGiaoVien;
use App\Models\Department;                    → use App\Models\PhongBan;
use App\Models\Category;                      → use App\Models\NhomHoc;
use App\Models\Role;                          → use App\Models\VaiTro;
use App\Models\ScheduleChangeRequest;         → use App\Models\YeuCauDoiLich;
use App\Models\LeaveRequest;                  → use App\Models\YeuCauXinPhep;
use App\Models\CourseTimeSlot;                → use App\Models\KhungGioKhoaHoc;
use App\Models\SlotRegistration;              → use App\Models\NguyenVongKhungGio;
use App\Models\SlotRegistrationChoice;        → use App\Models\LuaChonNguyenVongKhungGio;
use App\Models\CustomScheduleRequest;         → use App\Models\YeuCauLichTuyChon;
use App\Models\LessonProgress;                → use App\Models\TienDoBaiHoc;
use App\Models\Attachment;                    → use App\Models\TepDinhKem;
use App\Models\Comment;                       → use App\Models\BinhLuan;
```

---

### Bước 3 — Cập nhật tất cả tham chiếu tên class trong code

Tìm tất cả chỗ dùng tên class cũ **không phải trong `use` statement** — ví dụ trong type hints, `new ClassName()`, `ClassName::method()`, `ClassName::class`, Eloquent relationships, `@var` annotations, `instanceof` checks, factory calls, v.v.

Các pattern cần tìm và thay (ví dụ):
```php
// Type hints
function foo(User $user)          → function foo(NguoiDung $user)
function foo(Course $course)      → function foo(KhoaHoc $course)

// Static calls
User::findOrFail($id)             → NguoiDung::findOrFail($id)
Course::query()                   → KhoaHoc::query()
Enrollment::STATUS_PENDING        → GhiDanh::STATUS_PENDING

// new keyword
new User([...])                   → new NguoiDung([...])

// ::class
User::class                       → NguoiDung::class
Course::class                     → KhoaHoc::class

// instanceof
$x instanceof User                → $x instanceof NguoiDung

// Relationship return types (nếu có @return annotation)
@return \App\Models\User          → @return \App\Models\NguoiDung
```

---

### Bước 4 — Cập nhật `config/auth.php`

Tìm dòng model trong auth guards:
```php
// TRƯỚC:
'model' => App\Models\User::class,

// SAU:
'model' => App\Models\NguoiDung::class,
```

---

### Bước 5 — Cập nhật `database/factories/`

Nếu có `UserFactory.php` hoặc factory khác dùng tên class cũ, cập nhật:
```php
// TRƯỚC:
protected $model = User::class;

// SAU:
protected $model = NguoiDung::class;
```

---

### Bước 6 — Cập nhật Seeders

Trong `database/seeders/`, thay tất cả `use App\Models\OldName` và `OldName::` sang tên mới.

---

### Bước 7 — Cập nhật `bootstrap/cache/`

Xóa toàn bộ cache để tránh xung đột:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

---

### Bước 8 — Kiểm tra inline class references trong routes

Trong `routes/lenh.php` hoặc các route file, có thể có inline model usage như:
```php
$subject = \App\Models\Subject::findOrFail($id);
// →
$subject = \App\Models\MonHoc::findOrFail($id);
```
Tìm tất cả `\App\Models\` trong route files và đổi tương ứng.

---

## Ràng buộc tuyệt đối — KHÔNG được làm

- **KHÔNG** đổi tên bảng database (`$table` property trong Model)
- **KHÔNG** đổi tên cột, migration, hay schema
- **KHÔNG** đổi tên biến local trong function (ví dụ `$user`, `$course` vẫn giữ nguyên — chỉ đổi type hint)
- **KHÔNG** đổi tên route, controller, service
- **KHÔNG** đổi nội dung Blade views (views không import Model class trực tiếp)
- **KHÔNG** đổi tên namespace (`namespace App\Models` giữ nguyên — chỉ đổi tên class)
- **KHÔNG** refactor logic gì khác
- **KHÔNG** tự ý thêm tính năng hay sửa bug không liên quan

---

## Kiểm tra sau khi xong

```bash
# 1. Autoload lại
composer dump-autoload

# 2. Xóa cache
php artisan config:clear && php artisan cache:clear && php artisan route:clear

# 3. Kiểm tra syntax không lỗi
php artisan route:list

# 4. Chạy test — phải xanh toàn bộ như trước
php artisan test

# 5. Kiểm tra không còn tham chiếu tên cũ (ngoài comment)
grep -rn "class User\b\|class Course\b\|class ClassRoom\b\|class Enrollment\b" app/Models/
# → Phải trả về rỗng
```

---

## Thứ tự ưu tiên nếu làm từng bước

Nếu không làm tất cả một lúc, ưu tiên theo mức độ dùng nhiều nhất:

1. `User` → `NguoiDung` (79 chỗ dùng — quan trọng nhất, liên quan auth)
2. `Course` → `KhoaHoc` (30 chỗ)
3. `Enrollment` → `GhiDanh` (29 chỗ)
4. `Subject` → `MonHoc` (24 chỗ)
5. `ClassRoom` → `LopHoc` (24 chỗ)
6. `Room` → `PhongHoc` (17 chỗ)
7. `ClassSchedule` → `LichHoc` (15 chỗ)
8. Còn lại theo bảng mapping

> Mỗi batch phải chạy `php artisan test` xanh trước khi làm batch tiếp theo.
