@extends('layouts.app')

@section('title', 'KhaiTriEdu - Học trực tuyến thông minh')

@section('content')
<!-- Hero Section với hình ảnh và hiệu ứng fade-up -->
<div class="grid lg:grid-cols-2 gap-12 items-center fade-up">
    <div class="space-y-6">
        <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-light text-primary-dark rounded-full text-sm font-medium">
            <i class="fas fa-rocket"></i> Trung tâm giáo dục số 1
        </span>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
            <span class="bg-gradient-to-r from-primary to-primary-dark bg-clip-text text-transparent">KhaiTri Edu</span><br>
            Nền tảng đào tạo online
        </h1>
        <p class="text-lg text-gray-600 max-w-xl">Học trực tuyến và quản lý học viên, giảng viên, admin bằng hệ thống phân quyền đơn giản, hiện đại. Trải nghiệm học tập chưa bao giờ dễ dàng đến thế.</p>

        <!-- Nút hành động -->
        <div class="flex flex-wrap gap-4">
            @if(!session('user_id'))
                <a href="{{ route('login') }}" class="btn px-8 py-4 bg-primary text-white rounded-xl shadow-lg hover:bg-primary-dark transition transform hover:scale-105 flex items-center gap-2 text-lg">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
                </a>
                <a href="{{ route('register') }}" class="btn px-8 py-4 bg-white text-primary border-2 border-primary rounded-xl hover:bg-primary-light/20 transition transform hover:scale-105 flex items-center gap-2 text-lg">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="btn px-8 py-4 bg-primary text-white rounded-xl shadow-lg hover:bg-primary-dark transition transform hover:scale-105 flex items-center gap-2 text-lg">
                    <i class="fas fa-arrow-right"></i> Vào học ngay
                </a>
            @endif
        </div>

        <!-- Thống kê nhanh -->
        <div class="flex items-center gap-8 pt-4">
            <div>
                <div class="text-3xl font-bold text-primary-dark">{{ $studentCount }}+</div>
                <div class="text-sm text-gray-500">Học viên</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-primary-dark">{{ $courseCount }}+</div>
                <div class="text-sm text-gray-500">Khóa học</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-primary-dark">{{ $teacherCount }}+</div>
                <div class="text-sm text-gray-500">Giảng viên</div>
            </div>
        </div>
    </div>
    <div class="hidden lg:block relative">
        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1471&q=80" 
             alt="Học trực tuyến" 
             class="rounded-2xl shadow-2xl object-cover w-full h-auto">
        <div class="absolute -bottom-5 -left-5 bg-white p-4 rounded-xl shadow-lg flex items-center gap-3">
            <i class="fas fa-play-circle text-primary text-3xl"></i>
            <div>
                <div class="font-semibold">Giới thiệu về KhaiTri</div>
                <div class="text-sm text-gray-500">Xem video ngắn</div>
            </div>
        </div>
    </div>
</div>

<!-- Các tính năng nổi bật -->
<div class="grid md:grid-cols-3 gap-8 mt-24">
    <div class="card bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
        <div class="w-16 h-16 bg-primary-light rounded-2xl flex items-center justify-center text-primary-dark mb-6">
            <i class="fas fa-user-shield text-3xl"></i>
        </div>
        <h5 class="text-2xl font-semibold text-primary-dark mb-3">Admin</h5>
        <p class="text-gray-600 leading-relaxed">Quản lý toàn bộ trung tâm, cấu hình hệ thống và duyệt đăng ký một cách dễ dàng.</p>
    </div>
    <div class="card bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
        <div class="w-16 h-16 bg-primary-light rounded-2xl flex items-center justify-center text-primary-dark mb-6">
            <i class="fas fa-chalkboard-teacher text-3xl"></i>
        </div>
        <h5 class="text-2xl font-semibold text-primary-dark mb-3">Giảng viên</h5>
        <p class="text-gray-600 leading-relaxed">Tạo nội dung, quản lý khóa học, giao bài tập và chấm điểm học viên trực tuyến.</p>
    </div>
    <div class="card bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
        <div class="w-16 h-16 bg-primary-light rounded-2xl flex items-center justify-center text-primary-dark mb-6">
            <i class="fas fa-user-graduate text-3xl"></i>
        </div>
        <h5 class="text-2xl font-semibold text-primary-dark mb-3">Học viên</h5>
        <p class="text-gray-600 leading-relaxed">Xem khóa học, làm bài tập trắc nghiệm, theo dõi tiến độ và nhận chứng chỉ.</p>
    </div>
