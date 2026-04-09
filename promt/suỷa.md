Bạn là senior Laravel architect/developer. Hãy tiếp tục phát triển đồ án Education Management System trên codebase Laravel 12 hiện có của tôi, không tạo project mới và không đổi stack sang NodeJS, Django, NextJS hay React nếu tôi chưa yêu cầu.

Bối cảnh hiện tại:
- Stack đang dùng: PHP 8.2, Laravel 12, Blade/Vite.
- Hệ thống đã có RBAC với 3 vai trò: Admin, Giảng viên, Học viên.
- Route đã tách sẵn theo prefix `/admin`, `/teacher`, `/student`.
- Dự án đã có nhiều model/service/test; hãy đọc code trước khi sửa.
- Giữ tương thích với cấu trúc hiện có, đặc biệt các bảng tiếng Việt như `nguoi_dung`, `dang_ky`, `khoa_hoc`, `lop_hoc`, `lich_hoc`.
- Không refactor phá cấu trúc cũ nếu chưa thật sự cần. Ưu tiên migration tăng dần, service rõ ràng, test đầy đủ.

Mục tiêu:
Xây dựng và hoàn thiện hệ thống web quản lý giáo dục có RBAC gồm 3 role Admin, Giảng viên, Học viên; code rõ ràng, dễ mở rộng, tách logic theo từng role, bám sát mô hình vận hành thực tế.

Yêu cầu nghiệp vụ:
1. Xác thực và phân quyền
- Đăng nhập, đăng xuất, kiểm tra quyền theo role.
- Mỗi role có dashboard riêng và chỉ thấy chức năng của mình.

2. Admin có toàn quyền CRUD
- Quản lý giảng viên, học viên, phòng ban, phòng học, nhóm học, khóa học, lớp học, đơn ứng tuyển giảng viên, báo cáo, đăng ký học, lịch học.
- Quản lý mở lớp, xếp lịch, duyệt yêu cầu đổi lịch, theo dõi trạng thái ghi danh.

3. Flow dữ liệu chính
- Admin tạo phòng ban, phòng học, giảng viên và gán giảng viên vào phòng ban.
- Admin tạo nhóm học, từ đó tạo môn học/khóa học nền tảng.
- Admin tạo lịch học cố định bằng cách gán giảng viên, phòng học, thời gian học để sinh ra lớp học.
- Lớp học hiển thị cho học viên đăng ký.

4. Logic đăng ký học
- Học viên có thể đăng ký theo lớp có sẵn.
- Học viên cũng có thể gửi yêu cầu lịch học riêng.
- Admin xử lý yêu cầu lịch riêng, kiểm tra trùng lịch giảng viên, phòng học và học viên.
- Nếu phù hợp thì tạo lớp chờ hoặc ghép vào lớp chờ hiện có.
- Chỉ mở lớp chính thức khi đủ tối thiểu 5 học viên.
- Khi mở lớp, admin chốt giảng viên, phòng học, ngày bắt đầu, ngày kết thúc và lịch học chính thức.

5. Giảng viên
- Xem lịch dạy cá nhân.
- Xem lớp được phân công.
- Quản lý học viên trong lớp.
- Điểm danh, nhập điểm, đánh giá học viên.
- Xem thông tin phòng học.
- Gửi yêu cầu đổi lịch.

6. Học viên
- Xem thời khóa biểu.
- Xem điểm và điểm danh.
- Xem danh sách bạn học cùng lớp.
- Đăng ký lớp cố định hoặc gửi yêu cầu lịch riêng.
- Đánh giá khóa học.

7. Ràng buộc hệ thống
- Một lớp học phải có 1 khóa học, 1 giảng viên, 1 phòng học.
- Một khóa học có thể có nhiều lớp.
- Điều kiện mở lớp: tối thiểu 5 học viên.
- Không được trùng lịch giảng viên, phòng học, và ưu tiên kiểm tra cả học viên.
- Không tạo ghi danh trùng cho cùng một học viên và cùng một môn/lớp.

Yêu cầu kỹ thuật khi triển khai:
- Ưu tiên Laravel Controllers + Form Requests + Services + Eloquent.
- Business logic đặt trong service, không nhồi hết vào controller.
- Dùng transaction cho các luồng quan trọng như đăng ký học, xếp lớp, mở lớp, duyệt đổi lịch.
- Tạo hoặc cập nhật notification khi có sự kiện quan trọng.
- Viết feature test cho từng flow chính.
- Nếu cần API, hãy bổ sung theo hướng song song với web hiện tại, không phá Blade flow đang có.

Quy trình làm việc bắt buộc:
1. Audit codebase hiện tại trước, liệt kê rõ phần nào đã có, phần nào thiếu, phần nào đang lệch với yêu cầu.
2. Đề xuất kế hoạch triển khai theo phase nhỏ, an toàn.
3. Chỉ sửa các file liên quan trực tiếp.
4. Sau mỗi phase, chạy test phù hợp và báo kết quả.
5. Cuối cùng, trả về:
- tóm tắt những gì đã làm,
- ERD hoặc mô tả quan hệ bảng,
- danh sách route/controller/service/model đã thêm hoặc sửa,
- các rule nghiệp vụ đã xử lý,
- các test đã chạy,
- các rủi ro hoặc phần còn thiếu.

Nếu có chỗ yêu cầu mới mâu thuẫn với code hiện tại, hãy dừng lại, chỉ rõ điểm mâu thuẫn, đề xuất 2 hướng xử lý và ưu tiên phương án giữ tương thích với dữ liệu và cấu trúc sẵn có.
