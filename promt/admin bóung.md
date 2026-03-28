Bạn là senior Laravel developer đang tiếp tục phát triển dự án web giáo dục tên “khaitriedu”.

## 0. Bối cảnh dự án
Dự án là website giáo dục có 3 vai trò:
- Admin
- Giảng viên
- Học viên

## 1. FLOW NGHIỆP VỤ ĐÃ CHỐT — PHẢI XEM LÀ SOURCE OF TRUTH

### 1.1. Vai trò trung tâm của Admin
- Admin là trung tâm phê duyệt và quyết định toàn bộ nghiệp vụ quan trọng
- Học viên không tự vào lớp ngay sau khi đăng ký
- Giảng viên không tự đổi lịch, chỉ được gửi yêu cầu đổi lịch
- Mọi thay đổi quan trọng như xếp lớp, mở lớp, gán phòng, gán giảng viên, đổi lịch đều phải qua admin duyệt

### 1.2. Flow đào tạo đã thống nhất
Hệ thống vận hành theo mô hình:

1. Admin tạo tài nguyên đào tạo trước:
   - nhóm học
   - khóa học
   - module
   - phòng học
   - khung giờ học sẵn cho từng khóa học
   - giảng viên dự kiến
   - phòng học dự kiến

2. Hệ thống kiểm tra:
   - không trùng giảng viên
   - không trùng phòng học
   - không vượt sức chứa phòng
   - cấu hình lịch hợp lệ

3. Học viên vào khóa học và chọn từ các khung giờ do admin tạo sẵn

4. Học viên bắt buộc:
   - chọn ít nhất 2 khung giờ
   - không được chọn lịch bị trùng với lịch hiện tại của mình
   - gửi đăng ký theo nguyện vọng khung giờ

5. Admin theo dõi số lượng học viên đăng ký theo từng khung giờ

6. Khi đủ điều kiện:
   - admin duyệt
   - admin mở lớp chính thức
   - gán giảng viên
   - gán phòng học
   - tạo lịch học chính thức
   - chuyển học viên vào lớp

7. Hệ thống gửi thông báo:
   - cho học viên
   - cho giảng viên
   - lớp chuyển sang trạng thái chờ khai giảng hoặc đang hoạt động

### 1.3. Quy tắc cốt lõi bắt buộc
- Admin quản lý phòng học để sắp xếp lịch
- Admin tạo các khung giờ sẵn cho từng khóa học
- Các khóa học không được trùng phòng hoặc trùng giảng viên ở cùng thời điểm
- Học viên dựa trên thời khóa biểu admin tạo để chọn lịch học phù hợp
- Học viên phải chọn ít nhất 2 khung giờ
- Học viên không được chọn khung giờ bị trùng lịch
- Admin theo dõi số lượng đăng ký và quyết định mở lớp
- Khi đủ học viên, admin mở lớp chính thức và thông báo lịch học

Từ bây giờ chỉ tập trung làm **phân hệ ADMIN** theo từng phase độc lập.
Yêu cầu: phase nào xong phase đó, hoàn thiện đầy đủ rồi mới chuyển phase tiếp theo.

--------------------------------------------------
2. CÔNG NGHỆ VÀ NGUYÊN TẮC BẮT BUỘC
--------------------------------------------------

Dùng đúng stack hiện tại của repo Laravel.
Giữ code sạch, dễ bảo trì, tách logic hợp lý.

Ưu tiên:
- Form Request để validate
- Service class / Action class nếu logic dài
- Middleware / policy / gate cho phân quyền admin
- Eloquent relationship chuẩn
- Migration đầy đủ nếu thiếu bảng/cột
- Seeder/Faker cho dữ liệu mẫu nếu cần
- Feature test cho các flow chính
- Blade theo layout hiện có hoặc layout admin đã được chuẩn hóa
- Không phá vỡ chức năng cũ nếu chưa cần thiết

