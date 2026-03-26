<?php $__env->startSection('title', 'Xác minh email'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto">
    <div class="card bg-white p-8 rounded-2xl shadow-md">
        <h3 class="text-2xl font-bold text-primary-dark mb-4 text-center">Xác minh email</h3>
        <p class="text-gray-600 mb-4">Chúng tôi đã gửi mã OTP đến email <strong><?php echo e(request('email')); ?></strong>. Nhập mã để hoàn tất đăng ký.</p>

        <?php if(session('status')): ?>
            <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg">
                <?php echo e(session('status')); ?> <a href="<?php echo e(route('login')); ?>" class="underline">Đăng nhập ngay</a>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <form action="<?php echo e(route('verify.email.post')); ?>" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="email" value="<?php echo e(request('email')); ?>">
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

    // Disable button and show countdown
    btn.disabled = true;
    btn.classList.add('text-gray-400');
    btn.classList.remove('text-primary', 'hover:text-primary-dark');
    countdown.classList.remove('hidden');

    // Start countdown
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

    // Send AJAX request
    fetch('<?php echo e(route("verify.email.resend")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({
            email: '<?php echo e(request("email")); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'mt-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm';
            successDiv.textContent = data.message;
            document.querySelector('.card').appendChild(successDiv);
            setTimeout(() => successDiv.remove(), 5000);
        } else if (data.error) {
            // Show error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'mt-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm';
            errorDiv.textContent = data.error;
            document.querySelector('.card').appendChild(errorDiv);
            setTimeout(() => errorDiv.remove(), 5000);

            // Reset countdown if error
            clearInterval(countdownInterval);
            btn.disabled = false;
            btn.classList.remove('text-gray-400');
            btn.classList.add('text-primary', 'hover:text-primary-dark');
            countdown.classList.add('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Reset countdown on error
        clearInterval(countdownInterval);
        btn.disabled = false;
        btn.classList.remove('text-gray-400');
        btn.classList.add('text-primary', 'hover:text-primary-dark');
        countdown.classList.add('hidden');
    });
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\auth\verify_email.blade.php ENDPATH**/ ?>