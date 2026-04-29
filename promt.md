Bạn là một AI code assistant. Hãy tạo cho tôi 2 file hoàn chỉnh dựa trên mô tả hệ thống bên dưới:

1) `usecase_khaitriedu.md`
2) `usecase_khaitriedu.drawio.xml`

Mục tiêu:
- Viết 1 file Markdown mô tả sơ đồ use case tổng quát của hệ thống web KhaiTriEdu.
- Viết 1 file XML chuẩn để import trực tiếp vào draw.io / diagrams.net.
- Sơ đồ phải là sơ đồ use case tổng quát, rõ ràng, dễ đọc, dùng tiếng Việt.
- Không dùng Mermaid, không dùng PlantUML trong file XML.
- File `.drawio.xml` phải là XML hợp lệ của draw.io, có thể import được.
- Hãy tự sinh đầy đủ nội dung file, không để placeholder.

====================
BỐI CẢNH HỆ THỐNG
====================

Tên hệ thống: KhaiTriEdu

Đây là hệ thống quản lý trung tâm đào tạo, có web public và các cổng riêng cho từng vai trò. Flow tổng quát của web như sau:

- Khách truy cập vào website để xem thông tin trung tâm, khóa học, giáo viên, bài viết, trợ giúp, chính sách.
- Nếu có nhu cầu học, học viên đăng nhập vào hệ thống để xem dashboard, lịch học, điểm, đăng ký lớp học hoặc gửi yêu cầu học theo lịch riêng.
- Hệ thống có bước kiểm tra trùng/xung đột lịch khi đăng ký hoặc xếp lịch.
- Quản trị viên quản lý học viên, giảng viên, môn học, khóa học, module, phòng học, khung giờ, ghi danh, xếp lịch, mở lớp, duyệt yêu cầu đổi lịch, duyệt đơn ứng tuyển giảng viên, xem báo cáo.
- Giảng viên đăng nhập để xem lịch dạy, xem lớp được phân công, điểm danh, nhập điểm, đánh giá học viên, gửi yêu cầu đổi lịch.
- Học viên sau khi tham gia học có thể làm quiz và xem chứng chỉ.
- Website còn có luồng ứng tuyển giảng viên, ứng viên nộp hồ sơ và quản trị viên xét duyệt.

====================
ACTOR CHÍNH
====================

Sơ đồ use case phải có 5 actor:

1. Khách
2. Học viên
3. Giảng viên
4. Quản trị viên
5. Ứng viên giảng viên

====================
NGHIỆP VỤ THEO TỪNG ACTOR
====================

1. Khách
- Xem trang chủ
- Xem danh sách khóa học
- Xem chi tiết khóa học
- Xem giảng viên
- Xem bài viết / tin tức
- Xem trợ giúp
- Xem điều khoản / chính sách
- Gửi liên hệ
- Nộp đơn ứng tuyển giảng viên

2. Học viên
- Đăng nhập
- Xem dashboard
- Xem lịch học
- Xem điểm
- Xem lớp đã đăng ký
- Đăng ký lớp học
- Gửi yêu cầu học theo lịch riêng
- Làm quiz
- Xem chứng chỉ

3. Giảng viên
- Đăng nhập
- Xem dashboard
- Xem lịch dạy
- Xem lớp được phân công
- Điểm danh học viên
- Nhập điểm
- Đánh giá học viên
- Gửi yêu cầu đổi lịch

4. Quản trị viên
- Đăng nhập
- Xem dashboard
- Quản lý học viên
- Quản lý giảng viên
- Quản lý người dùng
- Quản lý phòng ban
- Quản lý nhóm học
- Quản lý môn học
- Quản lý khóa học
- Quản lý module
- Quản lý phòng học
- Quản lý khung giờ
- Quản lý ghi danh
- Duyệt yêu cầu ghi danh
- Xếp lịch lớp học
- Mở lớp học
- Duyệt yêu cầu đổi lịch
- Duyệt đơn ứng tuyển giảng viên
- Xem báo cáo thống kê

