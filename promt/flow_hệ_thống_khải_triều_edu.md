# FLOW CHI TIẾT HỆ THỐNG WEBSITE GIÁO DỤC KHẢI TRIỀU

## 1. Mục tiêu hệ thống
Website Khải Triều là nền tảng giáo dục dùng để:

- quản lý học viên
- quản lý giảng viên
- quản lý khóa học, nhóm học, module học
- quản lý lịch học và sắp xếp lớp
- quản lý ứng tuyển giảng viên
- quản lý điểm danh, điểm số, đánh giá
- hỗ trợ kiểm tra, báo cáo và theo dõi tiến độ học tập

Hệ thống có **3 phân quyền chính**:

- **Admin**
- **Giảng viên**
- **Học viên**

Trong đó, **Admin là trung tâm phê duyệt và quyết định toàn bộ nghiệp vụ quan trọng**. Các hoạt động như duyệt học viên vào lớp, phân công giảng viên, sắp xếp lịch học, duyệt đơn ứng tuyển giảng viên, xử lý yêu cầu đổi lịch… đều phải qua admin.

---

## 2. Flow người dùng tổng quan

### 2.1. Người dùng truy cập hệ thống
Người dùng truy cập website và có thể:

- xem trang chủ
- xem giới thiệu
- xem danh sách khóa học
- xem thông tin môn học / nhóm học
- đăng ký tài khoản
- đăng nhập hệ thống

### 2.2. Đăng ký / đăng nhập
- Người dùng tạo tài khoản
- Hệ thống gửi OTP xác thực
- Người dùng nhập OTP để kích hoạt tài khoản
- Sau khi xác thực thành công, người dùng đăng nhập vào hệ thống
- Hệ thống điều hướng theo vai trò:
  - Admin → trang quản trị
  - Giảng viên → dashboard giảng viên
  - Học viên → dashboard học viên

---

## 3. Flow phân quyền hệ thống

### 3.1. Admin
Admin có quyền cao nhất trong hệ thống, chịu trách nhiệm:

- quản lý người dùng
- quản lý giảng viên và học viên riêng biệt
- quản lý nhóm học
- quản lý khóa học
- quản lý module trong khóa học
- duyệt đơn ứng tuyển giảng viên
- phân công giảng viên
- sắp xếp lịch học cho học viên
- duyệt đăng ký học
- xử lý yêu cầu đổi lịch
- theo dõi báo cáo, điểm danh, điểm số, tiến độ học tập

### 3.2. Giảng viên
Giảng viên chỉ được thao tác trong phạm vi lớp/nhóm học được phân công:

- xem lịch dạy
- xem danh sách học viên
- điểm danh
- nhập điểm / quản lý điểm
- đánh giá học viên
- xem tiến độ học viên
- gửi yêu cầu đổi lịch
- xem thông tin lớp được giao

### 3.3. Học viên
Học viên được phép thao tác với quá trình học tập của mình:

- đăng ký khóa học / lịch học
- thanh toán học phí
- xem lịch học
- xem điểm danh
- xem điểm học tập
- làm bài kiểm tra / quiz
- đánh giá giảng viên
- đánh giá khóa học
- theo dõi tiến độ học
- xem kết quả học tập

---

## 4. Flow chức năng Admin

### 4.1. Flow quản lý người dùng
#### Mục tiêu
Admin quản lý toàn bộ tài khoản trong hệ thống.

#### Quy trình
1. Admin đăng nhập vào trang quản trị
2. Vào menu **Quản lý người dùng**
3. Hệ thống chia thành:
   - Quản lý học viên
   - Quản lý giảng viên
4. Admin có thể:
   - xem danh sách
   - tìm kiếm / lọc
   - thêm mới
   - chỉnh sửa thông tin
   - khóa / mở tài khoản
   - xóa tài khoản nếu cần
5. Dữ liệu sau khi cập nhật sẽ được lưu vào hệ thống và ảnh hưởng trực tiếp đến quyền truy cập của người dùng

### 4.2. Flow quản lý đơn ứng tuyển giảng viên
#### Mục tiêu
Người muốn trở thành giảng viên gửi hồ sơ ứng tuyển và admin là người duyệt cuối cùng.

#### Quy trình
1. Ứng viên gửi đơn ứng tuyển giảng viên
2. Hệ thống lưu hồ sơ ở trạng thái **chờ duyệt**
3. Admin vào mục **Quản lý đơn ứng tuyển**
4. Admin xem:
   - thông tin cá nhân
   - trình độ / kinh nghiệm
   - kỹ năng / chuyên môn
   - minh chứng nếu có