--------------------------------------------------
3. LUẬT TRIỂN KHAI BẮT BUỘC
--------------------------------------------------

1. Chỉ làm chức năng admin theo đúng phase được giao
2. Mỗi phase phải hoàn thành đủ:
   - migration (nếu cần)
   - model / relation (nếu cần)
   - route
   - controller
   - request validation
   - service/action nếu cần
   - view admin đầy đủ
   - xử lý trạng thái
   - thông báo thành công / thất bại
   - test
3. Khi làm xong mỗi phase, phải:
   - tóm tắt file đã tạo/sửa
   - giải thích luồng hoạt động
   - nêu cách test thủ công
   - nêu test tự động đã viết
4. Không làm dồn nhiều phase trong một lần nếu chưa hoàn tất phase hiện tại
5. Nếu dữ liệu hiện tại của repo chưa đủ để support phase, hãy thêm migration tối thiểu nhưng phải giữ naming rõ ràng
6. Mọi route admin phải được bảo vệ bằng middleware/phân quyền admin
7. Không viết pseudo-code, phải viết code chạy được
8. Không phá flow đã chốt
9. Nếu có thể tái sử dụng model hiện có thì ưu tiên tái sử dụng, chỉ bổ sung tối thiểu để hỗ trợ flow mới

--------------------------------------------------
4. KIẾN TRÚC PHÂN HỆ ADMIN PHẢI BÁM THEO FLOW ĐÃ CHỐT
--------------------------------------------------

Admin có các chức năng:

1. Dashboard admin
2. Quản lý người dùng
   - quản lý học viên riêng
   - quản lý giảng viên riêng
3. Quản lý đơn ứng tuyển giảng viên
4. Quản lý nhóm học
5. Quản lý khóa học
6. Quản lý module trong khóa học
7. Quản lý phòng học
8. Quản lý khung giờ học cho từng khóa học
9. Quản lý đăng ký nguyện vọng khung giờ của học viên
10. Theo dõi số lượng đăng ký theo từng khung giờ
11. Mở lớp chính thức khi đủ học viên
12. Quản lý lịch học / lớp học
13. Duyệt / từ chối yêu cầu đổi lịch của giảng viên
14. Báo cáo hệ thống

Mỗi chức năng phải có:
- trạng thái rõ ràng
- màn hình rõ ràng
- thao tác rõ ràng
- validation rõ ràng
- rule nghiệp vụ đúng flow

--------------------------------------------------
5. CHUẨN ROUTE ADMIN
--------------------------------------------------

Tất cả chức năng admin phải nằm dưới prefix:
- /admin/...

Ví dụ:
- /admin/dashboard
- /admin/students
- /admin/teachers
- /admin/teacher-applications
- /admin/study-groups
- /admin/courses
- /admin/modules
- /admin/rooms
- /admin/course-time-slots
- /admin/slot-registrations
- /admin/classes
- /admin/schedules
- /admin/schedule-change-requests
- /admin/reports

Tách route theo nhóm hợp lý.
Nếu phù hợp, chia file route admin riêng.

--------------------------------------------------
6. CHUẨN TRẠNG THÁI NGHIỆP VỤ CẦN CÓ
--------------------------------------------------

Nếu repo chưa có, hãy bổ sung trạng thái rõ ràng như sau:

### User status
- active
- inactive
- locked

### Teacher application status
- pending
- approved
- rejected
- needs_revision

### Room status
- active
- maintenance
- inactive

### Course time slot status
- pending_open
- open_for_registration
- ready_to_open_class
- class_opened
- cancelled

### Student slot registration status
- pending
- recorded
- scheduled
- needs_reselect
- rejected

### Class status
- pending_start
- active
- completed
- cancelled

### Schedule change request status
- pending
- approved
- rejected

### Payment status nếu có
- unpaid
- pending
- paid
- failed

### Attendance status nếu có liên kết báo cáo
- present
- absent
- late
- excused

