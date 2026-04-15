import './bootstrap';

document.addEventListener('click', function (e) {
    const target = e.target.closest('.btn, .nav-link, .card');
    if (!target) return;

    const ripple = document.createElement('span');
    const rect = target.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.className = 'ripple';

    target.appendChild(ripple);

    setTimeout(() => {
        ripple.remove();
    }, 600);
});
