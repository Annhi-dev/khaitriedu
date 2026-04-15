@extends('bo_cuc.ung_dung')
@section('title', 'Xác minh email')
@section('content')
<div class="max-w-md mx-auto">
    <div class="card bg-white p-8 rounded-2xl shadow-md">
        <h3 class="text-2xl font-bold text-primary-dark mb-4 text-center">Xác minh email</h3>
        <p class="text-gray-600 mb-4">Chúng tôi đã gửi mã OTP đến email <strong>{{ request('email') }}</strong>. Nhập mã để hoàn tất đăng ký.</p>

        @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg">
                {{ session('status') }} <a href="{{ route('login') }}" class="underline">Đăng nhập ngay</a>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg">{{ session('error') }}</div>
        @endif

        <form action="{{ route('verify.email.post') }}" method="post">
            @csrf
            <input type="hidden" name="email" value="{{ request('email') }}">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã OTP</label>
                <input type="text" name="code" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
            </div>
            <button type="submit" class="btn w-full py-3 bg-primary text-white rounded-xl shadow-md hover:bg-primary-dark transition">Xác minh</button>
        </form>

        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600 mb-2">Không nhận được mã?</p>
            <button id="resendBtn" type="button" class="text-primary hover:text-primary-dark underline text-sm" onclick="resendOtp()">
                Gửi lại mã
            </button>
            <p id="countdown" class="text-xs text-gray-500 mt-1 hidden">Gửi lại sau <span id="countdownTime">60</span> giây</p>
        </div>
    </div>
</div>

<script>
let countdownInterval;
let countdownTime = 60;

function resendOtp() {
    const btn = document.getElementById('resendBtn');
    const countdown = document.getElementById('countdown');
    const countdownTimeEl = document.getElementById('countdownTime');

    btn.disabled = true;
    btn.classList.add('text-gray-400');
    btn.classList.remove('text-primary', 'hover:text-primary-dark');
    countdown.classList.remove('hidden');

    countdownTime = 60;
    countdownTimeEl.textContent = countdownTime;

    countdownInterval = setInterval(() => {
        countdownTime--;
        countdownTimeEl.textContent = countdownTime;

        if (countdownTime <= 0) {
            clearInterval(countdownInterval);
            btn.disabled = false;
            btn.classList.remove('text-gray-400');
            btn.classList.add('text-primary', 'hover:text-primary-dark');
            countdown.classList.add('hidden');
        }
    }, 1000);

    fetch('{{ route("verify.email.resend") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            email: '{{ request("email") }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'mt-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm';
            successDiv.textContent = data.message;
            document.querySelector('.card').appendChild(successDiv);
            setTimeout(() => successDiv.remove(), 5000);
        } else if (data.error) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'mt-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm';
            errorDiv.textContent = data.error;
            document.querySelector('.card').appendChild(errorDiv);
            setTimeout(() => errorDiv.remove(), 5000);

            clearInterval(countdownInterval);
            btn.disabled = false;
            btn.classList.remove('text-gray-400');
            btn.classList.add('text-primary', 'hover:text-primary-dark');
            countdown.classList.add('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        clearInterval(countdownInterval);
        btn.disabled = false;
        btn.classList.remove('text-gray-400');
        btn.classList.add('text-primary', 'hover:text-primary-dark');
        countdown.classList.add('hidden');
    });
}
</script>
@endsection