Ưu tiên dùng enum nhất quán nếu phù hợp, hoặc constant trong model.

--------------------------------------------------
PHASE A1 — NỀN TẢNG ADMIN + PHÂN QUYỀN + DASHBOARD
--------------------------------------------------

## Mục tiêu phase A1
Thiết lập nền tảng chắc chắn cho phân hệ admin.

## Cần làm
1. Kiểm tra hệ thống đăng nhập hiện tại
2. Thiết lập bảo vệ route admin:
   - chỉ admin mới vào được
   - teacher/student không vào được
3. Tạo hoặc chuẩn hóa admin dashboard
4. Hiển thị số liệu tổng quan:
   - tổng học viên
   - tổng giảng viên
   - tổng đơn ứng tuyển chờ duyệt
   - tổng khóa học
   - tổng nhóm học
   - tổng phòng học
   - tổng khung giờ đang mở đăng ký
   - tổng đăng ký nguyện vọng đang chờ xử lý
   - tổng lớp đang hoạt động
   - tổng yêu cầu đổi lịch đang chờ
5. Tạo layout admin thống nhất:
   - sidebar
   - topbar
   - breadcrumb nếu phù hợp
   - flash message
6. Chuẩn hóa menu admin theo đúng flow

## Kết quả mong muốn
- Admin login xong vào được dashboard
- User không phải admin bị chặn
- Có layout admin dùng lại cho các phase sau
- Có dashboard thống kê cơ bản

## Test cần có
- admin truy cập /admin/dashboard thành công
- student bị chặn
- teacher bị chặn

--------------------------------------------------
PHASE A2 — QUẢN LÝ NGƯỜI DÙNG: HỌC VIÊN
--------------------------------------------------

## Mục tiêu phase A2
Admin quản lý học viên riêng biệt.

## Chức năng bắt buộc
1. Danh sách học viên
   - phân trang
   - tìm kiếm theo tên/email/số điện thoại nếu có
   - lọc theo trạng thái
2. Xem chi tiết học viên
   - thông tin cá nhân
   - trạng thái tài khoản
   - danh sách đăng ký học
   - danh sách nguyện vọng khung giờ
   - lịch học hiện tại nếu có
   - tình trạng thanh toán nếu có
3. Tạo học viên mới
4. Cập nhật thông tin học viên
5. Khóa / mở khóa tài khoản học viên
6. Xóa mềm hoặc inactive theo cấu trúc phù hợp với repo
7. Restore nếu dùng soft delete
8. Gắn role học viên đúng chuẩn nếu cần

## Test cần có
- admin xem danh sách học viên
- admin tạo học viên
- admin sửa học viên
- admin khóa học viên
- user không phải admin không truy cập được

--------------------------------------------------
PHASE A3 — QUẢN LÝ NGƯỜI DÙNG: GIẢNG VIÊN
--------------------------------------------------

## Mục tiêu phase A3
Admin quản lý giảng viên riêng biệt.

## Chức năng bắt buộc
1. Danh sách giảng viên
2. Tìm kiếm / lọc trạng thái
3. Xem chi tiết giảng viên
   - thông tin cá nhân
   - chuyên môn nếu có
   - lớp/khóa học đang phụ trách
   - lịch dạy
   - trạng thái tài khoản
4. Tạo giảng viên mới
5. Cập nhật giảng viên
6. Khóa / mở khóa tài khoản giảng viên
7. Gán role giảng viên

## Test cần có
- admin xem danh sách giảng viên
- admin tạo giảng viên
- admin cập nhật giảng viên
- admin khóa giảng viên

--------------------------------------------------
PHASE A4 — QUẢN LÝ ĐƠN ỨNG TUYỂN GIẢNG VIÊN
--------------------------------------------------

## Mục tiêu phase A4
Đúng flow:
Ứng viên gửi đơn → admin duyệt/từ chối/yêu cầu bổ sung → nếu duyệt thì trở thành giảng viên.