5. Admin thực hiện một trong các hành động:
   - **duyệt**
   - **từ chối**
   - **yêu cầu bổ sung**
6. Nếu duyệt:
   - hệ thống chuyển trạng thái hồ sơ thành **đã duyệt**
   - tạo hoặc kích hoạt tài khoản giảng viên
   - gán quyền giảng viên cho người dùng
7. Nếu từ chối:
   - hệ thống cập nhật trạng thái từ chối
   - ứng viên nhận thông báo kết quả

### 4.3. Flow quản lý khóa học, nhóm học, module
#### Cấu trúc
- **Nhóm học**
  - chứa các **khóa học**
    - mỗi khóa học có nhiều **module**

#### Quy trình quản lý
1. Admin vào mục **Quản lý nhóm học**
2. Admin tạo nhóm học theo từng chương trình / môn / cấp độ
3. Trong mỗi nhóm học, admin tạo các khóa học
4. Trong mỗi khóa học, admin tạo các module bài học
5. Admin cấu hình:
   - tên khóa học
   - mô tả
   - học phí
   - thời lượng
   - lịch học dự kiến
   - giảng viên phụ trách
   - học viên tham gia
6. Sau khi thiết lập hoàn tất, khóa học được mở cho học viên đăng ký hoặc đưa vào quy trình xét duyệt

### 4.4. Flow sắp xếp lịch học cho học viên
#### Đây là flow quan trọng nhất của hệ thống

#### Mục tiêu
Học viên đăng ký lịch học nhưng **không tự vào lớp ngay**. Admin là người sắp xếp, duyệt và phân lớp.

#### Quy trình
1. Học viên chọn khóa học / nhóm học muốn tham gia
2. Học viên gửi yêu cầu đăng ký học, bao gồm:
   - thông tin khóa học
   - khung giờ mong muốn
   - ngày học mong muốn
   - nhu cầu cá nhân nếu có
3. Hệ thống ghi nhận yêu cầu ở trạng thái **chờ admin duyệt**
4. Admin vào mục **Quản lý đăng ký học**
5. Admin kiểm tra:
   - lịch trống của lớp
   - lịch của giảng viên
   - sĩ số lớp
   - trình độ phù hợp
   - khung giờ phù hợp với học viên
6. Admin ra quyết định:
   - xếp học viên vào nhóm/lớp có sẵn
   - tạo sắp xếp mới
   - từ chối nếu không phù hợp
7. Sau khi admin duyệt:
   - hệ thống cập nhật lịch học chính thức
   - học viên thấy lịch học trong dashboard
   - giảng viên thấy học viên trong danh sách lớp
8. Toàn bộ việc học chính thức chỉ bắt đầu sau khi admin xác nhận

### 4.5. Flow quản lý yêu cầu đổi lịch
#### Mục tiêu
Giảng viên không tự ý đổi lịch. Mọi thay đổi phải qua admin.

#### Quy trình
1. Giảng viên phát sinh nhu cầu đổi lịch
2. Giảng viên gửi yêu cầu đổi lịch, nêu rõ:
   - lớp liên quan
   - buổi học liên quan
   - lý do đổi lịch
   - thời gian đề xuất thay thế
3. Hệ thống lưu yêu cầu ở trạng thái **chờ duyệt**
4. Admin vào mục **Yêu cầu đổi lịch**
5. Admin kiểm tra:
   - lịch học viên
   - lịch giảng viên
   - khung giờ trống
   - ảnh hưởng đến tiến độ học
6. Admin quyết định:
   - duyệt đổi lịch
   - từ chối đổi lịch
7. Nếu duyệt:
   - hệ thống cập nhật lịch mới
   - gửi thông báo cho học viên và giảng viên
8. Nếu từ chối:
   - hệ thống giữ nguyên lịch cũ
   - thông báo lý do từ chối

### 4.6. Flow quản lý báo cáo
#### Mục tiêu
Admin theo dõi hoạt động toàn hệ thống.

#### Nội dung báo cáo có thể gồm
- số lượng học viên
- số lượng giảng viên
- số lớp đang hoạt động
- số đơn ứng tuyển
- tỷ lệ điểm danh
- kết quả học tập
- tiến độ khóa học
- doanh thu / thanh toán
- đánh giá giảng viên
- đánh giá khóa học

#### Quy trình
1. Admin vào mục **Báo cáo**
2. Chọn loại báo cáo
3. Chọn khoảng thời gian
4. Hệ thống tổng hợp dữ liệu
5. Admin xem, lọc, xuất báo cáo nếu cần

