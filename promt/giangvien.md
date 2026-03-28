Bạn là senior Laravel developer đang tiếp tục phát triển dự án web giáo dục “khaitriedu”.

## 0. Bối cảnh dự án
Dự án là website giáo dục có 3 vai trò:
- Admin
- Giảng viên
- Học viên

Flow nghiệp vụ đã chốt và phải xem là SOURCE OF TRUTH:

### Quy tắc cốt lõi
- Admin là trung tâm phê duyệt và quyết định toàn bộ nghiệp vụ quan trọng
- Học viên không tự vào lớp ngay sau khi đăng ký
- Giảng viên không tự đổi lịch, chỉ được gửi yêu cầu đổi lịch
- Giảng viên chỉ thao tác trong phạm vi lớp / lịch / học viên được admin phân công
- Hệ thống tập trung vào quản lý lịch học, lớp học, điểm danh, điểm số, đánh giá và chất lượng đào tạo

Từ bây giờ chỉ tập trung làm **phân hệ GIẢNG VIÊN** theo từng phase độc lập.
Yêu cầu: phase nào xong phase đó, hoàn thiện đầy đủ rồi mới chuyển phase tiếp theo.

## 1. Công nghệ và nguyên tắc bắt buộc
Dùng đúng stack hiện tại của repo Laravel.
Giữ code sạch, dễ bảo trì, tách logic hợp lý.
Ưu tiên:
- Form Request để validate
- Service class / Action class nếu logic dài
- Middleware / policy / gate cho phân quyền giảng viên
- Eloquent relationship chuẩn
- Migration đầy đủ nếu thiếu bảng/cột
- Seeder/Faker cho dữ liệu mẫu nếu cần
- Feature test cho các flow chính
- Giao diện Blade hoặc giao diện đang dùng trong repo, đồng bộ style hiện có
- Không phá vỡ chức năng cũ nếu chưa cần thiết

## 2. Luật triển khai bắt buộc
1. Chỉ làm chức năng giảng viên theo đúng phase được giao
2. Mỗi phase phải hoàn thành đủ:
   - migration (nếu cần)
   - model / relation (nếu cần)
   - route
   - controller
   - request validation
   - service/action nếu cần
   - view teacher đầy đủ
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
6. Mọi route teacher phải được bảo vệ bằng middleware/phân quyền teacher
7. Giảng viên chỉ được xem và thao tác trên dữ liệu thuộc các lớp / buổi học / học viên mà mình được phân công
8. Không viết pseudo-code, phải viết code chạy được

## 3. Kiến trúc phân hệ Giảng viên phải bám theo flow đã chốt
Giảng viên có các chức năng:
1. Dashboard giảng viên
2. Xem lịch dạy
3. Xem danh sách lớp / nhóm học được phân công
4. Xem danh sách học viên trong lớp mình phụ trách
5. Điểm danh học viên
6. Đánh giá học viên
7. Kiểm tra và quản lý điểm
8. Gửi yêu cầu đổi lịch học
9. Theo dõi tiến độ lớp/học viên trong phạm vi được giao

Giảng viên KHÔNG có quyền:
- tự duyệt enrollment
- tự phân lớp
- tự phân công lịch học
- tự đổi lịch chính thức
- xem/sửa dữ liệu lớp không thuộc mình

## 4. Chuẩn route teacher
Tất cả chức năng teacher phải nằm dưới prefix:
- /teacher/...

Ví dụ:
- /teacher/dashboard
- /teacher/schedules
- /teacher/classes
- /teacher/classes/{class}/students
- /teacher/attendance
- /teacher/grades
- /teacher/student-reviews
- /teacher/schedule-change-requests

Tách route theo nhóm hợp lý. Nếu phù hợp, dùng file route riêng cho teacher.

## 5. Chuẩn trạng thái nghiệp vụ cần có
Khi làm các phase, luôn dùng status rõ ràng. Nếu repo chưa có thì bổ sung:
- class / schedule status: draft / scheduled / active / completed / cancelled
- attendance status: present / absent / late / excused
- grade status nếu cần: draft / published
- schedule change request status: pending / approved / rejected
- student progress nếu phù hợp: not_started / in_progress / completed

Ưu tiên dùng enum style nhất quán nếu dự án support, hoặc constant trong model.

--------------------------------------------------
PHASE T1 — NỀN TẢNG GIẢNG VIÊN + PHÂN QUYỀN + DASHBOARD
--------------------------------------------------

## Mục tiêu phase T1
Thiết lập nền tảng chắc chắn cho toàn bộ phân hệ giảng viên.

