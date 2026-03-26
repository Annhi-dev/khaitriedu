@extends('layouts.app')

@section('title', 'Tuyển dụng - KhaiTriEdu')

@section('content')
<div class="bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 text-white py-20">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Gia nhập KhaiTriEdu</h1>
        <p class="text-xl text-blue-100">Cơ hội thú vị để phát triển sự nghiệp cùng chúng tôi</p>
    </div>
</div>

<div class="container mx-auto px-4 py-20">
    <div class="mb-20">
        <h2 class="text-4xl font-bold text-gray-800 mb-6">Cơ hội tuyển dụng</h2>
        <div class="space-y-4">
            @for($i = 1; $i <= 5; $i++)
            <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-lg transition border-l-4 border-primary group">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-primary transition">Vị trí {{ $i }}: {{ ['Giảng viên Python', 'Thiết kế UI/UX', 'Kỹ sư Backend', 'Chuyên viên Marketing', 'Quản lý Dự án'][rand(0,4)] }}</h3>
                        <div class="flex items-center gap-4 mt-3 text-sm text-gray-600">
                            <span><i class="fas fa-map-marker-alt text-primary mr-1"></i>TP.HCM</span>
                            <span><i class="fas fa-briefcase text-primary mr-1"></i>Full-time</span>
                            <span><i class="fas fa-dollar-sign text-primary mr-1"></i>15-25 triệu/tháng</span>
                        </div>
                        <p class="text-gray-600 mt-3">
                            Chúng tôi đang tìm kiếm những chuyên gia tài năng để gia nhập đội ngũ. Bạn sẽ có cơ hội làm việc với công nghệ hiện đại...
                        </p>
                    </div>
                    <a href="#" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition whitespace-nowrap">Ứng tuyển</a>
                </div>
            </div>
            @endfor
        </div>
    </div>

    <!-- Why Join KhaiTriEdu -->
    <div class="mb-20 bg-blue-50 p-12 rounded-2xl">
        <h2 class="text-4xl font-bold text-gray-800 mb-12 text-center">Tại sao gia nhập KhaiTriEdu?</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Sáng tạo</h3>
                <p class="text-gray-600 text-sm">Môi trường làm việc sáng tạo, được khuyến khích thử nghiệm ý tưởng mới</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Đội ngũ tuyệt vời</h3>
                <p class="text-gray-600 text-sm">Làm việc với những chuyên gia giàu kinh nghiệm và tận tâm</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Phát triển</h3>
                <p class="text-gray-600 text-sm">Cơ hội học hỏi liên tục và phát triển kỹ năng chuyên môn</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Tâm huyết</h3>
                <p class="text-gray-600 text-sm">Tham gia vào sứ mệnh dân chủ hóa giáo dục trực tuyến</p>
            </div>
        </div>
    </div>

    <!-- Application Info -->
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Quy trình ứng tuyển</h2>
        <div class="space-y-6">
            <div class="flex gap-4">
                <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0 mt-1">1</div>
                <div>
                    <h3 class="font-bold text-gray-800">Gửi CV</h3>
                    <p class="text-gray-600 text-sm">Gửi CV và thư xin việc của bạn qua email hoặc form online</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0 mt-1">2</div>
                <div>
                    <h3 class="font-bold text-gray-800">Phỏng vấn sơ bộ</h3>
                    <p class="text-gray-600 text-sm">Chúng tôi sẽ liên hệ để phỏng vấn sơ bộ qua điện thoại hoặc video</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0 mt-1">3</div>
                <div>
                    <h3 class="font-bold text-gray-800">Phỏng vấn chuyên sâu</h3>
                    <p class="text-gray-600 text-sm">Gặp trực tiếp hoặc video call với team để thảo luận về kỹ năng</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0 mt-1">4</div>
                <div>
                    <h3 class="font-bold text-gray-800">Đưa ra quyết định</h3>
                    <p class="text-gray-600 text-sm">Chúng tôi sẽ thông báo kết quả và mức lương cụ thể</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
