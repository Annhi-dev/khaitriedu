@extends('bo_cuc.ung_dung')

@section('title', 'Đội ngũ giảng viên - KhaiTriEdu')

@section('content')
<div class="bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 text-white py-20">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Đội ngũ giảng viên</h1>
        <p class="text-xl text-blue-100">Những chuyên gia hàng đầu trong lĩnh vực, cam kết truyền đạt kiến thức chất lượng cao</p>
    </div>
</div>

<div class="container mx-auto px-4 py-20">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
        @forelse($teachers as $teacher)
            @php
                $field = $teacher->specialtyLabel() ?? 'Giảng viên chuyên nghiệp';
            @endphp
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
                <div class="relative overflow-hidden h-64 bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center">
                    <img src="https://randomuser.me/api/portraits/men/{{ $loop->index + 10 }}.jpg" alt="Teacher" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                </div>
                <div class="p-6 text-center">
                    <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $teacher->displayName() }}</h3>
                    <p class="text-primary font-semibold mb-3">{{ $field }}</p>
                    <p class="text-gray-600 text-sm mb-4">Hơn 8 năm kinh nghiệm, đã đào tạo hơn 500+ học viên.</p>
                    <div class="flex justify-center gap-2">
                        <a href="#" class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm hover:bg-primary hover:text-white transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm hover:bg-primary hover:text-white transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm hover:bg-primary hover:text-white transition">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-4 text-center py-12 bg-white rounded-2xl shadow-md">
                <p class="text-gray-600">Chưa có giảng viên nào được đăng ký.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
