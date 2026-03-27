Bạn là senior Laravel developer đang tiếp tục phát triển dự án web giáo dục tên “khaitriedu”.

## 0. Bối cảnh dự án
Dự án là website giáo dục có 3 vai trò:
- Admin
- Giảng viên
- Học viên

Flow nghiệp vụ đã được chốt và phải xem là SOURCE OF TRUTH:
- Admin là trung tâm phê duyệt và quyết định toàn bộ nghiệp vụ quan trọng
- Học viên không tự vào lớp ngay sau khi đăng ký
- Giảng viên không tự đổi lịch, chỉ được gửi yêu cầu
- Mọi hoạt động quan trọng đều phải qua admin duyệt
- Hệ thống tập trung vào quản lý lịch học, quản lý lớp, quản lý chất lượng đào tạo

Từ bây giờ chỉ tập trung làm **chức năng ADMIN** theo từng phase độc lập.
Yêu cầu: phase nào xong phase đó, hoàn thiện đầy đủ rồi mới chuyển phase tiếp theo.

## 1. Công nghệ và nguyên tắc bắt buộc
Dùng đúng stack hiện tại của repo Laravel.
Giữ code sạch, dễ bảo trì, tách logic hợp lý.
Ưu tiên:
- Form Request để validate
- Service class / Action class nếu logic dài
- Policy / middleware / gate cho phân quyền admin
- Eloquent relationship chuẩn
- Migration đầy đủ nếu thiếu cột/bảng
- Seeder/Faker cho dữ liệu mẫu nếu cần
- Feature test cho các flow chính
- Giao diện Blade hoặc giao diện đang dùng trong repo, đồng bộ style hiện có
- Không phá vỡ chức năng cũ nếu chưa cần thiết

## 2. Luật triển khai bắt buộc
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
5. Nếu dữ liệu hiện tại của repo chưa đủ để support phase, hãy thêm migration tối thiểu, nhưng phải giữ naming rõ ràng
6. Mọi route admin phải được bảo vệ bằng middleware/phân quyền admin
7. Không viết pseudo-code, phải viết code chạy được

## 3. Kiến trúc phân hệ Admin phải bám theo flow đã chốt
Admin có các chức năng:
1. Quản lý người dùng
   - quản lý học viên riêng
   - quản lý giảng viên riêng
2. Quản lý đơn ứng tuyển giảng viên
3. Quản lý nhóm học
4. Quản lý khóa học
5. Quản lý module trong khóa học
6. Quản lý đăng ký học
7. Sắp xếp lịch học cho học viên
8. Duyệt / từ chối yêu cầu đổi lịch của giảng viên
9. Quản lý báo cáo hệ thống

Mỗi chức năng phải có trạng thái rõ ràng, màn hình rõ ràng, thao tác rõ ràng.

## 4. Chuẩn route admin
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
- /admin/enrollments
- /admin/schedules
- /admin/schedule-change-requests
- /admin/reports

Tách route theo nhóm hợp lý. Nếu phù hợp, chia file route admin riêng.

## 5. Chuẩn trạng thái nghiệp vụ cần có
Khi làm các phase, luôn dùng status rõ ràng. Nếu repo chưa có thì bổ sung:
- user status: active / inactive / locked
- teacher application status: pending / approved / rejected / needs_revision
- enrollment status: pending / approved / rejected / scheduled / active / completed
- payment status: unpaid / pending / paid / failed
- schedule change request status: pending / approved / rejected
- class/session attendance status: present / absent / late / excused

Ưu tiên dùng enum style nhất quán nếu dự án support, hoặc constant trong model.

--------------------------------------------------
PHASE 1 — NỀN TẢNG ADMIN + PHÂN QUYỀN + DASHBOARD
--------------------------------------------------

## Mục tiêu phase 1
Thiết lập nền tảng chắc chắn cho toàn bộ phân hệ admin.

## Cần làm
1. Kiểm tra hệ thống đăng nhập hiện tại
2. Thiết lập bảo vệ route admin:
   - chỉ admin mới vào được
   - giảng viên/học viên không được vào
3. Tạo admin dashboard
4. Hiển thị số liệu tổng quan:
   - tổng học viên
   - tổng giảng viên
   - tổng đơn ứng tuyển đang chờ
   - tổng đăng ký học đang chờ duyệt
   - tổng lớp/khóa học đang hoạt động
   - tổng yêu cầu đổi lịch đang chờ
