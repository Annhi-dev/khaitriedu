@extends('layouts.app')

@section('title', 'Khóa học - KhaiTriEdu')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="mb-16 text-center">
        <h1 class="text-5xl md:text-6xl font-black bg-gradient-to-r from-primary via-blue-600 to-primary bg-clip-text text-transparent mb-4">Khám phá khóa học</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">Chọn khóa học bạn quan tâm theo nhóm ngành, gửi khung giờ mong muốn và admin sẽ xếp bạn vào lớp phù hợp.</p>
    </div>

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
                    <i class="fas fa-layer-group text-primary"></i> Nhóm ngành
                </label>
                <select id="categoryFilter" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:border-primary focus:outline-none transition" onchange="window.location.href = '{{ route('courses.index') }}?category=' + (this.value ? this.value : '')">
                    <option value="">Tất cả nhóm ngành</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold mb-3 text-gray-700 flex items-center gap-2">
                    <i class="fas fa-sort text-primary"></i> Sắp xếp
                </label>
                <select id="sortFilter" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:border-primary focus:outline-none transition">
                    <option value="newest">Mới nhất</option>
                    <option value="oldest">Cũ nhất</option>
                    <option value="popular">Nhiều đăng ký nhất</option>
                </select>
            </div>
        </div>
    </div>

    <div id="coursesGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($courses as $course)
            <div class="relative group course-card"
                 data-title="{{ strtolower($course->name) }}"
                 data-category="{{ strtolower($course->category->slug ?? '') }}"
                 data-enrollments="{{ $course->enrollments_count ?? 0 }}"
                 data-created="{{ $course->created_at ?? now() }}">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-primary via-blue-500 to-primary rounded-3xl opacity-0 group-hover:opacity-100 transition duration-500 blur-lg"></div>

                <div class="relative card bg-white rounded-3xl shadow-xl group-hover:shadow-2xl transition-all duration-300 overflow-hidden h-full flex flex-col">
                    <div class="relative overflow-hidden h-56 bg-gradient-to-br from-blue-100 to-sky-50">
                        <img src="{{ $course->image_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80' }}"
                             alt="{{ $course->name }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition duration-300">

                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>

                        <div class="absolute top-4 left-4 bg-gradient-to-r from-primary to-primary-dark text-white text-xs font-bold px-5 py-2 rounded-full shadow-lg backdrop-blur-sm">
                            {{ $course->category?->name ?? 'Khóa học' }}
                        </div>

                        <div class="absolute top-4 right-4 bg-white/90 text-primary text-xs font-bold px-4 py-2 rounded-full shadow-lg">
                            {{ ($course->courses_count ?? 0) > 0 ? ($course->courses_count . ' lớp mở') : 'Chờ xếp lớp' }}
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h4 class="text-xl font-bold mb-2 line-clamp-2 text-gray-800 group-hover:text-primary transition">{{ $course->name }}</h4>
                            <p class="text-gray-600 text-sm line-clamp-3">{{ $course->description ?? 'Khóa học được thiết kế linh hoạt để admin xếp lớp theo nhu cầu thực tế của học viên.' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="rounded-2xl bg-blue-50 p-4 text-center border border-blue-100">
                                <div class="text-2xl font-black text-primary">{{ $course->enrollments_count ?? 0 }}</div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Đăng ký</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 text-center border border-slate-100">
                                <div class="text-2xl font-black text-slate-700">{{ $course->courses_count ?? 0 }}</div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Lớp hiện có</div>
                            </div>
                        </div>

                        <div class="mt-auto flex items-end justify-between gap-3">
                            <div>
                                <div class="text-xs text-gray-500 font-medium">Học phí tham khảo</div>
                                <div class="text-2xl font-black bg-gradient-to-r from-primary to-primary-dark bg-clip-text text-transparent">
                                    {{ number_format($course->price ?? 0, 0, ',', '.') }}đ
                                </div>
                            </div>
                            <a href="{{ route('khoa-hoc.show', $course->id) }}" class="flex-1 text-center bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white py-3 rounded-xl font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 group/btn">
                                <span>Xem chi tiết</span>
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
                <p class="text-2xl font-bold text-gray-700 mb-2">Chưa có khóa học nào phù hợp</p>
                <p class="text-gray-500">Hãy thử đổi nhóm ngành hoặc quay lại sau.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-16 flex justify-center">
        <div class="pagination">
            {{ $courses->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const sortFilter = document.getElementById('sortFilter');
        const grid = document.getElementById('coursesGrid');
        const cards = Array.from(document.querySelectorAll('.course-card'));

        function render(filtered) {
            grid.innerHTML = '';
            filtered.forEach(card => grid.appendChild(card));
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
                filtered.sort((a, b) => {
                    const da = new Date(a.dataset.created).getTime();
                    const db = new Date(b.dataset.created).getTime();
                    return sortBy === 'newest' ? db - da : da - db;
                });
            } else if (sortBy === 'popular') {
                filtered.sort((a, b) => Number(b.dataset.enrollments) - Number(a.dataset.enrollments));
            }

            render(filtered);
        }

        searchInput.addEventListener('input', applyFilters);
        categoryFilter.addEventListener('change', applyFilters);
        sortFilter.addEventListener('change', applyFilters);

        applyFilters();
    });
</script>
@endpush

@endsection
