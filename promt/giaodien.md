Bạn là senior Laravel architect + senior UI engineer, đang refactor và nâng cấp dự án web giáo dục “khaitriedu”.

## Mục tiêu của task này
Thực hiện 3 mục tiêu lớn, theo đúng thứ tự ưu tiên:

### Mục tiêu 1: Nâng cấp giao diện admin
Nâng cấp toàn bộ giao diện khu vực admin theo hướng:
- hiện đại
- chuyên nghiệp
- cao cấp
- dễ dùng
- rõ hierarchy
- phù hợp dashboard quản trị hệ thống giáo dục

### Mục tiêu 2: Dọn rác cấu trúc dự án
Kiểm tra và xóa / gộp / sắp xếp lại các file không còn dùng hoặc trùng lặp trong:
- resources
- app/Http/Controllers
- routes

### Mục tiêu 3: Tổ chức lại controller
Gộp và phân nhóm controller theo đúng 3 phân hệ:
- Admin
- Teacher
- Student

Phải làm cho codebase sạch, dễ maintain, đúng kiến trúc hơn.

--------------------------------------------------
1. BỐI CẢNH NGHIỆP VỤ BẮT BUỘC PHẢI GIỮ
--------------------------------------------------

Dự án là website giáo dục có 3 vai trò:
- Admin
- Giảng viên
- Học viên

Flow nghiệp vụ đã chốt, phải xem là source of truth:
- Admin là trung tâm duyệt và quyết định toàn bộ nghiệp vụ quan trọng
- Học viên không tự vào lớp ngay sau khi đăng ký
- Giảng viên không tự đổi lịch, chỉ được gửi yêu cầu
- Các hoạt động quan trọng đều phải qua admin duyệt
- Hệ thống tập trung vào quản lý lịch học, quản lý lớp, quản lý chất lượng đào tạo

Không được refactor làm sai flow này.

--------------------------------------------------
2. PHẠM VI CÔNG VIỆC CỤ THỂ
--------------------------------------------------

Bạn phải thực hiện theo 4 phase trong đúng task này.

# PHASE A — AUDIT & LẬP KẾ HOẠCH REFACTOR
Trước khi sửa code, hãy audit toàn bộ cấu trúc hiện tại:
- app/Http/Controllers
- resources/views
- routes
- layout đang dùng
- partial đang dùng
- file blade trùng lặp
- file controller không còn được route gọi tới
- file route cũ / route dead / route trùng lặp
- view không còn được controller render
- component UI trùng lặp
- asset css/js không còn dùng

## Yêu cầu output phase A
1. Liệt kê cấu trúc hiện tại
2. Chỉ ra:
   - file nào đang dùng
   - file nào nghi ngờ không dùng
   - file nào trùng trách nhiệm
   - file nào nên gộp
   - file nào nên đổi tên
3. Đưa ra kế hoạch refactor an toàn trước khi sửa
4. Không xóa bừa. Chỉ xóa sau khi xác minh file không dùng

--------------------------------------------------
# PHASE B — REFACTOR KIẾN TRÚC CONTROLLER & ROUTE
--------------------------------------------------

## Mục tiêu
Tổ chức lại controller rõ ràng theo 3 nhóm chính.

## Cấu trúc controller bắt buộc sau refactor
Sắp xếp lại về dạng:

app/Http/Controllers/
  Admin/
  Teacher/
  Student/
  Auth/
  Shared/        (nếu thực sự cần controller dùng chung)
  Controller.php

Ví dụ:
- app/Http/Controllers/Admin/DashboardController.php
- app/Http/Controllers/Admin/StudentController.php
- app/Http/Controllers/Admin/TeacherController.php
- app/Http/Controllers/Admin/TeacherApplicationController.php
- app/Http/Controllers/Admin/StudyGroupController.php
- app/Http/Controllers/Admin/CourseController.php
- app/Http/Controllers/Admin/ModuleController.php
- app/Http/Controllers/Admin/EnrollmentController.php
- app/Http/Controllers/Admin/ScheduleController.php
- app/Http/Controllers/Admin/ScheduleChangeRequestController.php
- app/Http/Controllers/Admin/ReportController.php

- app/Http/Controllers/Teacher/DashboardController.php
- app/Http/Controllers/Teacher/AttendanceController.php
- app/Http/Controllers/Teacher/GradeController.php
- app/Http/Controllers/Teacher/ScheduleController.php
- app/Http/Controllers/Teacher/StudentController.php
- app/Http/Controllers/Teacher/ScheduleChangeRequestController.php

- app/Http/Controllers/Student/DashboardController.php
- app/Http/Controllers/Student/EnrollmentController.php
- app/Http/Controllers/Student/ScheduleController.php
- app/Http/Controllers/Student/QuizController.php
- app/Http/Controllers/Student/ReviewController.php
- app/Http/Controllers/Student/AttendanceController.php
- app/Http/Controllers/Student/GradeController.php