## Cần làm
1. Kiểm tra hệ thống đăng nhập hiện tại
2. Thiết lập bảo vệ route teacher:
   - chỉ giảng viên mới vào được
   - admin/học viên không vào nhầm khu teacher nếu business rule không cho
3. Tạo teacher dashboard
4. Hiển thị số liệu tổng quan của giảng viên đang đăng nhập:
   - tổng số lớp được phân công
   - tổng số buổi dạy sắp tới
   - tổng số học viên đang phụ trách
   - số buổi chưa điểm danh
   - số yêu cầu đổi lịch đã gửi
   - số bài/đợt chấm điểm cần xử lý nếu có
5. Tạo layout teacher thống nhất:
   - sidebar
   - topbar
   - breadcrumb nếu phù hợp
   - flash message
6. Chuẩn hóa menu teacher theo đúng flow chức năng

## Kết quả mong muốn
- Teacher login xong vào được dashboard
- User không phải teacher bị chặn khỏi khu teacher
- Có layout teacher dùng lại cho các phase sau
- Có dashboard thống kê cơ bản theo đúng dữ liệu của teacher hiện tại

## Test cần có
- teacher truy cập /teacher/dashboard thành công
- student bị chặn
- admin bị chặn nếu route teacher chỉ dành riêng cho teacher
- teacher chỉ thấy số liệu của chính mình

--------------------------------------------------
PHASE T2 — XEM LỊCH DẠY
--------------------------------------------------

## Mục tiêu phase T2
Giảng viên xem được toàn bộ lịch dạy được admin phân công.

## Chức năng bắt buộc
1. Trang danh sách lịch dạy
2. Hiển thị:
   - ngày dạy
   - giờ bắt đầu / kết thúc
   - lớp học / nhóm học / khóa học
   - số lượng học viên
   - trạng thái buổi học
   - hình thức học / phòng học nếu có
3. Hỗ trợ lọc:
   - theo ngày
   - theo tuần
   - theo tháng
   - theo trạng thái
4. Có thể có chế độ calendar hoặc list view nếu phù hợp với repo
5. Trang chi tiết buổi học/lịch dạy:
   - thông tin lớp
   - danh sách học viên
   - trạng thái điểm danh
   - ghi chú buổi học nếu có

## Rule bảo mật
- teacher chỉ xem được lịch dạy thuộc mình
- không xem được lịch của teacher khác

## Test cần có
- teacher xem được lịch của mình
- teacher không xem được lịch của người khác
- filter lịch hoạt động đúng

--------------------------------------------------
PHASE T3 — XEM DANH SÁCH LỚP / NHÓM HỌC ĐƯỢC PHÂN CÔNG
--------------------------------------------------

## Mục tiêu phase T3
Giảng viên thấy các lớp / nhóm học / khóa học mà mình đang phụ trách.

## Chức năng bắt buộc
1. Danh sách lớp được phân công
2. Hiển thị:
   - tên lớp / nhóm học
   - khóa học
   - số học viên
   - lịch học chính
   - trạng thái lớp
3. Trang chi tiết lớp
4. Trong chi tiết lớp hiển thị:
   - thông tin lớp
   - lịch học
   - danh sách học viên
   - tổng quan điểm danh
   - tổng quan điểm số nếu có
5. Có lọc theo:
   - active / completed / upcoming
   - khóa học
   - ngày học

## Rule bảo mật
- teacher chỉ xem được lớp mình phụ trách

## Test cần có
- teacher xem danh sách lớp của mình
- teacher không xem được lớp không thuộc mình
- teacher vào được trang chi tiết lớp của mình

--------------------------------------------------
PHASE T4 — XEM DANH SÁCH HỌC VIÊN TRONG LỚP PHỤ TRÁCH
--------------------------------------------------

## Mục tiêu phase T4
Giảng viên xem danh sách học viên trong từng lớp được giao.

## Chức năng bắt buộc
1. Từ chi tiết lớp, xem danh sách học viên
2. Thông tin hiển thị:
   - họ tên
   - email / số điện thoại nếu có
   - trạng thái học
   - tỷ lệ điểm danh
   - điểm trung bình nếu có
   - tiến độ học tập cơ bản
3. Trang chi tiết học viên trong phạm vi lớp:
   - thông tin cơ bản
   - lịch sử điểm danh trong lớp này
   - điểm số trong lớp này
   - nhận xét/đánh giá của giảng viên nếu có
4. Tìm kiếm học viên theo tên/email
5. Có thể lọc theo trạng thái học hoặc tỷ lệ chuyên cần nếu phù hợp

