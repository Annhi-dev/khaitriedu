@extends('bo_cuc.ung_dung')

@section('title', 'Blog - KhaiTriEdu')

@section('content')
@php
    use Illuminate\Support\Str;
    $featured = $posts->first() ?? null;
    $gridPosts = $posts->skip(1);
    $imageUrls = [
        'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
        'https://images.unsplash.com/photo-1561070791-2526d30994b5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
        'https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
        'https://images.unsplash.com/photo-1516534775068-bb57e39c8ac4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
    ];
@endphp

<div class="bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 text-white py-20">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Blog KhaiTriEdu</h1>
        <p class="text-xl text-blue-100">Chia sẻ kiến thức, kinh nghiệm và mẹo hữu ích từ cộng đồng</p>
    </div>
</div>

<div class="container mx-auto px-4 py-20">
    
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

    
    @if ($featured)
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-16">
            <div class="grid md:grid-cols-2">
                <img src="{{ $imageUrls[0] }}" alt="Featured" class="h-64 md:h-auto object-cover">
                <div class="p-8 flex flex-col justify-center">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">Nổi bật</span>
                        <span class="text-sm text-gray-500">{{ $featured->published_at ? optional($featured->published_at)->diffForHumans() : 'Mới nhất' }}</span>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-3">{{ $featured->title }}</h2>
                    <p class="text-gray-600 mb-6">{{ Str::limit($featured->message, 150) }}</p>
                    <a href="#" class="inline-flex items-center text-primary font-semibold hover:gap-2 gap-1 transition">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-16 p-8">
            <p class="text-gray-700">Chưa có bài viết nào được đăng.</p>
        </div>
    @endif

    
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($gridPosts as $post)
            @php
                $image = $imageUrls[$loop->index % count($imageUrls)];
                $categories = ['Lập trình', 'Thiết kế', 'Marketing', 'Kinh doanh', 'Mẹo', 'Review'];
                $category = $categories[$loop->index % count($categories)];
            @endphp
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
                <div class="relative overflow-hidden h-48">
                    <img src="{{ $image }}" alt="Post" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                    <div class="absolute top-3 left-3 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">{{ $category }}</div>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                        <i class="fas fa-calendar"></i>
                        <span>{{ $post->published_at ? $post->published_at->format('d-m-Y') : 'N/A' }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">{{ $post->title }}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ Str::limit($post->message, 100) }}</p>
                    <div class="flex items-center justify-between pt-4 border-t">
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <i class="fas fa-eye"></i>
                            <span>{{ rand(500, 5600) }} lượt xem</span>
                        </div>
                        <a href="#" class="text-primary font-semibold text-sm hover:underline">Đọc</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-xl shadow-lg p-8 text-center text-gray-500">
                Chưa có bài viết nào để hiển thị.
            </div>
        @endforelse
    </div>

    
    <div class="mt-16 flex items-center justify-center gap-2">
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Trước</button>
        <button class="px-4 py-2 bg-primary text-white rounded-lg">1</button>
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">2</button>
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">3</button>
        <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Tiếp theo</button>
    </div>
</div>
@endsection