5. Ứng viên giảng viên
- Nộp đơn ứng tuyển

====================
QUAN HỆ INCLUDE / EXTEND
====================

Hãy thể hiện một số quan hệ chính trong use case:

- "Đăng ký lớp học" <<include>> "Kiểm tra xung đột lịch"
- "Gửi yêu cầu học theo lịch riêng" <<include>> "Kiểm tra xung đột lịch"
- "Xếp lịch lớp học" <<include>> "Kiểm tra xung đột lịch"
- "Quản lý khóa học" <<include>> "Quản lý module"
- "Duyệt đơn ứng tuyển giảng viên" <<include>> "Xem hồ sơ ứng tuyển"
- "Theo dõi quá trình học" có thể được thể hiện bằng việc liên kết logic với:
  - Điểm danh học viên
  - Nhập điểm
  - Đánh giá học viên

Nếu cần, có thể thêm một use case phụ "Xem hồ sơ ứng tuyển".

====================
YÊU CẦU FILE MARKDOWN
====================

File `usecase_khaitriedu.md` phải có cấu trúc rõ ràng như sau:

1. Tiêu đề
2. Giới thiệu ngắn về hệ thống
3. Mô tả flow tổng quát của web
4. Danh sách actor
5. Nghiệp vụ chi tiết của từng actor
6. Quan hệ include / extend quan trọng
7. Mô tả ngắn cách bố trí sơ đồ use case
8. Kết luận ngắn

Yêu cầu viết:
- Viết bằng tiếng Việt
- Văn phong học thuật, dễ đưa vào báo cáo
- Trình bày gọn, rõ, không lan man
- Dùng heading Markdown chuẩn

====================
YÊU CẦU FILE DRAW.IO XML
====================

File `usecase_khaitriedu.drawio.xml` phải:
- Là XML draw.io hợp lệ
- Import được trực tiếp vào draw.io / diagrams.net
- Có 1 trang tên là: `Use Case Tong Quat`
- Có khung hệ thống tên: `Hệ thống KhaiTriEdu`
- Có 5 actor đặt bên ngoài khung hệ thống
- Các use case là hình ellipse bên trong khung hệ thống
- Có đường nối actor tới các use case tương ứng
- Các quan hệ <<include>> dùng đường nét đứt và mũi tên đúng kiểu use case
- Bố cục cân đối, dễ nhìn, tránh chồng chéo
- Font hiển thị tốt tiếng Việt
- Kích thước canvas đủ rộng để nhìn rõ toàn bộ sơ đồ

Gợi ý bố cục:
- Bên trái: Khách, Học viên
- Bên phải: Giảng viên, Quản trị viên, Ứng viên giảng viên
- Trung tâm trong system boundary: toàn bộ use case nhóm theo chức năng
- Nhóm use case public gần actor Khách
- Nhóm use case học viên ở phần giữa-trái
- Nhóm use case giảng viên ở phần giữa-phải
- Nhóm use case admin ở phần trung tâm / phải
- Use case “Kiểm tra xung đột lịch” đặt ở trung tâm dưới để nối include từ nhiều use case
- Use case “Xem hồ sơ ứng tuyển” đặt gần “Duyệt đơn ứng tuyển giảng viên”

====================
RÀNG BUỘC QUAN TRỌNG
====================

- Không giải thích dài dòng ngoài yêu cầu.
- Hãy tạo nội dung hoàn chỉnh của cả 2 file.
- Nếu cần, hãy tự tối ưu tên use case để sơ đồ gọn hơn nhưng vẫn giữ đúng nghiệp vụ.
- Ưu tiên khả năng import draw.io thành công.
- Không trả lời kiểu hướng dẫn chung chung.
- Hãy xuất kết quả theo dạng:

=== FILE: usecase_khaitriedu.md ===
[nội dung file]

=== FILE: usecase_khaitriedu.drawio.xml ===
[nội dung file]

Hãy bắt đầu tạo đầy đủ 2 file ngay bây giờ. 