@extends('layouts.app')

@section('title', 'Khóa học - KhaiTriEdu')

@section('content')
<div class="container mx-auto px-4 py-12">
    <!-- Hero Section -->
    <div class="mb-16 text-center">
        <h1 class="text-5xl md:text-6xl font-black bg-gradient-to-r from-primary via-purple-600 to-primary bg-clip-text text-transparent mb-4">Khám phá khóa học</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">Hàng trăm khóa học chất lượng cao từ các giảng viên giàu kinh nghiệm. Bắt đầu hành trình học tập của bạn hôm nay</p>
    </div>

    <!-- Filters -->
    <div class="bg-gradient-to-r from-white to-blue-50 p-8 rounded-2xl shadow-lg mb-12 border border-blue-100">
        <div class="grid md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-bold mb-3 text-gray-700 flex items-center gap-2">
                    <i class="fas fa-search text-primary"></i> Tìm kiếm
                </label>
                <input type="text" id="searchInput" placeholder="Nhập tên khóa học..." class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:border-primary focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-bold mb-3 text-gray-700 flex items-center gap-2">
                    <i class="fas fa-list text-primary"></i> Danh mục
                </label>
                <select id="categoryFilter" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:border-primary focus:outline-none transition">
                    <option value="">📚 Tất cả danh mục</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold mb-3 text-gray-700 flex items-center gap-2">
                    <i class="fas fa-sort text-primary"></i> Sắp xếp
                </label>
                <select id="sortFilter" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:border-primary focus:outline-none transition">
                    <option value="newest">🆕 Mới nhất</option>
                    <option value="oldest">📅 Cũ nhất</option>
                    <option value="popular">🔥 Phổ biến nhất</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Courses Grid -->
    <div id="coursesGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($courses as $course)
            <div class="relative group course-card"
                 data-title="{{ strtolower($course->title) }}"
                 data-category="{{ strtolower($course->subject->name ?? '') }}"
                 data-enrollments="{{ $course->enrollments->count() }}"
                 data-created="{{ $course->created_at ?? now() }}">
                <!-- Gradient Border Effect -->
                <div class="absolute -inset-0.5 bg-gradient-to-r from-primary via-purple-500 to-primary rounded-3xl opacity-0 group-hover:opacity-100 transition duration-500 blur-lg"></div>
                
                <div class="relative card bg-white rounded-3xl shadow-xl group-hover:shadow-2xl transition-all duration-300 overflow-hidden h-full flex flex-col">
                    <!-- Image Container -->
                    <div class="relative overflow-hidden h-56 bg-gradient-to-br from-blue-100 to-purple-100">
                        <img src="https://images.unsplash.com/photo-1587620962725-abab7fe55159?ixlib=rb-4.0.3&auto=format&fit=crop&w=1031&q=80"
                             alt="{{ $course->title }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                        
                        <!-- Overlays -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        
                        <!-- Category Badge -->
                        <div class="absolute top-4 left-4 bg-gradient-to-r from-primary to-primary-dark text-white text-xs font-bold px-5 py-2 rounded-full shadow-lg backdrop-blur-sm">
                            {{ $course->subject->name ?? 'Khóa học' }}
                        </div>
                        
                        <!-- Premium Badge -->
                        <div class="absolute top-4 right-4">
                            <div class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-5 py-2 rounded-full shadow-lg flex items-center gap-2 backdrop-blur-sm">
                                <i class="fas fa-crown"></i> PRO
                            </div>
                        </div>
                        
                        <!-- Rating Star -->
                        <div class="absolute bottom-4 right-4 bg-white/95 backdrop-blur-md rounded-full px-4 py-2 shadow-lg border border-white/20">
                            <span class="text-sm font-bold text-yellow-500">⭐ 4.8</span>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <!-- Title -->
                        <h4 class="text-lg font-bold mb-2 line-clamp-2 text-gray-800 group-hover:text-primary transition">{{ $course->title }}</h4>
                        
                        <!-- Description -->
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2 flex-1">{{ $course->description ?? 'Khóa học chất lượng cao với nội dung cập nhật liên tục.' }}</p>
                        
                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-2 mb-4 pb-4 border-b border-gray-100">
                            <div class="text-center">
                                <div class="text-xl font-black bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent">{{ $course->enrollments->count() }}</div>
                                <div class="text-xs text-gray-500 font-medium">👥 Học viên</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-black bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent">{{ $course->modules->count() }}</div>
                                <div class="text-xs text-gray-500 font-medium">📖 Module</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-black bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent">{{ substr($course->schedule ?? '1 tháng', 0, 2) }}</div>
                                <div class="text-xs text-gray-500 font-medium">⏱️ Thời gian</div>
                            </div>
                        </div>

                        <!-- Teacher Info - Only show if teacher is assigned to any enrollment -->
                        @php
                            $hasAssignedTeacher = $course->enrollments->whereNotNull('assigned_teacher_id')->count() > 0;
                        @endphp
                        @if($hasAssignedTeacher && $course->teacher)
                            <div class="mb-4 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100 flex items-center gap-3">
                                <img src="https://randomuser.me/api/portraits/men/{{ $course->teacher->id % 50 }}.jpg" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-md">
                                <div class="flex-1">
                                    <div class="text-xs text-gray-600 font-semibold uppercase">👨‍🏫 Giảng viên</div>
                                    <div class="font-bold text-sm text-gray-800">{{ $course->teacher->name }}</div>
                                </div>
                            </div>
                        @endif

                        <!-- Price & Button -->
                        <div class="flex items-center justify-between gap-3 mt-auto">
                            <div>
                                <span class="text-xs text-gray-500 font-medium">Giá:</span>
                                <div class="text-2xl font-black bg-gradient-to-r from-primary to-primary-dark bg-clip-text text-transparent">
                                    {{ number_format($course->subject->price ?? 0, 0, ',', '.') }}đ
                                </div>
                            </div>
                            <a href="{{ route('courses.show', $course->id) }}" class="flex-1 text-center bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white py-3 rounded-xl font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 group/btn">
                                <span>Khám phá</span>
                                <i class="fas fa-arrow-right text-xs group-hover/btn:translate-x-1 transition"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-20">
                <div class="mb-6">
                    <i class="fas fa-box-open text-8xl text-gray-200"></i>
                </div>
                <p class="text-2xl font-bold text-gray-700 mb-2">Không tìm thấy khóa học nào</p>
                <p class="text-gray-500">Hãy thử thay đổi các bộ lọc hoặc quay lại sau</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-16 flex justify-center">
        <div class="pagination">
            {{ $courses->links() }}
        </div>
    </div>