## Rule bảo mật
- teacher chỉ xem được học viên thuộc lớp mình phụ trách
- không được xem toàn bộ học viên hệ thống như admin

## Test cần có
- teacher xem được học viên lớp mình
- teacher không xem được học viên lớp người khác qua URL trực tiếp
- teacher xem được chi tiết học viên trong đúng phạm vi lớp

--------------------------------------------------
PHASE T5 — ĐIỂM DANH HỌC VIÊN
--------------------------------------------------

## Mục tiêu phase T5
Giảng viên điểm danh cho từng buổi học của lớp mình phụ trách.

## Chức năng bắt buộc
1. Chọn lớp hoặc buổi học cần điểm danh
2. Hiển thị danh sách học viên của buổi đó
3. Cho phép cập nhật trạng thái:
   - present
   - absent
   - late
   - excused
4. Có ô ghi chú nếu cần
5. Cho phép lưu điểm danh một lần cho cả lớp
6. Cho phép sửa điểm danh nếu business rule cho phép
7. Hiển thị lịch sử điểm danh của buổi học
8. Đồng bộ để:
   - học viên xem lại được
   - admin theo dõi được trong báo cáo

## Yêu cầu dữ liệu
Nếu chưa có bảng attendance/attendances hoặc session_attendances thì bổ sung bảng hợp lý với tối thiểu:
- session_id hoặc schedule_id
- student_id
- teacher_id
- status
- note
- attended_at hoặc recorded_at

## Rule bảo mật
- teacher chỉ điểm danh cho buổi học thuộc mình
- không điểm danh lớp người khác

## Test cần có
- teacher điểm danh buổi học của mình thành công
- teacher không điểm danh được buổi học không thuộc mình
- attendance lưu đúng trạng thái
- teacher có thể xem lại lịch sử điểm danh

--------------------------------------------------
PHASE T6 — QUẢN LÝ ĐIỂM / NHẬP ĐIỂM
--------------------------------------------------

## Mục tiêu phase T6
Giảng viên nhập và quản lý điểm cho học viên thuộc lớp mình phụ trách.

## Chức năng bắt buộc
1. Danh sách lớp có thể nhập điểm
2. Trong mỗi lớp:
   - danh sách học viên
   - các cột điểm hoặc bài kiểm tra
3. Chức năng:
   - tạo cột điểm / đợt chấm nếu cần
   - nhập điểm cho từng học viên
   - cập nhật điểm
   - thêm nhận xét ngắn nếu phù hợp
4. Có thể hiển thị:
   - điểm quá trình
   - điểm kiểm tra
   - điểm tổng kết
5. Nếu hệ thống đã có quiz/test:
   - tận dụng dữ liệu hiện có
   - teacher có thể xem và quản lý phần điểm liên quan
6. Nếu có status điểm:
   - draft
   - published
   thì hỗ trợ đúng luồng

## Yêu cầu dữ liệu
Nếu thiếu bảng grades/grade_items/score_records thì bổ sung theo cấu trúc tối thiểu, rõ ràng, không over-engineer.

## Rule bảo mật
- teacher chỉ nhập điểm cho lớp mình phụ trách
- không sửa điểm lớp khác

## Test cần có
- teacher nhập điểm thành công cho lớp của mình
- teacher cập nhật điểm thành công
- teacher không sửa điểm lớp khác
- student data mapping đúng

--------------------------------------------------
PHASE T7 — ĐÁNH GIÁ HỌC VIÊN
--------------------------------------------------

## Mục tiêu phase T7
Giảng viên đánh giá học viên sau buổi học, sau giai đoạn học hoặc trong lớp đang phụ trách.

## Chức năng bắt buộc
1. Tạo nhận xét/đánh giá cho học viên
2. Có thể đánh giá theo:
   - thái độ học tập
   - mức độ tiếp thu
   - tiến bộ
   - kỹ năng
   - ghi chú chung
3. Cho phép teacher xem danh sách đánh giá đã tạo
4. Có thể sửa đánh giá nếu business rule cho phép
5. Gắn đánh giá theo:
   - lớp
   - học viên
   - buổi học hoặc giai đoạn học nếu phù hợp
6. Admin có thể xem lại trong báo cáo/quản trị
7. Học viên có thể xem nếu hệ thống đã cho phép ở phân hệ student

## Yêu cầu dữ liệu
Nếu thiếu bảng student_reviews / student_evaluations thì bổ sung hợp lý với:
- teacher_id
- student_id
- class_id hoặc enrollment_id
- title nếu cần
- content
- rating nếu có
- created_at

