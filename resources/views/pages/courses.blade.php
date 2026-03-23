@extends('layouts.app')

@section('title', 'Khóa học - KhaiTriEdu')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="mb-12">
        <h1 class="text-4xl font-bold text-gray-800">Khám phá khóa học</h1>
        <p class="text-gray-600 mt-2">Hàng trăm khóa học chất lượng cao từ các giảng viên giàu kinh nghiệm</p>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Tìm kiếm</label>
                <input type="text" id="searchInput" placeholder="Tên khóa học..." class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Danh mục</label>
                <select id="categoryFilter" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Tất cả danh mục</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Sắp xếp</label>
                <select id="sortFilter" class="w-full border rounded-lg px-3 py-2">
                    <option value="newest">Mới nhất</option>
                    <option value="oldest">Cũ nhất</option>
                    <option value="popular">Phổ biến nhất</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Courses Grid -->
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
                    <div class="absolute top-3 right-3 bg-yellow-400 text-gray-800 text-xs font-bold px-3 py-1 rounded-full">
                        4.8 ⭐
                    </div>
                </div>
                <div class="p-6">
                    <h4 class="text-xl font-semibold mb-2 line-clamp-2">{{ $course->title }}</h4>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $course->description ?? 'Khóa học chất lượng cao với nội dung cập nhật liên tục.' }}</p>
                    
                    <div class="flex items-center justify-between border-t pt-4 mb-4">
                        <span class="text-primary-dark font-bold text-lg">{{ number_format($course->subject->price ?? 0, 0, ',', '.') }}đ</span>
                        <span class="text-xs text-gray-500">{{ $course->enrollments->count() }} học viên</span>
                    </div>

                    @if($course->teacher)
                        <div class="mb-4 flex items-center gap-2 text-xs text-gray-500">
                            <img src="https://randomuser.me/api/portraits/men/1.jpg" class="w-6 h-6 rounded-full object-cover">
                            <span><strong>{{ $course->teacher->name }}</strong></span>
                        </div>
                    @endif

                    <a href="{{ route('courses.show', $course->id) }}" class="w-full text-center bg-primary text-white py-2 rounded-lg font-semibold hover:bg-primary-dark transition">
                        Xem chi tiết
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-16">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-600 text-lg">Chưa có khóa học nào</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-12">
        {{ $courses->links() }}
    </div>
</div>
@endsection