5. Tạo layout admin thống nhất:
   - sidebar
   - topbar
   - breadcrumb nếu phù hợp
   - thông báo session success/error
6. Chuẩn hóa menu admin theo đúng flow chức năng

## Kết quả mong muốn
- Admin login xong vào được dashboard
- User không phải admin bị chặn khỏi khu vực admin
- Có layout admin dùng lại cho các phase sau
- Có dashboard thống kê cơ bản

## Test cần có
- admin truy cập /admin/dashboard thành công
- student bị chặn
- teacher bị chặn

--------------------------------------------------
PHASE 2 — QUẢN LÝ NGƯỜI DÙNG: HỌC VIÊN
--------------------------------------------------

## Mục tiêu phase 2
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
   - lịch học hiện tại nếu có
   - tình trạng thanh toán nếu có
3. Tạo học viên mới
4. Cập nhật thông tin học viên
5. Khóa / mở khóa tài khoản học viên
6. Xóa mềm hoặc xóa theo cách phù hợp với repo
7. Gắn role học viên đúng chuẩn nếu hệ thống role chưa rõ
8. Hiển thị lịch sử hoạt động học tập cơ bản nếu data đã có

## Yêu cầu giao diện
- Trang index học viên
- Trang create
- Trang edit
- Trang show
- Nút khóa/mở khóa
- Thông báo thao tác thành công/thất bại

## Validation
- email unique
- số điện thoại unique nếu có
- dữ liệu bắt buộc phải hợp lý

## Test cần có
- admin xem danh sách học viên
- admin tạo học viên
- admin sửa học viên
- admin khóa học viên
- user không phải admin không truy cập được

--------------------------------------------------
PHASE 3 — QUẢN LÝ NGƯỜI DÙNG: GIẢNG VIÊN
--------------------------------------------------

## Mục tiêu phase 3
Admin quản lý giảng viên riêng biệt.

## Chức năng bắt buộc
1. Danh sách giảng viên
   - tìm kiếm
   - lọc trạng thái
2. Xem chi tiết giảng viên
   - thông tin cá nhân
   - chuyên môn nếu có
   - lớp/khóa học đang phụ trách
   - lịch dạy
   - trạng thái tài khoản
3. Tạo giảng viên mới
4. Cập nhật giảng viên
5. Khóa / mở khóa tài khoản giảng viên
6. Gán role giảng viên
7. Có thể xem thống kê cơ bản:
   - số lớp đang dạy
   - số học viên phụ trách
   - số yêu cầu đổi lịch đã gửi nếu có

## Test cần có
- admin xem danh sách giảng viên
- admin tạo giảng viên
- admin cập nhật giảng viên
- admin khóa giảng viên

--------------------------------------------------
PHASE 4 — QUẢN LÝ ĐƠN ỨNG TUYỂN GIẢNG VIÊN
--------------------------------------------------

## Mục tiêu phase 4
Xây đúng flow:
Ứng viên gửi đơn → admin duyệt/từ chối/yêu cầu bổ sung → nếu duyệt thì trở thành giảng viên.

## Chức năng bắt buộc
1. Danh sách đơn ứng tuyển
   - lọc theo status: pending / approved / rejected / needs_revision
   - tìm kiếm theo tên/email/chuyên môn
2. Xem chi tiết đơn
   - thông tin cá nhân
   - trình độ
   - kinh nghiệm
   - kỹ năng / chuyên môn
   - ghi chú
   - file minh chứng nếu repo có hỗ trợ
3. Admin duyệt đơn
4. Admin từ chối đơn
5. Admin yêu cầu bổ sung hồ sơ
6. Khi duyệt:
   - cập nhật trạng thái application = approved
   - tạo mới user hoặc nâng role hiện có thành teacher tùy cấu trúc data
   - đảm bảo teacher được kích hoạt đúng
7. Khi từ chối:
   - lưu lý do từ chối
8. Khi yêu cầu bổ sung:
   - lưu ghi chú phản hồi

## Yêu cầu dữ liệu
Nếu thiếu các field như:
- status
- admin_note
- reviewed_by
- reviewed_at
- rejection_reason
thì bổ sung migration hợp lý.