## Rule bảo mật
- teacher chỉ đánh giá học viên thuộc lớp mình
- không đánh giá học viên ngoài phạm vi được giao

## Test cần có
- teacher tạo đánh giá cho học viên lớp mình
- teacher sửa đánh giá của mình
- teacher không đánh giá học viên ngoài lớp mình

--------------------------------------------------
PHASE T8 — GỬI YÊU CẦU ĐỔI LỊCH
--------------------------------------------------

## Mục tiêu phase T8
Giảng viên không tự đổi lịch, chỉ được gửi yêu cầu để admin duyệt.

## Chức năng bắt buộc
1. Danh sách yêu cầu đổi lịch của teacher hiện tại
2. Tạo yêu cầu đổi lịch mới từ buổi học/lịch dạy thuộc mình
3. Form yêu cầu gồm:
   - buổi học/lịch liên quan
   - thời gian hiện tại
   - thời gian đề xuất mới
   - lý do đổi lịch
4. Status rõ ràng:
   - pending
   - approved
   - rejected
5. Trang chi tiết yêu cầu đổi lịch
6. Teacher xem được phản hồi của admin nếu bị từ chối hoặc được duyệt
7. Teacher không được tự áp lịch mới vào hệ thống khi request còn pending

## Yêu cầu dữ liệu
Nếu chưa có bảng schedule_change_requests thì bổ sung tối thiểu:
- schedule_id hoặc session_id
- teacher_id
- requested_date
- requested_start_time
- requested_end_time
- reason
- status
- admin_note
- reviewed_by
- reviewed_at

## Rule bảo mật
- teacher chỉ gửi yêu cầu cho lịch của mình
- teacher không được duyệt request của chính mình
- teacher không tự cập nhật lịch chính thức

## Test cần có
- teacher tạo request đổi lịch thành công
- request mặc định là pending
- teacher không request cho buổi học không thuộc mình
- teacher xem được lịch sử request của mình

--------------------------------------------------
PHASE T9 — THEO DÕI TIẾN ĐỘ LỚP / HỌC VIÊN
--------------------------------------------------

## Mục tiêu phase T9
Giảng viên có màn hình theo dõi tổng quan chất lượng lớp mình phụ trách.

## Chức năng bắt buộc
1. Trang tổng quan theo từng lớp
2. Hiển thị:
   - tỷ lệ điểm danh
   - tỷ lệ hoàn thành bài/quiz nếu có
   - phân bố điểm số cơ bản
   - học viên có nguy cơ thấp chuyên cần
   - học viên có tiến độ tốt
3. Từ lớp, xem được tổng quan từng học viên
4. Có thể lọc theo:
   - buổi học
   - giai đoạn
   - trạng thái học tập
5. Truy vấn phải hợp lý, tránh N+1

## Mục tiêu UX
Teacher nhìn nhanh để biết:
- lớp nào đang ổn
- học viên nào cần chú ý
- nội dung nào cần can thiệp

## Test cần có
- teacher xem dashboard tiến độ lớp của mình
- dữ liệu tổng hợp chỉ thuộc lớp teacher phụ trách

--------------------------------------------------
PHASE T10 — HOÀN THIỆN, REFACTOR, TEST, UX
--------------------------------------------------

## Mục tiêu phase T10
Làm sạch toàn bộ phân hệ giảng viên sau khi xong chức năng.

## Cần làm
1. Rà soát route, controller, request, service
2. Refactor đoạn logic lặp
3. Chuẩn hóa tên biến, method, view
4. Bổ sung eager loading tránh N+1
5. Bổ sung flash message thống nhất
6. Hoàn thiện empty state / validation message / confirm action
7. Bổ sung test còn thiếu
8. Đảm bảo toàn bộ teacher flow chạy mượt từ đầu đến cuối

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
- có đúng flow teacher đã chốt không
- có giới hạn dữ liệu theo teacher không
- có bypass sang dữ liệu lớp khác không
- có trạng thái rõ ràng không
- có test chưa

--------------------------------------------------
ƯU TIÊN TRIỂN KHAI
--------------------------------------------------

Bắt đầu ngay từ:
1. PHASE T1 — NỀN TẢNG GIẢNG VIÊN + PHÂN QUYỀN + DASHBOARD

Sau khi hoàn tất phase T1, dừng lại và báo cáo đầy đủ theo format A-B-C-D-E.
Không tự nhảy sang phase T2 nếu chưa hoàn tất phase T1.