## Chức năng bắt buộc
1. Danh sách đơn ứng tuyển
2. Lọc theo status:
   - pending
   - approved
   - rejected
   - needs_revision
3. Xem chi tiết đơn:
   - thông tin cá nhân
   - trình độ
   - kinh nghiệm
   - kỹ năng / chuyên môn
   - file minh chứng nếu có
4. Admin duyệt đơn
5. Admin từ chối đơn
6. Admin yêu cầu bổ sung
7. Khi duyệt:
   - cập nhật status application = approved
   - tạo mới user hoặc nâng role hiện có thành teacher
8. Khi từ chối:
   - lưu lý do từ chối
9. Khi needs_revision:
   - lưu ghi chú phản hồi

## Nếu thiếu field thì bổ sung tối thiểu
- status
- admin_note
- reviewed_by
- reviewed_at
- rejection_reason

## Test cần có
- admin xem danh sách application
- admin duyệt application
- admin từ chối application
- khi duyệt thì user trở thành teacher

--------------------------------------------------
PHASE A5 — QUẢN LÝ NHÓM HỌC
--------------------------------------------------

## Mục tiêu phase A5
Admin quản lý nhóm học.

## Chức năng bắt buộc
1. Danh sách nhóm học
2. Tạo nhóm học
3. Cập nhật nhóm học
4. Xem chi tiết nhóm học
5. Inactive/soft delete nếu cần
6. Hiển thị:
   - tên nhóm
   - mô tả
   - cấp độ / chương trình nếu có
   - trạng thái
   - số khóa học bên trong

## Rule
- Không xóa cứng nếu đang có khóa học liên kết

## Test cần có
- admin tạo nhóm học
- admin sửa nhóm học
- admin không xóa cứng nhóm có dữ liệu phụ thuộc

--------------------------------------------------
PHASE A6 — QUẢN LÝ KHÓA HỌC
--------------------------------------------------

## Mục tiêu phase A6
Admin quản lý khóa học nằm trong nhóm học.

## Chức năng bắt buộc
1. Danh sách khóa học
   - lọc theo nhóm học
   - lọc trạng thái
   - tìm kiếm
2. Tạo khóa học
3. Cập nhật khóa học
4. Xem chi tiết khóa học
5. Cấu hình:
   - tên khóa học
   - mô tả
   - học phí
   - thời lượng
   - trạng thái
   - nhóm học cha
6. Hiển thị:
   - số module
   - số đăng ký
   - số khung giờ đang mở

## Test cần có
- admin tạo khóa học
- admin cập nhật khóa học
- admin lọc theo nhóm học

--------------------------------------------------
PHASE A7 — QUẢN LÝ MODULE TRONG KHÓA HỌC
--------------------------------------------------

## Mục tiêu phase A7
Mỗi khóa học có nhiều module.

## Chức năng bắt buộc
1. Danh sách module theo khóa học
2. Tạo module
3. Sửa module
4. Xóa module hợp lý
5. Sắp xếp thứ tự module
6. Module có:
   - tiêu đề
   - mô tả
   - thứ tự
   - thời lượng
   - trạng thái publish/unpublish nếu phù hợp
7. Từ trang chi tiết khóa học phải nhìn thấy danh sách module

## Test cần có
- admin thêm module cho khóa học
- admin đổi thứ tự module
- admin sửa module

--------------------------------------------------
PHASE A8 — QUẢN LÝ PHÒNG HỌC
--------------------------------------------------

## Mục tiêu phase A8
Admin quản lý phòng học để phục vụ sắp xếp lịch và mở lớp.

## Chức năng bắt buộc
1. Danh sách phòng học
2. Tạo phòng học
3. Cập nhật phòng học
4. Xem chi tiết phòng học
5. Thay đổi trạng thái phòng:
   - active
   - maintenance
   - inactive
