@extends('layouts.app')

@section('title', 'Liên hệ - KhaiTriEdu')

@section('content')
<div class="bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 text-white py-20">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Liên hệ chúng tôi</h1>
        <p class="text-xl text-blue-100">Chúng tôi luôn sẵn sàng trả lời mọi câu hỏi của bạn</p>
    </div>
</div>

<div class="container mx-auto px-4 py-20">
    <div class="grid md:grid-cols-3 gap-8 mb-20">
        <!-- Contact Info -->
        <div class="space-y-8">
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center text-primary text-xl">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-1">Địa chỉ</h3>
                        <p class="text-gray-600">123 Đường ABC, Quận 1, TP.HCM, Việt Nam</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center text-primary text-xl">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-1">Điện thoại</h3>
                        <p class="text-gray-600">+84 123 456 789</p>
                        <p class="text-gray-600">Giờ làm việc: 8:00 - 18:00 (Thứ 2 - Thứ 6)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center text-primary text-xl">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-1">Email</h3>
                        <p class="text-gray-600">support@khaitriedu.com</p>
                        <p class="text-gray-600">info@khaitriedu.com</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Theo dõi chúng tôi</h3>
                <div class="flex gap-3">
                    <a href="https://www.facebook.com/profile.php?id=61575515763147" target="_blank" class="w-10 h-10 bg-primary text-white rounded-lg flex items-center justify-center hover:bg-primary-dark transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.youtube.com/channel/UCPrE7RBNFZHZAxJzvCxvMSg" target="_blank" class="w-10 h-10 bg-primary text-white rounded-lg flex items-center justify-center hover:bg-primary-dark transition">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://zalo.me/84867852853" target="_blank" class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center hover:bg-primary-dark transition">
                        <img src="{{ asset('hinh/zalo.png') }}" alt="Zalo" class="w-6 h-6">
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Gửi tin nhắn cho chúng tôi</h2>
                
                @if(session('status'))
                    <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.post') }}" class="space-y-6">
                    @csrf
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Họ và tên</label>
                            <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('name') border-red-500 @enderror" placeholder="Nhập tên của bạn">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Email</label>
                            <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('email') border-red-500 @enderror" placeholder="Nhập email của bạn">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Tiêu đề</label>
                        <input type="text" name="subject" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('subject') border-red-500 @enderror" placeholder="Vấn đề cần giải quyết">
                        @error('subject')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Nội dung</label>
                        <textarea name="message" required rows="6" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('message') border-red-500 @enderror" placeholder="Nhập tin nhắn của bạn..."></textarea>
                        @error('message')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="w-full bg-primary text-white font-semibold py-3 rounded-lg hover:bg-primary-dark transition">
                            <i class="fas fa-paper-plane mr-2"></i> Gửi tin nhắn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Map Section (Optional) -->
    <div class="bg-gray-200 rounded-2xl overflow-hidden h-96 mb-20">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1569.6935022522895!2d105.43735901504439!3d10.367556123108891!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a72e6226e4093%3A0xdc2db2a3b1ff6bb4!2zVHJ1bmcgdMsQY2ggRMO0eSBEacOgIEtoaSBUcuG7qyBLaGFpIFTDtGk!5e0!3m2!1svi!2s!4v1700000000000!5m2!1svi!2s" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

    <!-- FAQ -->
    <div class="mb-20">
        <h2 class="text-4xl font-bold text-gray-800 mb-12 text-center">Câu hỏi thường gặp</h2>
        <div class="space-y-4 max-w-3xl mx-auto">
            <details class="bg-white p-6 rounded-xl shadow-sm cursor-pointer">
                <summary class="font-bold text-gray-800 flex items-center justify-between">
                    <span>Bạn có hỗ trợ khách hàng 24/7 không?</span>
                    <i class="fas fa-chevron-down"></i>
                </summary>
                <p class="text-gray-600 mt-4">Chúng tôi hỗ trợ từ 8:00 - 18:00, Thứ 2 - Thứ 6. Ngoài giờ làm việc, bạn có thể gửi email và chúng tôi sẽ phản hồi trong 24 giờ.</p>
            </details>
            <details class="bg-white p-6 rounded-xl shadow-sm cursor-pointer">
                <summary class="font-bold text-gray-800 flex items-center justify-between">
                    <span>Làm sao để hoàn lại tiền?</span>
                    <i class="fas fa-chevron-down"></i>
                </summary>
                <p class="text-gray-600 mt-4">Nếu không hài lòng trong vòng 7 ngày, chúng tôi sẽ hoàn lại 100% tiền học phí không đặt câu hỏi.</p>
            </details>
            <details class="bg-white p-6 rounded-xl shadow-sm cursor-pointer">
                <summary class="font-bold text-gray-800 flex items-center justify-between">
                    <span>Có hỗ trợ sau hoàn thành khóa học không?</span>
                    <i class="fas fa-chevron-down"></i>
                </summary>
                <p class="text-gray-600 mt-4">Có, bạn có quyền truy cập khóa học trọn đời và hỗ trợ cộng đồng vĩnh viễn.</p>
            </details>
        </div>
    </div>
</div>
@endsection
