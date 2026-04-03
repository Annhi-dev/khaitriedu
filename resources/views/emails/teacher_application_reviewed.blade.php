<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả hồ sơ ứng tuyển giảng viên</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:680px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;overflow:hidden;">
            <div style="padding:24px 28px;background:linear-gradient(135deg,#0f766e,#2563eb);color:#ffffff;">
                <p style="margin:0 0 8px;font-size:12px;letter-spacing:0.24em;text-transform:uppercase;opacity:0.82;">KhaiTriEdu</p>
                <h1 style="margin:0;font-size:28px;line-height:1.3;">Cập nhật hồ sơ ứng tuyển giảng viên</h1>
            </div>

            <div style="padding:28px;">
                <p style="margin:0 0 16px;font-size:16px;line-height:1.7;">Chào {{ $application->name }},</p>

                @if ($action === \App\Models\TeacherApplication::STATUS_APPROVED)
                    <p style="margin:0 0 16px;font-size:16px;line-height:1.7;">
                        Hồ sơ ứng tuyển của bạn đã được <strong>duyệt</strong>. KhaiTriEdu đã kích hoạt tài khoản giảng viên để bạn có thể đăng nhập và bắt đầu làm việc trên hệ thống.
                    </p>
                @elseif ($action === \App\Models\TeacherApplication::STATUS_NEEDS_REVISION)
                    <p style="margin:0 0 16px;font-size:16px;line-height:1.7;">
                        Hồ sơ ứng tuyển của bạn hiện đang ở trạng thái <strong>cần bổ sung</strong>. Vui lòng xem ghi chú bên dưới và phản hồi lại để admin tiếp tục xử lý.
                    </p>
                @elseif ($action === \App\Models\TeacherApplication::STATUS_REJECTED)
                    <p style="margin:0 0 16px;font-size:16px;line-height:1.7;">
                        Cảm ơn bạn đã quan tâm đến vị trí giảng viên tại KhaiTriEdu. Hiện tại hồ sơ của bạn chưa phù hợp với nhu cầu tuyển chọn lần này.
                    </p>
                @endif

                @if ($reviewMessage)
                    <div style="margin:20px 0;padding:18px 20px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:16px;">
                        <p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#475569;">Ghi chú từ admin</p>
                        <p style="margin:0;font-size:15px;line-height:1.8;color:#1e293b;white-space:pre-line;">{{ $reviewMessage }}</p>
                    </div>
                @endif

                @if ($action === \App\Models\TeacherApplication::STATUS_APPROVED && $username && $temporaryPassword)
                    <div style="margin:20px 0;padding:20px;background:#ecfeff;border:1px solid #a5f3fc;border-radius:16px;">
                        <p style="margin:0 0 12px;font-size:13px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#0f766e;">Thông tin tài khoản giảng viên</p>
                        <p style="margin:0 0 10px;font-size:15px;line-height:1.7;"><strong>Tài khoản:</strong> {{ $username }}</p>
                        <p style="margin:0 0 10px;font-size:15px;line-height:1.7;"><strong>Mật khẩu tạm:</strong> {{ $temporaryPassword }}</p>
                        @if ($loginUrl)
                            <p style="margin:0 0 14px;font-size:15px;line-height:1.7;"><strong>Đăng nhập tại:</strong> <a href="{{ $loginUrl }}" style="color:#2563eb;">{{ $loginUrl }}</a></p>
                        @endif
                        <p style="margin:0;font-size:14px;line-height:1.7;color:#0f172a;">Vui lòng đăng nhập và đổi mật khẩu ngay sau khi vào hệ thống để đảm bảo an toàn.</p>
                    </div>
                @endif

                <p style="margin:24px 0 0;font-size:15px;line-height:1.8;color:#334155;">
                    Trân trọng,<br>
                    <strong>Đội ngũ KhaiTriEdu</strong>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
