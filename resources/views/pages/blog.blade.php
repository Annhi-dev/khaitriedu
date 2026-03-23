@extends('layouts.app')

@section('title', 'Blog - KhaiTriEdu')

@section('content')
<div class="bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 text-white py-20">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Blog KhaiTriEdu</h1>
        <p class="text-xl text-blue-100">Chia sẻ kiến thức, kinh nghiệm và mẹo hữu ích từ cộng đồng</p>
    </div>
</div>

<div class="container mx-auto px-4 py-20">
    <!-- Search and Filter -->
    <div class="mb-12">
        <div class="flex flex-col md:flex-row gap-4">
            <input type="text" placeholder="Tìm kiếm bài viết..." class="flex-1 border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">
            <select class="border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">
                <option>Tất cả danh mục</option>
                <option>Lập trình</option>
                <option>Thiết kế Web</option>
                <option>Marketing Digital</option>
                <option>Kinh doanh</option>
            </select>
        </div>
    </div>

    <!-- Featured Post -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-16">
        <div class="grid md:grid-cols-2">
            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Featured" class="h-64 md:h-auto object-cover">
            <div class="p-8 flex flex-col justify-center">
                <div class="flex items-center gap-3 mb-4">
                    <span class="bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">Nổi bật</span>
                    <span class="text-sm text-gray-500">2 tháng trước</span>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-3">Bí quyết học lập trình hiệu quả</h2>
                <p class="text-gray-600 mb-6">
                    Hướng dẫn toàn diện giúp bạn học lập trình nhanh hơn, nhớ lâu hơn và giải quyết bài tập khó khăn. Các kỹ thuật được kiểm nghiệm bởi hàng ngàn học viên thành công...
                </p>
                <a href="#" class="inline-flex items-center text-primary font-semibold hover:gap-2 gap-1 transition">
                    Đọc thêm <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Blog Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Post 1 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
            <div class="relative overflow-hidden h-48">
                <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Post" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                <div class="absolute top-3 left-3 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">
                    Lập trình
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                    <i class="fas fa-calendar"></i>
                    <span>15-03-2026</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">Hướng dẫn từng bước xây dựng website đầu tiên</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                    Bạn là người mới bắt đầu? Bài viết này sẽ hướng dẫn bạn cách tạo website đầu tiên một cách dễ dàng...
                </p>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <i class="fas fa-eye"></i>
                        <span>1.2K lượt xem</span>
                    </div>
                    <a href="#" class="text-primary font-semibold text-sm hover:underline">Đọc</a>
                </div>
            </div>
        </div>

        <!-- Post 2 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
            <div class="relative overflow-hidden h-48">
                <img src="https://images.unsplash.com/photo-1561070791-2526d30994b5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Post" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                <div class="absolute top-3 left-3 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                    Thiết kế
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                    <i class="fas fa-calendar"></i>
                    <span>12-03-2026</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">Nguyên tắc thiết kế UI/UX hiện đại</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                    Tìm hiểu về những nguyên tắc cốt lõi của thiết kế giao diện người dùng đẹp và thân thiện...
                </p>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <i class="fas fa-eye"></i>
                        <span>892 lượt xem</span>
                    </div>
                    <a href="#" class="text-primary font-semibold text-sm hover:underline">Đọc</a>
                </div>
            </div>
        </div>

        <!-- Post 3 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
            <div class="relative overflow-hidden h-48">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Post" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                <div class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                    Marketing
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                    <i class="fas fa-calendar"></i>
                    <span>10-03-2026</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">5 chiến lược marketing digital hiệu quả</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                    Khám phá những chiến lược marketing digital được chứng minh hiệu quả để phát triển business...
                </p>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <i class="fas fa-eye"></i>
                        <span>2.1K lượt xem</span>
                    </div>
                    <a href="#" class="text-primary font-semibold text-sm hover:underline">Đọc</a>
                </div>
            </div>
        </div>

        <!-- Post 4 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
            <div class="relative overflow-hidden h-48">
                <img src="https://images.unsplash.com/photo-1516534775068-bb57e39c8ac4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Post" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                <div class="absolute top-3 left-3 bg-purple-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                    Kinh doanh
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                    <i class="fas fa-calendar"></i>
                    <span>08-03-2026</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">Cách kiếm thêm thu nhập qua kỹ năng online</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                    Hướng dẫn chi tiết giúp bạn kiếm tiền thêm bằng cách sử dụng kỹ năng lập trình hoặc thiết kế...
                </p>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <i class="fas fa-eye"></i>
                        <span>3.4K lượt xem</span>
                    </div>
                    <a href="#" class="text-primary font-semibold text-sm hover:underline">Đọc</a>
                </div>
            </div>
        </div>

        <!-- Post 5 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
            <div class="relative overflow-hidden h-48">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Post" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                <div class="absolute top-3 left-3 bg-yellow-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                    Mẹo
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                    <i class="fas fa-calendar"></i>
                    <span>05-03-2026</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">10 mẹo tăng năng suất học tập hàng ngày</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                    Những mẹo đơn giản nhưng cực hiệu quả giúp bạn học tập hiệu quả hơn và đạt kết quả tốt...
                </p>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <i class="fas fa-eye"></i>
                        <span>4.2K lượt xem</span>
                    </div>
                    <a href="#" class="text-primary font-semibold text-sm hover:underline">Đọc</a>
                </div>
            </div>
        </div>

        <!-- Post 6 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
            <div class="relative overflow-hidden h-48">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Post" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                <div class="absolute top-3 left-3 bg-indigo-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                    Review
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                    <i class="fas fa-calendar"></i>
                    <span>01-03-2026</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">Review chi tiết về các công cụ lập trình năm 2026</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                    So sánh chi tiết các IDE, framework và công cụ lập trình phổ biến nhất hiện nay...
                </p>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <i class="fas fa-eye"></i>
                        <span>5.6K lượt xem</span>
                    </div>
                    <a href="#" class="text-primary font-semibold text-sm hover:underline">Đọc</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-16 flex items-center justify-center gap-2">
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Trước</button>
        <button class="px-4 py-2 bg-primary text-white rounded-lg">1</button>
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">2</button>
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">3</button>
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Tiếp theo</button>
    </div>
</div>
@endsection