## Yêu cầu route
Tách route rõ ràng:
- routes/web.php chỉ giữ phần public + include các file khác nếu cần
- routes/admin.php
- routes/teacher.php
- routes/student.php
- routes/auth.php nếu phù hợp

Nếu repo hiện tại chưa hỗ trợ load nhiều file route, hãy cấu hình lại gọn gàng theo chuẩn Laravel hiện tại của project.

## Yêu cầu bắt buộc
- update toàn bộ namespace/controller import cho đúng
- không để route gãy sau refactor
- không để file cũ dư thừa sau khi chuyển
- xóa controller cũ nếu đã xác minh không còn dùng
- giữ tương thích nghiệp vụ cũ nếu chưa refactor logic

## Middleware / prefix
- admin routes dùng prefix `admin` + middleware admin
- teacher routes dùng prefix `teacher` + middleware teacher
- student routes dùng prefix `student` + middleware student

## Output phase B
- danh sách file controller cũ đã xóa
- danh sách file controller mới đã tạo
- danh sách route file đã sửa/tạo
- giải thích mapping cũ -> mới

--------------------------------------------------
# PHASE C — DỌN RÁC FILE KHÔNG DÙNG
--------------------------------------------------

## Phạm vi dọn
1. resources/views
2. app/Http/Controllers
3. routes
4. asset css/js nếu thấy rõ là dead code

## Nguyên tắc cực kỳ quan trọng
- chỉ xóa file khi xác minh chắc chắn không dùng
- nếu chưa chắc, chuyển sang backup strategy hoặc comment note
- ưu tiên gộp partial/layout/view trùng lặp
- loại bỏ blade cũ không còn route/controller render
- loại bỏ controller không được gọi
- loại bỏ route chết
- loại bỏ import thừa
- loại bỏ file demo/example mặc định Laravel nếu không dùng

## Riêng với resources/views
Tổ chức lại rõ ràng theo nhóm:

resources/views/
  layouts/
    admin/
    teacher/
    student/
    public/
  admin/
    dashboard/
    students/
    teachers/
    teacher-applications/
    study-groups/
    courses/
    modules/
    enrollments/
    schedules/
    reports/
  teacher/
    dashboard/
    schedules/
    students/
    attendance/
    grades/
  student/
    dashboard/
    enrollments/
    schedules/
    quizzes/
    reviews/
    attendance/
    grades/
  auth/
  components/
  partials/

Nếu cần, gộp layout cũ bị rối thành layout chuẩn hơn.

## Output phase C
- liệt kê file đã xóa
- liệt kê file đã gộp
- liệt kê file đã đổi tên
- giải thích vì sao xóa/gộp là an toàn

--------------------------------------------------
# PHASE D — NÂNG CẤP GIAO DIỆN ADMIN VIP / PRO
--------------------------------------------------

## Mục tiêu thiết kế
Thiết kế lại admin UI theo phong cách:
- hiện đại
- enterprise dashboard
- chuyên nghiệp
- sáng sủa, rõ ràng
- cao cấp nhưng không rối
- phù hợp hệ thống giáo dục / quản trị vận hành

## Nguyên tắc UI/UX bắt buộc
1. Không chỉ đổi màu, mà phải nâng cấp trải nghiệm thật
2. Có cấu trúc layout quản trị thống nhất
3. Sidebar rõ nhóm chức năng
4. Header/topbar gọn đẹp
5. Card thống kê đẹp
6. Table dữ liệu chuyên nghiệp
7. Bộ lọc và search nhìn xịn
8. Form create/edit dễ nhìn, có spacing tốt
9. Status badge đẹp và thống nhất
10. Empty state / no data state đẹp
11. Flash message success/error/warning đẹp
12. Confirm modal hoặc confirm UI cho thao tác nguy hiểm
13. Responsive tốt ở laptop/tablet
14. Không phá chức năng cũ

## Giao diện admin cần có
### 1. Admin layout mới
Bao gồm:
- sidebar trái
- topbar
- user dropdown
- breadcrumb
- vùng content chuẩn
- flash message
- footer nhẹ nếu cần

### 2. Sidebar admin nhóm chức năng rõ ràng
Nhóm menu đề xuất:
- Tổng quan
- Quản lý người dùng
  - Học viên
  - Giảng viên
- Quản lý đào tạo
  - Nhóm học
  - Khóa học
  - Module
  - Đăng ký học
  - Lịch học
- Vận hành
  - Đơn ứng tuyển giảng viên
  - Yêu cầu đổi lịch
- Báo cáo

### 3. Dashboard admin
Thiết kế dashboard mới với:
- card thống kê
- block cảnh báo pending items
- quick actions
- bảng dữ liệu gần đây
- khu vực tóm tắt hoạt động

Các card nên hiển thị:
- tổng học viên
- tổng giảng viên
- đơn ứng tuyển chờ duyệt
- đăng ký học chờ xử lý
- lớp đang hoạt động
- yêu cầu đổi lịch chờ duyệt

### 4. Danh sách dữ liệu admin
Các trang index của admin phải được nâng cấp:
- table đẹp
- search/filter bar đẹp
- phân trang rõ ràng
- actions gọn
- badge trạng thái
- bulk action nếu phù hợp
- empty state đẹp