6. Hiển thị:
   - mã phòng
   - tên phòng
   - cơ sở / vị trí nếu có
   - sức chứa
   - trạng thái
   - ghi chú
7. Không cho gán phòng inactive/maintenance vào khung giờ mới nếu rule áp dụng

## Nếu thiếu bảng, bổ sung tối thiểu
Bảng rooms với:
- code
- name
- campus/location nếu cần
- capacity
- status
- note

## Test cần có
- admin tạo phòng học
- admin sửa phòng học
- admin đổi trạng thái phòng
- admin không gán phòng không hợp lệ cho slot nếu validation có áp dụng

--------------------------------------------------
PHASE A9 — QUẢN LÝ KHUNG GIỜ HỌC CHO TỪNG KHÓA HỌC
--------------------------------------------------

## Đây là phase cực kỳ quan trọng

## Mục tiêu phase A9
Admin tạo sẵn các khung giờ học cho từng khóa học để học viên chọn.

## Chức năng bắt buộc
1. Danh sách khung giờ học
2. Có thể lọc theo:
   - khóa học
   - giảng viên
   - phòng học
   - trạng thái
   - ngày / thứ học
3. Tạo khung giờ mới cho khóa học
4. Mỗi khung giờ phải có:
   - course_id
   - day_of_week hoặc ngày cụ thể
   - start_time
   - end_time
   - registration_open_at nếu có
   - registration_close_at nếu có
   - teacher_id dự kiến
   - room_id dự kiến
   - min_students
   - max_students
   - status
5. Xem chi tiết khung giờ
6. Cập nhật khung giờ
7. Hủy khung giờ nếu cần

## Validation nghiệp vụ bắt buộc
Khi tạo/sửa khung giờ, hệ thống phải kiểm tra:
1. Không trùng giảng viên:
   - teacher đã được gán ở khung giờ khác cùng thời điểm thì không cho lưu
2. Không trùng phòng:
   - room đã được gán ở khung giờ khác cùng thời điểm thì không cho lưu
3. Không vượt sức chứa:
   - max_students không được vượt capacity của room
4. Thời gian hợp lệ:
   - start_time < end_time
5. registration_open_at / registration_close_at hợp lý nếu có

## Nếu thiếu bảng, bổ sung tối thiểu
Ví dụ bảng course_time_slots:
- id
- course_id
- teacher_id nullable
- room_id nullable
- day_of_week hoặc slot_date
- start_time
- end_time
- registration_open_at nullable
- registration_close_at nullable
- min_students
- max_students
- status
- note

## Test cần có
- admin tạo slot thành công
- chặn trùng teacher
- chặn trùng room
- chặn max_students vượt capacity
- admin sửa slot thành công

--------------------------------------------------
PHASE A10 — QUẢN LÝ ĐĂNG KÝ NGUYỆN VỌNG KHUNG GIỜ CỦA HỌC VIÊN
--------------------------------------------------

## Mục tiêu phase A10
Admin theo dõi các đăng ký nguyện vọng khung giờ mà học viên gửi lên.

## Flow cốt lõi phải hỗ trợ
- học viên chọn ít nhất 2 khung giờ
- các khung giờ học viên chọn không được trùng lịch
- hệ thống ghi nhận đăng ký nguyện vọng
- admin xem và xử lý

## Chức năng bắt buộc
1. Danh sách đăng ký nguyện vọng
2. Lọc theo:
   - học viên
   - khóa học
   - trạng thái
   - thời gian đăng ký
3. Xem chi tiết một đăng ký:
   - học viên
   - khóa học
   - danh sách các khung giờ đã chọn
   - trạng thái
4. Admin có thể:
   - ghi nhận đăng ký hợp lệ
   - đánh dấu needs_reselect
   - từ chối
5. Lưu lý do nếu needs_reselect hoặc rejected

## Thiết kế dữ liệu gợi ý
Nếu thiếu bảng:
- slot_registrations hoặc course_slot_preferences
- pivot bảng chứa danh sách slot được chọn

