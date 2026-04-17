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

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatNotificationDate(value) {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    try {
        return new Intl.DateTimeFormat('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    } catch (error) {
        return date.toLocaleString();
    }
}

function renderNotificationItems(listElement, notifications, emptyMessage) {
    if (!listElement) {
        return;
    }

    if (!Array.isArray(notifications) || notifications.length === 0) {
        listElement.innerHTML = `
            <div class="px-5 py-8 text-center text-sm text-slate-500" data-notification-empty>
                ${escapeHtml(emptyMessage || 'Chưa có thông báo nào.')}
            </div>
        `;
        return;
    }

    listElement.innerHTML = notifications.map((notification) => {
        const unreadDotClass = notification.is_read ? 'bg-slate-300' : 'bg-cyan-500';
        const typeLabel = notification.type || 'info';
        const createdAt = formatNotificationDate(notification.created_at);

        return `
            <a href="${escapeHtml(notification.open_url)}" class="block border-b border-slate-100 px-5 py-4 transition hover:bg-slate-50">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-medium text-slate-900">${escapeHtml(notification.title)}</p>
                            <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">${escapeHtml(typeLabel)}</span>
                        </div>
                        <p class="mt-1 text-sm leading-6 text-slate-500">${escapeHtml(notification.message)}</p>
                        ${createdAt ? `<p class="mt-3 text-xs text-slate-400">${escapeHtml(createdAt)}</p>` : ''}
                    </div>
                    <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full ${unreadDotClass}"></span>
                </div>
            </a>
        `;
    }).join('');
}

function updateNotificationPoller(root, data) {
    if (!root || !data) {
        return;
    }

    const unreadCount = Number(data.unread_count || 0);
    const badge = root.querySelector('[data-notification-badge]');
    const unreadCounter = root.querySelector('[data-notification-unread-count]');
    const listElement = root.querySelector('[data-notification-list]');
    const emptyMessage = root.dataset.notificationEmptyMessage || 'Chưa có thông báo nào.';

    if (badge) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
            badge.classList.remove('hidden');
        } else {
            badge.textContent = '';
            badge.classList.add('hidden');
        }
    }

    if (unreadCounter) {
        unreadCounter.textContent = String(unreadCount);
    }

    renderNotificationItems(listElement, data.notifications || [], emptyMessage);
}

function initNotificationPoller(root) {
    const url = root.dataset.notificationPollerUrl;

    if (!url) {
        return;
    }

    const listElement = root.querySelector('[data-notification-list]');
    const emptyElement = listElement ? listElement.querySelector('[data-notification-empty]') : null;

    if (emptyElement && !root.dataset.notificationEmptyMessage) {
        root.dataset.notificationEmptyMessage = emptyElement.textContent.trim();
    } else if (!root.dataset.notificationEmptyMessage) {
        root.dataset.notificationEmptyMessage = 'Chưa có thông báo nào.';
    }

    const refresh = async () => {
        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            updateNotificationPoller(root, data);
        } catch (error) {
            // Ignore polling failures and keep the last rendered state.
        }
    };

    refresh();
    const timer = window.setInterval(refresh, 30000);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            refresh();
        }
    });

    root.addEventListener('alpine:destroy', () => {
        window.clearInterval(timer);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-notification-poller]').forEach((root) => {
        initNotificationPoller(root);
    });
});