## Test cần có
- admin xem danh sách application
- admin duyệt application
- admin từ chối application
- khi duyệt thì user trở thành teacher

--------------------------------------------------
PHASE 5 — QUẢN LÝ NHÓM HỌC
--------------------------------------------------

## Mục tiêu phase 5
Admin quản lý nhóm học là lớp logic lớn chứa các khóa học.

## Chức năng bắt buộc
1. Danh sách nhóm học
2. Tạo nhóm học
3. Cập nhật nhóm học
4. Xem chi tiết nhóm học
5. Ẩn/ngừng hoạt động nhóm học nếu cần
6. Hiển thị trong nhóm học:
   - tên nhóm
   - mô tả
   - cấp độ / chương trình nếu có
   - trạng thái
   - số khóa học bên trong
7. Không cho xóa cứng nếu đang có khóa học liên kết, thay bằng soft delete hoặc inactive

## Test cần có
- admin tạo nhóm học
- admin sửa nhóm học
- admin không xóa cứng nhóm có dữ liệu phụ thuộc

--------------------------------------------------
PHASE 6 — QUẢN LÝ KHÓA HỌC
--------------------------------------------------

## Mục tiêu phase 6
Admin quản lý khóa học nằm trong nhóm học.

## Chức năng bắt buộc
1. Danh sách khóa học
   - lọc theo nhóm học
   - lọc trạng thái
   - tìm kiếm
2. Tạo khóa học
3. Cập nhật khóa học
4. Xem chi tiết khóa học
5. Cấu hình khóa học:
   - tên khóa học
   - mô tả
   - học phí
   - thời lượng
   - trạng thái
   - nhóm học cha
   - giảng viên phụ trách nếu business rule cho phép gán tại đây
6. Quản lý trạng thái khóa học:
   - draft / open / closed / archived hoặc mapping tương thích với repo
7. Hiển thị số module
8. Hiển thị số học viên đăng ký

## Test cần có
- admin tạo khóa học
- admin cập nhật khóa học
- admin lọc theo nhóm học

--------------------------------------------------
PHASE 7 — QUẢN LÝ MODULE TRONG KHÓA HỌC
--------------------------------------------------

## Mục tiêu phase 7
Mỗi khóa học có nhiều module.

## Chức năng bắt buộc
1. Danh sách module theo khóa học
2. Tạo module
3. Sửa module
4. Xóa module hợp lý
5. Sắp xếp thứ tự module
6. Module có thể có:
   - tiêu đề
   - mô tả
   - thứ tự
   - thời lượng
   - trạng thái publish/unpublish nếu phù hợp
7. Giao diện từ chi tiết khóa học phải nhìn thấy danh sách module

## Test cần có
- admin thêm module cho khóa học
- admin đổi thứ tự module
- admin sửa module

--------------------------------------------------
PHASE 8 — QUẢN LÝ ĐĂNG KÝ HỌC
--------------------------------------------------

## Mục tiêu phase 8
Bám đúng flow:
Học viên đăng ký → status pending → admin xét duyệt → xếp lớp/lịch → học viên mới học chính thức.

## Chức năng bắt buộc
1. Danh sách đăng ký học
   - lọc theo pending / approved / rejected / scheduled / active
   - tìm kiếm theo học viên / khóa học
2. Xem chi tiết đăng ký
   - học viên
   - khóa học / nhóm học
   - ngày học mong muốn
   - khung giờ mong muốn
   - ghi chú
   - trạng thái
3. Admin duyệt đăng ký
4. Admin từ chối đăng ký
5. Admin đánh dấu cần bổ sung nếu muốn
6. Chỉ admin mới được chuyển từ pending sang approved/scheduled
7. Ghi log người duyệt và thời gian duyệt nếu có thể

## Nếu repo đang dùng Enrollment
Hãy tận dụng model đó, nhưng chuẩn hóa status và màn hình admin cho đúng flow.

## Test cần có
- student tạo enrollment pending
- admin xem enrollment pending
- admin approved enrollment
- admin rejected enrollment

--------------------------------------------------
PHASE 9 — SẮP XẾP LỊCH HỌC CHO HỌC VIÊN
--------------------------------------------------

## Đây là phase quan trọng nhất
Admin là người sắp xếp lịch học chính thức.