</div>

<!-- Khóa học nổi bật -->
<div class="mt-24">
    <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Khóa học <span class="text-primary">nổi bật</span></h2>
        <p class="text-gray-600 mt-2">Những khóa học được nhiều học viên lựa chọn nhất</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
        @forelse($courses as $course)
        <div class="card bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition">
            <img src="https://images.unsplash.com/photo-1587620962725-abab7fe55159?ixlib=rb-4.0.3&auto=format&fit=crop&w=1031&q=80" alt="{{ $course->title }}" class="w-full h-48 object-cover">
            <div class="p-6">
                <div class="flex items-center gap-2 text-sm text-primary mb-2">
                    <i class="fas fa-book"></i> {{ $course->subject->name ?? 'Khóa học' }}
                </div>
                <h4 class="text-xl font-semibold mb-2 line-clamp-2">{{ $course->title }}</h4>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $course->description ?? 'Khóa học chất lượng cao' }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-primary-dark font-bold">{{ number_format($course->subject->price ?? 0, 0, ',', '.') }}đ</span>
                    <a href="{{ route('courses.show', $course->id) }}" class="text-primary hover:underline flex items-center gap-1">Xem chi tiết <i class="fas fa-arrow-right text-xs"></i></a>
                </div>
                @if($course->teacher)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <div class="text-xs text-gray-500">Giảng viên: <span class="text-primary font-semibold">{{ $course->teacher->name }}</span></div>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12">
            <p class="text-gray-600">Chưa có khóa học nào. Quay lại sau nhé!</p>
        </div>
        @endforelse
    </div>
    <div class="text-center mt-10">
        <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 text-primary font-semibold hover:gap-3 transition-all">
            Xem tất cả khóa học <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- Đánh giá học viên -->
<div class="mt-24 bg-primary-light/30 py-16 rounded-3xl">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Học viên <span class="text-primary">nói gì</span></h2>
            <p class="text-gray-600 mt-2">Trải nghiệm thực tế từ những người đã học tại KhaiTri Edu</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <div class="flex items-center gap-4 mb-4">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="avatar" class="w-14 h-14 rounded-full object-cover">
                    <div>
                        <h5 class="font-semibold">Nguyễn Thị Lan</h5>
                        <div class="flex text-yellow-400 text-sm">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"Khóa học rất dễ hiểu, giảng viên nhiệt tình. Tôi đã có thể xây dựng website đầu tay sau 2 tháng."</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <div class="flex items-center gap-4 mb-4">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="avatar" class="w-14 h-14 rounded-full object-cover">
                    <div>
                        <h5 class="font-semibold">Trần Văn Nam</h5>
                        <div class="flex text-yellow-400 text-sm">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"Nội dung cập nhật, bài tập thực hành sát thực tế. Hỗ trợ rất tốt từ cộng đồng."</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <div class="flex items-center gap-4 mb-4">
                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="avatar" class="w-14 h-14 rounded-full object-cover">
                    <div>
                        <h5 class="font-semibold">Lê Thị Hoa</h5>
                        <div class="flex text-yellow-400 text-sm">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"Giao diện đẹp, dễ sử dụng. Tôi rất thích tính năng theo dõi tiến độ học tập."</p>
            </div>
        </div>
    </div>
</div>

<!-- Lời kêu gọi hành động -->
<div class="mt-24 text-center">
    <div class="bg-gradient-to-r from-primary to-primary-dark text-white rounded-3xl p-12">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Bắt đầu hành trình học tập ngay hôm nay</h2>
        <p class="text-xl mb-8 opacity-90">Đăng ký tài khoản để khám phá hàng trăm khóa học chất lượng.</p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-primary px-8 py-4 rounded-xl font-semibold text-lg shadow-lg hover:bg-gray-100 transition transform hover:scale-105">
            <i class="fas fa-user-plus"></i> Đăng ký miễn phí
        </a>
    </div>
</div>
@endsection