---

## 5. Flow chức năng Giảng viên

### 5.1. Flow xem lịch dạy
1. Giảng viên đăng nhập
2. Vào mục **Lịch dạy**
3. Hệ thống hiển thị:
   - ngày dạy
   - giờ dạy
   - lớp học
   - số lượng học viên
   - phòng / hình thức học
4. Giảng viên theo dõi lịch được admin phân công

### 5.2. Flow xem danh sách học viên
1. Giảng viên chọn lớp đang phụ trách
2. Hệ thống hiển thị danh sách học viên của lớp
3. Giảng viên có thể xem:
   - thông tin cơ bản
   - tình trạng học
   - điểm danh
   - kết quả học tập
   - tiến độ học

### 5.3. Flow điểm danh học viên
1. Đến buổi học, giảng viên mở danh sách lớp
2. Chọn buổi học tương ứng
3. Hệ thống hiển thị danh sách học viên
4. Giảng viên cập nhật trạng thái:
   - có mặt
   - vắng mặt
   - đi muộn
   - có phép
5. Hệ thống lưu dữ liệu điểm danh
6. Học viên có thể xem lại lịch sử điểm danh của mình
7. Admin có thể theo dõi thống kê điểm danh toàn hệ thống

### 5.4. Flow đánh giá học viên
1. Sau mỗi giai đoạn học hoặc sau mỗi buổi học
2. Giảng viên vào hồ sơ học viên
3. Nhập nhận xét:
   - thái độ học tập
   - mức độ tiếp thu
   - kỹ năng
   - tiến bộ
4. Hệ thống lưu nhận xét
5. Admin có thể xem báo cáo đánh giá
6. Học viên có thể xem nhận xét nếu hệ thống cho phép

### 5.5. Flow kiểm tra và quản lý điểm
1. Giảng viên vào lớp đang phụ trách
2. Chọn bài kiểm tra / cột điểm
3. Nhập hoặc cập nhật điểm cho từng học viên
4. Hệ thống lưu điểm
5. Học viên có thể xem điểm của mình
6. Admin có thể giám sát chất lượng giảng dạy và kết quả học tập

### 5.6. Flow yêu cầu đổi lịch học
1. Giảng viên chọn buổi học cần đổi
2. Gửi yêu cầu đổi lịch
3. Hệ thống chuyển yêu cầu đến admin
4. Chờ admin duyệt
5. Sau khi có kết quả:
   - nếu duyệt → lịch mới được cập nhật
   - nếu từ chối → giữ lịch cũ

---

## 6. Flow chức năng Học viên

### 6.1. Flow đăng ký học
1. Học viên đăng nhập
2. Vào danh sách khóa học / nhóm học
3. Chọn khóa học phù hợp
4. Nhập thông tin đăng ký:
   - ca học mong muốn
   - ngày học phù hợp
   - ghi chú nếu có
5. Gửi yêu cầu đăng ký
6. Hệ thống lưu trạng thái **chờ admin duyệt**
7. Admin kiểm tra và xếp lịch
8. Học viên nhận kết quả:
   - được xếp lớp
   - chờ bổ sung
   - bị từ chối

### 6.2. Flow thanh toán học phí
1. Sau khi được duyệt hoặc trong bước đăng ký
2. Học viên thực hiện thanh toán học phí
3. Hệ thống ghi nhận trạng thái thanh toán:
   - chưa thanh toán
   - đã thanh toán
   - chờ xác nhận
4. Admin kiểm tra thanh toán
5. Khi hợp lệ, học viên được xác nhận tham gia học chính thức

### 6.3. Flow xem lịch học
1. Học viên đăng nhập
2. Vào mục **Lịch học**
3. Hệ thống hiển thị:
   - ngày học
   - giờ học
   - giảng viên
   - lớp học
   - tình trạng buổi học
4. Nếu có đổi lịch do admin duyệt, hệ thống cập nhật ngay trên lịch

### 6.4. Flow xem điểm danh
1. Học viên vào mục **Điểm danh**
2. Hệ thống hiển thị lịch sử từng buổi học
3. Học viên xem được:
   - có mặt
   - vắng
   - đi muộn
   - có phép
4. Dùng để theo dõi kỷ luật học tập và đối chiếu khi cần

### 6.5. Flow xem điểm học tập
1. Học viên vào mục **Kết quả học tập**
2. Hệ thống hiển thị:
   - điểm kiểm tra
   - điểm quá trình
   - điểm tổng kết
   - nhận xét từ giảng viên