## Mục tiêu phase 9
Sau khi đăng ký học được admin xử lý, admin phải có màn hình để:
- phân lớp
- gán giảng viên
- chọn lịch học
- xác nhận lịch chính thức

## Chức năng bắt buộc
1. Danh sách đăng ký cần xếp lịch
2. Màn hình xếp lịch cho từng đăng ký
3. Admin chọn:
   - nhóm/lớp có sẵn hoặc tạo sắp xếp mới
   - giảng viên
   - ngày bắt đầu
   - khung giờ học
   - số buổi hoặc lịch lặp nếu hệ thống support
4. Kiểm tra hợp lệ:
   - không trùng lịch giảng viên
   - không vượt sĩ số lớp
   - khóa học đúng nhóm học
   - học viên chưa bị xếp trùng lịch bất hợp lý
5. Sau khi xác nhận:
   - enrollment chuyển sang scheduled hoặc active
   - lịch học hiển thị cho học viên
   - lịch dạy hiển thị cho giảng viên
6. Có trang danh sách lịch học toàn hệ thống
7. Có thể lọc theo:
   - giảng viên
   - học viên
   - khóa học
   - ngày
8. Nếu cần bổ sung bảng sessions/schedules/classes thì thêm migration hợp lý, nhưng phải thiết kế rõ ràng

## Kỳ vọng thiết kế dữ liệu
Nếu hiện repo chưa đủ, hãy thiết kế một cấu trúc tối thiểu, ví dụ:
- classes hoặc study_classes
- schedules hoặc class_schedules
- teacher_id
- course_id
- start_date
- end_date
- day_of_week / start_time / end_time
- capacity
- status

Chỉ tạo cái gì thật cần thiết để flow chạy được.

## Test cần có
- admin xếp lịch cho enrollment
- teacher nhìn thấy lịch dạy sau khi xếp
- student nhìn thấy lịch học sau khi xếp
- không cho xếp lịch trùng teacher nếu có validation

--------------------------------------------------
PHASE 10 — DUYỆT YÊU CẦU ĐỔI LỊCH CỦA GIẢNG VIÊN
--------------------------------------------------

## Mục tiêu phase 10
Giảng viên gửi yêu cầu đổi lịch, admin là người duyệt.

## Chức năng bắt buộc
1. Danh sách yêu cầu đổi lịch
   - pending / approved / rejected
2. Xem chi tiết yêu cầu
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

## Test cần có
- teacher tạo request đổi lịch
- admin duyệt request
- lịch học được cập nhật
- admin từ chối request thì lịch giữ nguyên

--------------------------------------------------
PHASE 11 — BÁO CÁO HỆ THỐNG CHO ADMIN
--------------------------------------------------

## Mục tiêu phase 11
Admin xem báo cáo tổng quan theo flow đã thống nhất.

## Chức năng bắt buộc
1. Trang báo cáo tổng quan
2. Các khối số liệu:
   - số lượng học viên
   - số lượng giảng viên
   - số lớp đang hoạt động
   - số đơn ứng tuyển
   - tỷ lệ điểm danh
   - kết quả học tập cơ bản
   - doanh thu/thanh toán nếu data có
   - đánh giá giảng viên
   - đánh giá khóa học
3. Bộ lọc theo thời gian
4. Nếu có thể, thêm biểu đồ đơn giản
5. Truy vấn phải tối ưu, không N+1

## Test cần có
- admin truy cập báo cáo
- báo cáo trả đúng số liệu cơ bản

--------------------------------------------------
PHASE 12 — HOÀN THIỆN, REFACTOR, TEST, UX
--------------------------------------------------

## Mục tiêu phase 12
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
QUY TẮC THỰC THI KHI BẠN (CODEX) LÀM VIỆC
--------------------------------------------------

## Với mỗi phase, bắt buộc làm theo format sau:

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
- có test chưa

--------------------------------------------------
ƯU TIÊN TRIỂN KHAI
--------------------------------------------------

Bắt đầu ngay từ:
1. PHASE 1 — NỀN TẢNG ADMIN + PHÂN QUYỀN + DASHBOARD

Sau khi hoàn tất phase 1, dừng lại và báo cáo đầy đủ theo format A-B-C-D-E.
Không tự nhảy sang phase 2 nếu chưa hoàn tất phase 1.