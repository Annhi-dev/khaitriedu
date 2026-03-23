@extends('layouts.app')

@section('title', 'Trung tâm trợ giúp - KhaiTriEdu')

@section('content')
<div class="bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 text-white py-20">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Trung tâm trợ giúp</h1>
        <p class="text-xl text-blue-100">Tìm câu trả lời cho những câu hỏi của bạn</p>
    </div>
</div>

<div class="container mx-auto px-4 py-20">
    <div class="mb-12 flex flex-col md:flex-row gap-4">
        <input type="text" placeholder="Tìm kiếm..." class="flex-1 border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">
        <select class="border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">
            <option>Tất cả danh mục</option>
            <option>Tài khoản</option>
            <option>Khóa học</option>
            <option>Thanh toán</option>
            <option>Kỹ thuật</option>
        </select>
    </div>

    <div class="grid md:grid-cols-3 gap-8 mb-20">
        <div class="bg-blue-50 p-6 rounded-xl text-center hover:bg-primary hover:text-white transition cursor-pointer group">
            <div class="text-4xl mb-3 group-hover:scale-110 transition">🛡️</div>
            <h3 class="font-bold mb-2">Bảo mật & Tài khoản</h3>
            <p class="text-sm text-gray-600 group-hover:text-white/80">Hướng dẫn về bảo mật đăng nhập và quản lý tài khoản</p>
        </div>
        <div class="bg-blue-50 p-6 rounded-xl text-center hover:bg-primary hover:text-white transition cursor-pointer group">
            <div class="text-4xl mb-3 group-hover:scale-110 transition">💳</div>
            <h3 class="font-bold mb-2">Thanh toán</h3>
            <p class="text-sm text-gray-600 group-hover:text-white/80">Câu hỏi về thanh toán, hoàn lại tiền và hóa đơn</p>
        </div>
        <div class="bg-blue-50 p-6 rounded-xl text-center hover:bg-primary hover:text-white transition cursor-pointer group">
            <div class="text-4xl mb-3 group-hover:scale-110 transition">📚</div>
            <h3 class="font-bold mb-2">Khóa học</h3>
            <p class="text-sm text-gray-600 group-hover:text-white/80">Hướng dẫn học tập, chứng chỉ và hoàn thành</p>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="max-w-3xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Câu hỏi thường gặp</h2>
        <div class="space-y-4">
            <details class="bg-white rounded-lg border border-gray-200 overflow-hidden group">
                <summary class="p-6 font-bold text-gray-800 cursor-pointer hover:bg-blue-50 transition flex items-center justify-between">
                    <span>Làm sao để đăng ký tài khoản?</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-6 pb-6 text-gray-600">
                    <p>Bạn có thể đăng ký tài khoản bằng cách nhấp vào nút "Đăng ký" ở trang chủ. Nhập email, tên người dùng, mật khẩu và các thông tin cần thiết. Sau đó xác nhận email của bạn.</p>
                </div>
            </details>

            <details class="bg-white rounded-lg border border-gray-200 overflow-hidden group">
                <summary class="p-6 font-bold text-gray-800 cursor-pointer hover:bg-blue-50 transition flex items-center justify-between">
                    <span>Có cách nào để lấy lại mật khẩu không?</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-6 pb-6 text-gray-600">
                    <p>Có! Tại trang đăng nhập, nhấp vào "Quên mật khẩu?". Nhập email của bạn và chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.</p>
                </div>
            </details>

            <details class="bg-white rounded-lg border border-gray-200 overflow-hidden group">
                <summary class="p-6 font-bold text-gray-800 cursor-pointer hover:bg-blue-50 transition flex items-center justify-between">
                    <span>Làm sao để đăng ký khóa học?</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-6 pb-6 text-gray-600">
                    <p>Truy cập trang "Khóa học", chọn khóa học bạn muốn. Nhấp vào "Xem chi tiết" và chọn "Đăng ký ngay". Hoàn tất thanh toán và bạn sẽ có quyền truy cập.</p>
                </div>
            </details>

            <details class="bg-white rounded-lg border border-gray-200 overflow-hidden group">
                <summary class="p-6 font-bold text-gray-800 cursor-pointer hover:bg-blue-50 transition flex items-center justify-between">
                    <span>Có hỗ trợ hoàn lại tiền không?</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-6 pb-6 text-gray-600">
                    <p>Có, chúng tôi cung cấp bảo đảm hoàn lại tiền 7 ngày. Nếu không hài lòng với khóa học, liên hệ support trong vòng 7 ngày để nhận hoàn lại 100%.</p>
                </div>
            </details>

            <details class="bg-white rounded-lg border border-gray-200 overflow-hidden group">
                <summary class="p-6 font-bold text-gray-800 cursor-pointer hover:bg-blue-50 transition flex items-center justify-between">
                    <span>Chứng chỉ được cấp khi nào?</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-6 pb-6 text-gray-600">
                    <p>Chứng chỉ sẽ được cấp ngay sau khi bạn hoàn thành tất cả bài học và đạt điểm yêu cầu. Bạn có thể tải xuống hoặc chia sẻ trực tiếp.</p>
                </div>
            </details>

            <details class="bg-white rounded-lg border border-gray-200 overflow-hidden group">
                <summary class="p-6 font-bold text-gray-800 cursor-pointer hover:bg-blue-50 transition flex items-center justify-between">
                    <span>Có thời hạn truy cập khóa học không?</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-6 pb-6 text-gray-600">
                    <p>Không! Bạn có quyền truy cập vĩnh viễn vào tất cả tài liệu khóa học. Miễn là tài khoản của bạn hoạt động, bạn có thể học lại bất cứ lúc nào.</p>
                </div>
            </details>

            <details class="bg-white rounded-lg border border-gray-200 overflow-hidden group">
                <summary class="p-6 font-bold text-gray-800 cursor-pointer hover:bg-blue-50 transition flex items-center justify-between">
                    <span>Làm sao để liên hệ với giảng viên?</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-6 pb-6 text-gray-600">
                    <p>Bát cứ khóa học nào cũng có mục bình luận để đặt câu hỏi. Giảng viên sẽ phản hồi trong vòng 24 giờ. Bạn cũng có thể liên hệ qua email support.</p>
                </div>
            </details>

            <details class="bg-white rounded-lg border border-gray-200 overflow-hidden group">
                <summary class="p-6 font-bold text-gray-800 cursor-pointer hover:bg-blue-50 transition flex items-center justify-between">
                    <span>Có được tải xuống tài liệu không?</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-6 pb-6 text-gray-600">
                    <p>Có, hầu hết tài liệu, slide và bài tập đều có thể tải xuống. Bạn có thể truy cập ngoại tuyến sau khi tải xuống.</p>
                </div>
            </details>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="mt-20 bg-gradient-to-r from-blue-900 to-blue-700 text-white rounded-2xl p-12 text-center">
        <h2 class="text-3xl font-bold mb-4">Không tìm thấy câu trả lời?</h2>
        <p class="text-blue-100 mb-6">Liên hệ với team hỗ trợ của chúng tôi</p>
        <a href="{{ route('contact') }}" class="inline-block bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
            Gửi tin nhắn
        </a>
    </div>
</div>
@endsection