Áp dụng cho:
- học viên
- giảng viên
- đơn ứng tuyển
- nhóm học
- khóa học
- module
- đăng ký học
- lịch học
- yêu cầu đổi lịch

### 5. Form create/edit/show
Tất cả form admin phải:
- label rõ
- input spacing tốt
- chia section hợp lý
- error message đẹp
- action buttons rõ
- có back button hợp lý

### 6. Design system nội bộ
Tạo style thống nhất cho admin:
- card
- table
- badge
- button
- form
- modal
- alert
- filter bar
- page header

Nếu project đang dùng Bootstrap/Tailwind/Blade thuần, hãy tận dụng stack hiện có, nhưng chuẩn hóa cho đẹp và đồng bộ.
Không thêm thư viện nặng nếu không thật cần.
Nếu cần dùng component Blade để tái sử dụng thì hãy tạo.

## Yêu cầu thẩm mỹ
Giao diện phải cho cảm giác:
- premium
- gọn
- có phân cấp thị giác
- dễ nhìn dữ liệu
- giống admin panel thật, không phải đồ án sơ sài

## Ưu tiên nâng cấp các màn hình này trước
1. admin layout tổng
2. admin dashboard
3. admin students index/show/form
4. admin teachers index/show/form
5. admin teacher applications index/show
6. admin enrollments index/show
7. admin schedules index/show
8. các màn hình còn lại theo cùng style system

--------------------------------------------------
3. YÊU CẦU KỸ THUẬT KHI REFACTOR
--------------------------------------------------

## Bắt buộc
- Không được làm hỏng route cũ mà chưa remap
- Không được xóa file nếu chưa kiểm tra usage
- Không được để duplicated view/controller sau refactor mà không có lý do
- Không được để namespace sai
- Không được để import chết
- Không được để blade path sai
- Không được đổi cấu trúc xong mà không sửa links trong sidebar/menu/button

## Nếu cần
- tạo view components blade dùng chung
- tạo partial dùng chung cho filter/table/header
- tạo helper view nhỏ nếu hợp lý
- chuẩn hóa tên file và tên folder

--------------------------------------------------
4. CÁCH THỰC HIỆN BẮT BUỘC
--------------------------------------------------

Làm theo thứ tự:

### Bước 1
Audit toàn bộ dự án liên quan tới:
- controllers
- routes
- resources/views
- admin UI hiện tại

### Bước 2
Đề xuất kế hoạch refactor rõ ràng:
- file nào giữ
- file nào chuyển
- file nào xóa
- file nào gộp
- route nào tách

### Bước 3
Thực hiện refactor controller + route trước

### Bước 4
Dọn file rác và cấu trúc lại resources/views

### Bước 5
Nâng cấp admin UI đồng bộ

### Bước 6
Tự kiểm tra toàn bộ route/view/controller sau refactor

--------------------------------------------------
5. KẾT QUẢ ĐẦU RA BẮT BUỘC
--------------------------------------------------

Sau khi hoàn thành, bạn phải báo cáo theo format sau:

## A. Audit summary
- tổng quan cấu trúc cũ
- vấn đề đã phát hiện

## B. Refactor summary
- controller nào đã chuyển nhóm
- route nào đã tách
- file nào đã xóa
- file nào đã gộp
- file nào đã đổi tên

## C. UI upgrade summary
- layout nào đã nâng cấp
- component nào đã tạo
- màn hình admin nào đã được làm mới

## D. File changed
- liệt kê file tạo mới
- liệt kê file sửa
- liệt kê file xóa

## E. Manual test checklist
- admin dashboard chạy
- menu admin hoạt động
- các route admin chính hoạt động
- route teacher/student không lỗi sau refactor
- các blade path hoạt động
- không còn import/namespace lỗi

--------------------------------------------------
6. MỨC ĐỘ ƯU TIÊN
--------------------------------------------------

Ưu tiên cao nhất:
1. cấu trúc controller theo 3 nhóm Admin / Teacher / Student
2. route sạch, dễ maintain
3. xóa/gộp file rác an toàn
4. nâng cấp admin layout và dashboard
5. đồng bộ UI các trang admin chính

--------------------------------------------------
7. LƯU Ý CỰC QUAN TRỌNG
--------------------------------------------------

- Đây là task refactor + UI upgrade thật, không phải chỉ đưa đề xuất
- Hãy sửa code trực tiếp
- Không viết pseudo-code
- Không chỉ nói “nên làm”
- Phải implement
- Nhưng phải làm an toàn, có audit trước khi xóa
- Nếu gặp file nghi ngờ chưa dùng, phải xác minh bằng tìm route/import/render usage trước

Bắt đầu từ:
PHASE A — AUDIT & LẬP KẾ HOẠCH REFACTOR

Sau khi audit xong, tiếp tục thực hiện luôn refactor và UI upgrade trong cùng task này, rồi báo cáo đầy đủ theo format A-B-C-D-E.