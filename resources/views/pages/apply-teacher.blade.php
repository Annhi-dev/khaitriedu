@extends('layouts.app')

@section('title', 'Ứng tuyển giảng viên - KhaiTriEdu')

@section('content')
<div class="container mx-auto px-4 py-20">
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-primary-dark mb-4">Ứng tuyển làm giảng viên</h1>
        <p class="text-gray-600 mb-6">Chúng tôi tìm kiếm giảng viên năng động, nhiệt huyết và có kinh nghiệm giảng dạy.</p>

        @if(session('status'))
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4">{{ session('status') }}</div>
        @endif

        <form action="{{ route('apply-teacher.post') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Họ và tên</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Nhập họ tên">
                @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Nhập email">
                @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Nhập số điện thoại">
                @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kinh nghiệm giảng dạy</label>
                <textarea name="experience" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Mô tả kinh nghiệm của bạn">{{ old('experience') }}</textarea>
                @error('experience') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Lý do ứng tuyển</label>
                <textarea name="message" rows="4" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Cho chúng tôi biết tại sao bạn phù hợp">{{ old('message') }}</textarea>
                @error('message') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <p class="text-sm text-gray-500">Admin sẽ kiểm tra hồ sơ và phản hồi trong vòng 24-48 giờ.</p>

            <button type="submit" class="w-full bg-primary text-white rounded-lg py-3 font-semibold hover:bg-primary-dark transition">Gửi hồ sơ ứng tuyển</button>
        </form>
    </div>
</div>
@endsection