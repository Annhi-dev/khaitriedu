@extends('bo_cuc.ung_dung')

@section('title', 'Giới thiệu - KhaiTriEdu')

@section('content')
<div class="bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 text-white py-20">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Về KhaiTriEdu</h1>
        <p class="text-xl text-blue-100">Nền tảng học tập trực tuyến hàng đầu Việt Nam</p>
    </div>
</div>

<div class="container mx-auto px-4 py-20">
    
    <div class="grid md:grid-cols-2 gap-12 mb-20 items-center">
        <div>
            <h2 class="text-4xl font-bold text-gray-800 mb-6">Câu chuyện của chúng tôi</h2>
            <p class="text-gray-600 text-lg mb-4">
                KhaiTriEdu được thành lập với sứ mệnh dân chủ hóa giáo dục online. Chúng tôi tin rằng mọi người đều có quyền học tập từ những giảng viên tốt nhất, bất kể địa điểm hay hoàn cảnh.
            </p>
            <p class="text-gray-600 text-lg mb-4">
                Từ năm 2023 đến nay, KhaiTriEdu đã giúp hàng nghìn học viên phát triển kỹ năng, tìm công việc mơ ước và thay đổi cuộc sống của họ.
            </p>
            <p class="text-gray-600 text-lg">
                Với đội ngũ giảng viên giàu kinh nghiệm và nền tảng công nghệ hiện đại, chúng tôi cam kết mang lại trải nghiệm học tập tốt nhất.
            </p>
        </div>
        <div>
            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="About" class="rounded-2xl shadow-lg">
        </div>
    </div>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center mb-20 bg-blue-50 p-12 rounded-2xl">
        <div>
            <div class="text-4xl font-bold text-primary mb-2">{{ number_format($studentCount) }}+</div>
            <div class="text-gray-600">Học viên</div>
        </div>
        <div>
            <div class="text-4xl font-bold text-primary mb-2">{{ number_format($courseCount) }}+</div>
            <div class="text-gray-600">Khóa học</div>
        </div>
        <div>
            <div class="text-4xl font-bold text-primary mb-2">{{ number_format($teacherCount) }}+</div>
            <div class="text-gray-600">Giảng viên</div>
        </div>
        <div>
            <div class="text-4xl font-bold text-primary mb-2">4.8</div>
            <div class="text-gray-600">Đánh giá</div>
        </div>
    </div>

    
    <div class="mb-20">
        <h2 class="text-4xl font-bold text-gray-800 mb-12 text-center">Giá trị cốt lõi</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-blue-50 p-8 rounded-2xl">
                <i class="fas fa-heart text-4xl text-primary mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-3">Tâm huyết</h3>
                <p class="text-gray-600">
                    Chúng tôi đặt tâm huyết vào từng khóa học, từng học viên. Để đó là trải nghiệm học tập tốt nhất.
                </p>
            </div>
            <div class="bg-blue-50 p-8 rounded-2xl">
                <i class="fas fa-graduation-cap text-4xl text-primary mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-3">Chất lượng</h3>
                <p class="text-gray-600">
                    Mỗi khóa học được tuyển chọn kỹ càng với giảng viên giàu kinh nghiệm và nội dung cập nhật.
                </p>
            </div>
            <div class="bg-blue-50 p-8 rounded-2xl">
                <i class="fas fa-handshake text-4xl text-primary mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-3">Cộng đồng</h3>
                <p class="text-gray-600">
                    Xây dựng cộng đồng học tập sôi nổi, nơi mọi người có thể chia sẻ, hỗ trợ nhau.
                </p>
            </div>
        </div>
    </div>

    
    <div class="mb-20">
        <h2 class="text-4xl font-bold text-gray-800 mb-12 text-center">Đội ngũ đạo tạo</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @for($i = 1; $i <= 3; $i++)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden text-center">
                <img src="https://randomuser.me/api/portraits/men/{{ rand(1,50) }}.jpg" alt="Trainer" class="w-full h-64 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Giảng viên #{{ $i }}</h3>
                    <p class="text-gray-600 mb-3">Chuyên gia lĩnh vực</p>
                    <p class="text-sm text-gray-500">Hơn 10 năm kinh nghiệm, đã dạy hơn 5000 học viên</p>
                </div>
            </div>
            @endfor
        </div>
    </div>

    
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 rounded-3xl p-12 text-center text-white">
        <h2 class="text-4xl font-bold mb-4">Sẵn sàng bắt đầu hành trình học tập?</h2>
        <p class="text-blue-100 text-lg mb-8">Khám phá hàng trăm khóa học chất lượng cao</p>
        <a href="{{ route('courses.index') }}" class="inline-block bg-white text-primary px-8 py-4 rounded-xl font-semibold hover:bg-gray-100 transition">
            Xem khóa học
        </a>
    </div>
</div>
@endsection