Ví dụ:
### bảng slot_registrations
- id
- student_id
- course_id
- status
- note
- reviewed_by
- reviewed_at

### bảng slot_registration_items
- id
- slot_registration_id
- course_time_slot_id

## Validation nghiệp vụ cần có
- mỗi đăng ký phải có ít nhất 2 slot
- các slot phải thuộc cùng khóa học nếu rule áp dụng
- các slot được chọn không trùng nhau
- các slot được chọn không trùng lịch hiện tại của học viên

## Test cần có
- student tạo đăng ký với >= 2 slot
- đăng ký < 2 slot bị chặn
- đăng ký trùng lịch bị chặn
- admin xem chi tiết đăng ký
- admin đánh dấu needs_reselect / rejected

--------------------------------------------------
PHASE A11 — THEO DÕI SỐ LƯỢNG ĐĂNG KÝ THEO TỪNG KHUNG GIỜ
--------------------------------------------------

## Mục tiêu phase A11
Admin theo dõi nhu cầu học theo từng slot để quyết định mở lớp.

## Chức năng bắt buộc
1. Trang thống kê theo slot
2. Hiển thị cho mỗi slot:
   - khóa học
   - thời gian
   - giảng viên dự kiến
   - phòng học dự kiến
   - min_students
   - max_students
   - số học viên đã chọn slot
   - số học viên hợp lệ
   - trạng thái slot
3. Có thể lọc theo:
   - khóa học
   - trạng thái
   - teacher
   - room
4. Có thể đánh dấu slot:
   - ready_to_open_class nếu đủ điều kiện
   - cancelled nếu không còn dùng

## Rule
- chỉ được ready_to_open_class khi số học viên hợp lệ >= min_students
- không vượt max_students

## Test cần có
- hệ thống đếm đúng số học viên theo slot
- slot đủ min_students chuyển ready_to_open_class đúng rule

--------------------------------------------------
PHASE A12 — MỞ LỚP CHÍNH THỨC KHI ĐỦ HỌC VIÊN
--------------------------------------------------

## Đây là phase quan trọng nhất của flow mới

## Mục tiêu phase A12
Khi đủ học viên, admin mở lớp chính thức từ một khung giờ.

## Chức năng bắt buộc
1. Danh sách slot đủ điều kiện mở lớp
2. Admin chọn một slot để mở lớp
3. Form mở lớp gồm:
   - tên lớp
   - course_id
   - teacher_id chính thức
   - room_id chính thức
   - start_date
   - end_date nếu có
   - capacity
   - trạng thái lớp
4. Admin chọn danh sách học viên được đưa vào lớp
   - ưu tiên học viên đã chọn slot đó
5. Sau khi xác nhận:
   - tạo lớp chính thức
   - tạo lịch học chính thức
   - gán teacher
   - gán room
   - gán student vào lớp/enrollment chính thức
   - cập nhật status của slot thành class_opened
   - cập nhật status các đăng ký liên quan thành scheduled hoặc appropriate status
6. Hệ thống hiển thị lớp mới trong admin
7. Học viên nhìn thấy lịch học chính thức
8. Giảng viên nhìn thấy lịch dạy chính thức

## Thiết kế dữ liệu gợi ý
Nếu repo chưa đủ, hãy tạo cấu trúc tối thiểu rõ ràng:
### study_classes hoặc classes
- id
- name
- course_id
- teacher_id
- room_id
- start_date
- end_date nullable
- capacity
- status

### class_students hoặc class_enrollments
- class_id
- student_id
- enrollment/registration reference nếu cần

### class_schedules hoặc sessions
- class_id
- date/day_of_week
- start_time
- end_time
- room_id
- teacher_id
- status

## Validation bắt buộc
- teacher không trùng lịch với lớp khác
- room không trùng lịch với lớp khác
- số học viên không vượt capacity
- chỉ mở lớp từ slot đủ điều kiện