3. Học viên theo dõi tiến độ và năng lực học tập của mình

### 6.6. Flow làm kiểm tra / quiz
1. Học viên vào khóa học đang tham gia
2. Chọn bài kiểm tra hoặc quiz
3. Làm bài theo số câu hỏi hệ thống cung cấp
4. Nộp bài
5. Hệ thống chấm điểm hoặc lưu để giảng viên/admin xử lý
6. Kết quả được cập nhật vào hồ sơ học tập

### 6.7. Flow đánh giá giảng viên và khóa học
1. Sau khi học xong hoặc sau một giai đoạn học
2. Học viên vào mục **Đánh giá**
3. Chọn:
   - đánh giá giảng viên
   - đánh giá khóa học
4. Nhập số sao / nhận xét
5. Hệ thống lưu dữ liệu đánh giá
6. Admin dùng dữ liệu này để theo dõi chất lượng đào tạo

---

## 7. Flow nghiệp vụ chính toàn hệ thống

### 7.1. Flow từ đăng ký học đến học chính thức
1. Học viên đăng ký khóa học
2. Học viên chọn lịch mong muốn
3. Hệ thống lưu đăng ký chờ duyệt
4. Admin kiểm tra
5. Admin phân lớp / phân giảng viên / sắp lịch
6. Học viên thanh toán học phí
7. Admin xác nhận hoàn tất
8. Học viên bắt đầu học
9. Giảng viên điểm danh, nhập điểm, đánh giá
10. Học viên theo dõi tiến độ và làm bài kiểm tra

### 7.2. Flow từ ứng tuyển giảng viên đến được phân công giảng dạy
1. Ứng viên nộp đơn ứng tuyển
2. Admin xét duyệt
3. Nếu đạt → tạo tài khoản giảng viên
4. Admin phân công giảng viên vào khóa học / nhóm học
5. Giảng viên xem lịch dạy
6. Giảng viên quản lý lớp được giao

### 7.3. Flow xử lý thay đổi lịch học
1. Giảng viên gửi yêu cầu đổi lịch
2. Hệ thống ghi nhận yêu cầu
3. Admin kiểm tra tính phù hợp
4. Admin phê duyệt hoặc từ chối
5. Nếu duyệt → cập nhật lịch toàn hệ thống
6. Học viên và giảng viên nhận thông báo

---

## 8. Sơ đồ phân cấp chức năng

### 8.1. Admin
- Quản lý người dùng
  - Quản lý học viên
  - Quản lý giảng viên
- Quản lý nhóm học
- Quản lý khóa học
- Quản lý module
- Quản lý đơn ứng tuyển giảng viên
- Quản lý đăng ký học
- Quản lý phân lớp
- Quản lý lịch học
- Duyệt đổi lịch
- Quản lý báo cáo
- Theo dõi điểm danh, điểm số, đánh giá

### 8.2. Giảng viên
- Xem lịch dạy
- Xem danh sách học viên
- Điểm danh
- Đánh giá học viên
- Quản lý điểm
- Xem tiến độ lớp
- Gửi yêu cầu đổi lịch

### 8.3. Học viên
- Đăng ký học
- Chọn lịch học mong muốn
- Thanh toán
- Xem lịch học
- Xem điểm danh
- Xem điểm
- Làm kiểm tra
- Đánh giá giảng viên
- Đánh giá khóa học

---

## 9. Đặc điểm nghiệp vụ nổi bật của hệ thống

- Học viên **không tự động vào lớp** ngay sau khi đăng ký
- Giảng viên **không tự đổi lịch** mà phải gửi yêu cầu
- Toàn bộ hoạt động quan trọng đều cần **admin phê duyệt**
- Hệ thống tập trung vào **quản lý lịch học, quản lý lớp, quản lý chất lượng đào tạo**
- Có sự liên kết chặt giữa **khóa học – lịch học – giảng viên – học viên – đánh giá – báo cáo**

---

## 10. Kết luận
Website giáo dục Khải Triều vận hành theo mô hình quản trị tập trung:

**Học viên / Giảng viên gửi yêu cầu → Admin kiểm tra, duyệt và sắp xếp → Hệ thống cập nhật dữ liệu và thông báo kết quả**.

Cách tổ chức này giúp:
- kiểm soát chất lượng đào tạo
- đảm bảo lịch học hợp lý
- quản lý được giảng viên và học viên hiệu quả
- hỗ trợ thống kê, báo cáo và theo dõi hoạt động toàn hệ thống một cách chặt chẽ

