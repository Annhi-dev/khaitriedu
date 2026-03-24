@extends('layouts.app')

@section('title', 'KhaiTriEdu - Học trực tuyến thông minh')

@section('content')
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 text-white overflow-hidden">
        <!-- Animated blob background -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        </div>

        <div class="container mx-auto px-4 py-16 md:py-24 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8 text-center lg:text-left">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
                        KhaiTriEdu
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-white">Học trực tuyến thông minh</span>
                    </h1>
                    <p class="text-xl text-blue-100 max-w-2xl mx-auto lg:mx-0">
                        Nền tảng học tập trực tuyến hàng đầu Việt Nam, mang đến trải nghiệm học tập chất lượng cao với đội ngũ giảng viên giàu kinh nghiệm.
                    </p>
                    <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                        @if(!session('user_id'))
                            <a href="{{ route('register') }}" class="btn px-8 py-4 bg-white text-blue-700 rounded-xl font-semibold shadow-lg hover:bg-blue-50 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-rocket"></i> Bắt đầu học ngay
                            </a>
                            <a href="{{ route('courses.index') }}" class="btn px-8 py-4 bg-transparent border-2 border-white text-white rounded-xl font-semibold hover:bg-white/10 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-book-open"></i> Xem khóa học
                            </a>
                            <a href="{{ route('apply-teacher') }}" class="btn px-8 py-4 bg-white/20 border border-white text-white rounded-xl font-semibold hover:bg-white/30 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-chalkboard-teacher"></i> Ứng tuyển giảng viên
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn px-8 py-4 bg-white text-blue-700 rounded-xl font-semibold shadow-lg hover:bg-blue-50 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-chalkboard-user"></i> Vào học ngay
                            </a>
                            <a href="{{ route('courses.index') }}" class="btn px-8 py-4 bg-transparent border-2 border-white text-white rounded-xl font-semibold hover:bg-white/10 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-search"></i> Khám phá khóa học
                            </a>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-8 justify-center lg:justify-start pt-8">
                        <div>
                            <div class="text-3xl font-bold">{{ number_format($studentCount) }}+</div>
                            <div class="text-blue-200">Học viên</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold">{{ number_format($courseCount) }}+</div>
                            <div class="text-blue-200">Khóa học</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold">{{ number_format($teacherCount) }}+</div>
                            <div class="text-blue-200">Giảng viên</div>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:block relative">
                    <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80"
                         alt="Học tập trực tuyến"
                         class="rounded-2xl shadow-2xl object-cover w-full h-auto">
                    <div class="absolute -bottom-5 -left-5 bg-white/10 backdrop-blur-md p-4 rounded-xl shadow-lg flex items-center gap-3">
                        <i class="fas fa-play-circle text-white text-3xl"></i>
                        <div>
                            <div class="font-semibold">Giới thiệu về KhaiTri</div>
                            <div class="text-sm text-blue-200">Xem video ngắn</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container mx-auto px-4 py-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Tại sao chọn <span class="text-primary">KhaiTriEdu</span>?</h2>
            <p class="text-gray-600 mt-2">Trải nghiệm học tập toàn diện với đội ngũ giảng viên tận tâm và lộ trình cá nhân hóa</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="card bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 text-center">
                <div class="w-20 h-20 bg-primary-light/20 rounded-2xl flex items-center justify-center text-primary mx-auto mb-6">
                    <i class="fas fa-chalkboard-user text-4xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-primary-dark mb-3">Giảng viên thân thiện</h3>
                <p class="text-gray-600">Đội ngũ giảng viên giàu kinh nghiệm, luôn sẵn sàng hỗ trợ, hướng dẫn chi tiết từng học viên. Phong cách dễ thương, gần gũi, tạo cảm hứng học tập.</p>
            </div>
            <div class="card bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 text-center">
                <div class="w-20 h-20 bg-primary-light/20 rounded-2xl flex items-center justify-center text-primary mx-auto mb-6">
                    <i class="fas fa-user-graduate text-4xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-primary-dark mb-3">Học viên được hỗ trợ tận tình</h3>
                <p class="text-gray-600">Mỗi học viên đều có lộ trình học riêng, được kèm cặp sát sao, giải đáp thắc mắc 24/7 qua hệ thống chat và lớp học trực tuyến.</p>
            </div>
            <div class="card bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 text-center">
                <div class="w-20 h-20 bg-primary-light/20 rounded-2xl flex items-center justify-center text-primary mx-auto mb-6">
                    <i class="fas fa-certificate text-4xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-primary-dark mb-3">Chất lượng đào tạo vượt trội</h3>
                <p class="text-gray-600">Nội dung bài giảng cập nhật liên tục, bài tập thực hành sát thực tế, cam kết đầu ra rõ ràng và chứng chỉ có giá trị.</p>
            </div>
        </div>
    </div>

    <!-- Featured Courses -->
    <div class="bg-gray-50 py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Khóa học <span class="text-primary">nổi bật</span></h2>
                <div class="w-24 h-1 bg-primary mx-auto mt-4 rounded-full"></div>
                <p class="text-gray-600 mt-4">Những khóa học được yêu thích nhất tại KhaiTriEdu</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($courses as $course)
                    <div class="card bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group">
                        <div class="relative">
                            <img src="https://images.unsplash.com/photo-1587620962725-abab7fe55159?ixlib=rb-4.0.3&auto=format&fit=crop&w=1031&q=80"
                                 alt="{{ $course->title }}"
                                 class="w-full h-56 object-cover group-hover:scale-105 transition duration-300">
                            <div class="absolute top-3 left-3 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">
                                {{ $course->subject->name ?? 'Khóa học' }}
                            </div>
                        </div>
                        <div class="p-6">
                            <h4 class="text-xl font-semibold mb-2 line-clamp-2">{{ $course->title }}</h4>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $course->description ?? 'Khóa học chất lượng cao với nội dung cập nhật liên tục.' }}</p>
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400 text-sm">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-xs text-gray-500 ml-2">(4.8)</span>
                            </div>
                            <div class="flex items-center justify-between border-t pt-4">
                                <span class="text-primary-dark font-bold text-xl">{{ number_format($course->subject->price ?? 0, 0, ',', '.') }}đ</span>
                                <a href="{{ route('courses.show', $course->id) }}" class="text-primary hover:underline flex items-center gap-1 text-sm font-medium">
                                    Xem chi tiết <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                                </a>
                            </div>
                            @if($course->teacher)
                                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                                    <img src="https://randomuser.me/api/portraits/men/1.jpg" class="w-6 h-6 rounded-full object-cover">
                                    <span>Giảng viên: <span class="text-primary font-semibold">{{ $course->teacher->name }}</span></span>
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
            <div class="text-center mt-12">
                <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 text-primary font-semibold hover:gap-3 transition-all border-b-2 border-primary pb-1">
                    Xem tất cả khóa học <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Counter -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 py-16 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center" x-data="{
                counters: {
                    students: {{ $studentCount }},
                    courses: {{ $courseCount }},
                    teachers: {{ $teacherCount }},
                    satisfaction: 98
                },
                init() {
                    for (let key in this.counters) {
                        let target = this.counters[key];
                        let current = 0;
                        let increment = target / 30;
                        let interval = setInterval(() => {
                            current += increment;
                            if (current >= target) {
                                current = target;
                                clearInterval(interval);
                            }
                            this.counters[key] = Math.floor(current);
                        }, 50);
                    }
                }
            }">
                <div>
                    <i class="fas fa-users text-4xl mb-3"></i>
                    <div class="text-4xl font-bold" x-text="counters.students">0</div>
                    <div class="text-blue-200">Học viên đã đăng ký</div>
                </div>
                <div>
                    <i class="fas fa-book-open text-4xl mb-3"></i>
                    <div class="text-4xl font-bold" x-text="counters.courses">0</div>
                    <div class="text-blue-200">Khóa học</div>
                </div>
                <div>
                    <i class="fas fa-chalkboard-user text-4xl mb-3"></i>
                    <div class="text-4xl font-bold" x-text="counters.teachers">0</div>
                    <div class="text-blue-200">Giảng viên</div>
                </div>
                <div>
                    <i class="fas fa-smile text-4xl mb-3"></i>
                    <div class="text-4xl font-bold" x-text="counters.satisfaction">0</div>
                    <div class="text-blue-200">Tỷ lệ hài lòng</div>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="container mx-auto px-4 py-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Cách <span class="text-primary">hoạt động</span></h2>
            <p class="text-gray-600 mt-2">Chỉ 3 bước đơn giản để bắt đầu hành trình học tập</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8 relative">
            <!-- Connecting lines (desktop) -->
            <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-primary/30 -translate-y-1/2 z-0"></div>
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-primary text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">1</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Đăng ký tài khoản</h3>
                <p class="text-gray-600">Tạo tài khoản miễn phí và xác thực email chỉ trong 1 phút.</p>
            </div>
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-primary text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">2</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Chọn khóa học</h3>
                <p class="text-gray-600">Khám phá hàng trăm khóa học phù hợp với mục tiêu của bạn.</p>
            </div>
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-primary text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">3</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Học và nhận chứng chỉ</h3>
                <p class="text-gray-600">Hoàn thành khóa học, nhận chứng chỉ có giá trị.</p>
            </div>
        </div>
    </div>

    <!-- Testimonials -->
    <div class="bg-blue-50 py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Học viên <span class="text-primary">nói gì</span></h2>
                <p class="text-gray-600 mt-2">Những phản hồi thực tế từ cộng đồng KhaiTriEdu</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
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
                <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
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
                <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
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

    <!-- CTA Section -->
    <div class="container mx-auto px-4 py-20">
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 rounded-3xl p-12 text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="100" r="80" fill="white"/>
                    <circle cx="900" cy="800" r="120" fill="white"/>
                    <circle cx="700" cy="200" r="60" fill="white"/>
                </svg>
            </div>
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Sẵn sàng để chinh phục tri thức?</h2>
                <p class="text-xl text-blue-100 mb-8">Đăng ký ngay hôm nay để nhận ưu đãi đặc biệt cho học viên mới.</p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-primary px-8 py-4 rounded-xl font-semibold text-lg shadow-lg hover:bg-gray-100 transition transform hover:scale-105">
                    <i class="fas fa-user-plus"></i> Đăng ký miễn phí
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
@endsection