</div>

<style>
    .pagination {
        display: flex;
        gap: 0.5rem;
    }
    
    .pagination a, .pagination span {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        font-weight: 600;
        text-decoration: none;
    }
    
    .pagination a {
        color: #3b82f6;
        background: white;
    }
    
    .pagination a:hover {
        border-color: #3b82f6;
        background: #eff6ff;
        transform: translateY(-2px);
    }
    
    .pagination .active {
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
        color: white;
        border-color: #3b82f6;
    }
    
    .pagination .disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const sortFilter = document.getElementById('sortFilter');
        const grid = document.getElementById('coursesGrid');
        const cards = Array.from(document.querySelectorAll('.course-card'));

        // Build category options from data
        const categories = [...new Set(cards.map(c => c.dataset.category).filter(c => c))];
        categories.sort();
        categories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
            categoryFilter.appendChild(opt);
        });

        function render(filtered) {
            grid.innerHTML = '';
            filtered.forEach(c => grid.appendChild(c));
        }

        function applyFilters() {
            const q = searchInput.value.trim().toLowerCase();
            const cat = categoryFilter.value;
            let filtered = cards.filter(card => {
                const title = card.dataset.title || '';
                const category = card.dataset.category || '';
                const matchesSearch = title.includes(q);
                const matchesCategory = !cat || category === cat;
                return matchesSearch && matchesCategory;
            });

            const sortBy = sortFilter.value;
            if (sortBy === 'newest' || sortBy === 'oldest') {
                filtered.sort((a,b) => {
                    const da = new Date(a.dataset.created).getTime();
                    const db = new Date(b.dataset.created).getTime();
                    return sortBy === 'newest' ? db - da : da - db;
                });
            } else if (sortBy === 'popular') {
                filtered.sort((a,b) => Number(b.dataset.enrollments) - Number(a.dataset.enrollments));
            }

            render(filtered);
        }

        searchInput.addEventListener('input', applyFilters);
        categoryFilter.addEventListener('change', applyFilters);
        sortFilter.addEventListener('change', applyFilters);

        // First render with current page cards
        applyFilters();
    });
</script>
@endpush

@endsection