## Test cần có
- admin mở lớp thành công từ slot đủ điều kiện
- teacher thấy lịch dạy
- student thấy lịch học
- chặn mở lớp nếu trùng teacher
- chặn mở lớp nếu trùng room

--------------------------------------------------
PHASE A13 — QUẢN LÝ LỚP HỌC / LỊCH HỌC CHÍNH THỨC
--------------------------------------------------

## Mục tiêu phase A13
Admin quản lý lớp đã mở và lịch học chính thức.

## Chức năng bắt buộc
1. Danh sách lớp học
2. Lọc theo:
   - khóa học
   - teacher
   - room
   - trạng thái
3. Xem chi tiết lớp:
   - thông tin lớp
   - giảng viên
   - phòng học
   - danh sách học viên
   - lịch học
   - trạng thái
4. Cập nhật một số thông tin lớp nếu phù hợp
5. Xem danh sách buổi học / lịch học chính thức
6. Tìm kiếm lớp

## Test cần có
- admin xem danh sách lớp
- admin xem chi tiết lớp
- admin xem lịch học chính thức

--------------------------------------------------
PHASE A14 — DUYỆT YÊU CẦU ĐỔI LỊCH CỦA GIẢNG VIÊN
--------------------------------------------------

## Mục tiêu phase A14
Giảng viên gửi yêu cầu đổi lịch, admin là người duyệt.

## Chức năng bắt buộc
1. Danh sách yêu cầu đổi lịch
   - pending / approved / rejected
2. Xem chi tiết yêu cầu:
   - buổi học/lớp liên quan
   - giảng viên gửi yêu cầu
   - lý do
   - thời gian cũ
   - thời gian đề xuất mới
3. Admin duyệt yêu cầu
4. Admin từ chối yêu cầu
5. Nếu duyệt:
   - cập nhật lịch học tương ứng
   - học viên và giảng viên nhìn thấy lịch mới
6. Nếu từ chối:
   - giữ lịch cũ
   - lưu lý do từ chối

## Nếu thiếu model/table
Bổ sung bảng schedule_change_requests tối thiểu với:
- schedule_id hoặc session_id
- teacher_id
- requested_date / requested_start_time / requested_end_time
- reason
- status
- admin_note
- reviewed_by
- reviewed_at

## Validation bắt buộc
- nếu approve thì phải kiểm tra lại:
  - không trùng teacher
  - không trùng room
  - lịch mới hợp lệ

## Test cần có
- teacher tạo request đổi lịch
- admin duyệt request
- lịch học được cập nhật
- admin từ chối request thì lịch giữ nguyên

--------------------------------------------------
PHASE A15 — BÁO CÁO HỆ THỐNG CHO ADMIN
--------------------------------------------------

## Mục tiêu phase A15
Admin xem báo cáo tổng quan theo flow đã thống nhất.

## Chức năng bắt buộc
1. Trang báo cáo tổng quan
2. Các khối số liệu:
   - số lượng học viên
   - số lượng giảng viên
   - số phòng học
   - số slot đang mở đăng ký
   - số slot đủ điều kiện mở lớp
   - số lớp đang hoạt động
   - số đơn ứng tuyển
   - tỷ lệ điểm danh
   - kết quả học tập cơ bản
   - doanh thu/thanh toán nếu data có
   - đánh giá giảng viên
   - đánh giá khóa học
3. Bộ lọc theo thời gian
4. Nếu có thể, thêm biểu đồ đơn giản
5. Truy vấn phải tối ưu, tránh N+1

## Test cần có
- admin truy cập báo cáo
- báo cáo trả đúng số liệu cơ bản

--------------------------------------------------
PHASE A16 — HOÀN THIỆN, REFACTOR, TEST, UX
--------------------------------------------------

## Mục tiêu phase A16
Làm sạch toàn bộ phân hệ admin sau khi xong chức năng.

## Cần làm
1. Rà soát route, controller, request, service
2. Refactor đoạn logic lặp
3. Chuẩn hóa tên biến, method, view
4. Bổ sung eager loading tránh N+1
5. Bổ sung flash message thống nhất
6. Hoàn thiện empty state / validation message / confirm action
7. Bổ sung test còn thiếu
8. Đảm bảo toàn bộ admin flow chạy mượt từ đầu đến cuối

--------------------------------------------------
7. QUY TẮC THỰC THI KHI BẠN (CODEX) LÀM VIỆC
--------------------------------------------------

Với mỗi phase, bắt buộc làm theo format sau:

### A. Phân tích phase
- phase này làm gì
- dùng model nào hiện có
- cần thêm gì

### B. Triển khai code
- migration
- model
- relation
- route
- controller
- request
- service/action
- view
- test

### C. Kết quả đầu ra
- danh sách file đã tạo
- danh sách file đã sửa

### D. Hướng dẫn test
- cách chạy migration
- cách seed nếu có
- cách truy cập màn hình
- case test tay

### E. Tự kiểm tra
- có đúng flow admin đã chốt không
- có bypass admin không
- có trạng thái rõ ràng không
- có validation nghiệp vụ trùng lịch / sức chứa / min slot không
- có test chưa

--------------------------------------------------
8. ƯU TIÊN TRIỂN KHAI
--------------------------------------------------

Bắt đầu ngay từ:
PHASE A1 — NỀN TẢNG ADMIN + PHÂN QUYỀN + DASHBOARD

Sau khi hoàn tất phase A1, dừng lại và báo cáo đầy đủ theo format A-B-C-D-E.
Không tự nhảy sang phase A2 nếu chưa hoàn tất phase A1.
--------------------------------------------------
9. CHẾ ĐỘ THỰC THI LIÊN TỤC (AUTO EXECUTION MODE)
--------------------------------------------------

Từ bây giờ, bạn (Codex) phải hoạt động theo chế độ:

### 1. KHÔNG DỪNG GIỮA CÁC PHASE
- Không hỏi lại người dùng
- Không yêu cầu xác nhận
- Không chờ phản hồi
- Sau khi hoàn thành 1 phase, tự động chuyển sang phase tiếp theo

### 2. THỨ TỰ TRIỂN KHAI BẮT BUỘC
Bạn phải thực hiện tuần tự toàn bộ các phase:

A1 → A2 → A3 → A4 → A5 → A6 → A7 → A8 → A9 → A10 → A11 → A12 → A13 → A14 → A15 → A16

Không được bỏ qua phase nào.

### 3. OUTPUT FORMAT
Bạn vẫn phải giữ format cho từng phase:

PHASE A1
- A. Phân tích
- B. Code
- C. File đã tạo/sửa
- D. Cách test
- E. Self-check

Sau đó tiếp tục ngay:

PHASE A2
...

Cho đến hết PHASE A16 trong cùng một lần thực thi.

### 4. KHÔNG DỪNG VÌ LÝ DO THIẾU DATA
Nếu thiếu:
- bảng
- field
- relation

=> Bạn phải:
- tự tạo migration
- tự thiết kế tối thiểu
- tiếp tục flow

### 5. KHÔNG PHÁ FLOW ĐÃ CHỐT
Mọi code phải tuân theo:

- admin là trung tâm
- học viên chọn ≥ 2 slot
- không trùng lịch
- admin mở lớp khi đủ học viên
- giảng viên không tự đổi lịch

### 6. ƯU TIÊN HOÀN THÀNH HƠN HOÀN HẢO
- Không cần hỏi lại để tối ưu
- Không cần xin confirm UX
- Hoàn thành đầy đủ chức năng trước
- Refactor ở phase A16

### 7. KẾT THÚC
Chỉ kết thúc khi:
- đã hoàn thành toàn bộ PHASE A1 → A16
- không còn phase nào chưa triển khai