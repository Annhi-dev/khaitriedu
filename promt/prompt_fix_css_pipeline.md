# Prompt: Gộp CSS pipeline — KhaiTriEdu

## Bối cảnh

Đây là project Laravel 12 + Tailwind CSS v4 + Vite tên **KhaiTriEdu**.

Hiện tại CSS đang được load ở **hai nơi khác nhau** trong mỗi layout:

```html
@vite(['resources/css/app.css', 'resources/js/app.js'])   ← Tailwind (compiled)
<link rel="stylesheet" href="{{ asset('css/app.css') }}">  ← Custom CSS tĩnh (public/)
```

Vấn đề: nếu chạy không có Vite thì mất Tailwind; nếu deploy thiếu `public/css/` thì mất layout. Hai pipeline độc lập nhau là nợ kỹ thuật.

---

## Mục tiêu

Sau khi làm xong, **toàn bộ CSS chỉ đi qua một pipeline duy nhất: Vite + Tailwind**. Không còn file tĩnh `public/css/` nào được load từ Blade.

---

## Bước 1 — Gộp nội dung `public/css/` vào `resources/css/app.css`

Thay toàn bộ nội dung `resources/css/app.css` bằng nội dung sau (giữ y chang, không thêm bớt):

```css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';

    --color-primary: #2563eb;
    --color-primary-light: #60a5fa;
    --color-primary-dark: #1e40af;
    --color-primary-accent: #3b82f6;
    --color-secondary: #64748b;
}

/* ============================================================
   Layout & sidebar
   ============================================================ */

[x-cloak] {
    display: none !important;
}

.transition-smooth {
    transition: all 0.2s ease-in-out;
}

@media (min-width: 1024px) {
    #app-wrapper.sidebar-collapsed {
        grid-template-columns: 80px minmax(0, 1fr) !important;
    }

    #app-wrapper.sidebar-collapsed .sidebar-header-text,
    #app-wrapper.sidebar-collapsed .sidebar-text,
    #app-wrapper.sidebar-collapsed .sidebar-badge {
        display: none !important;
    }

    #app-wrapper.sidebar-collapsed .sidebar-menu-item {
        justify-content: center;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    #app-wrapper.sidebar-collapsed .sidebar-icon {
        margin: 0;
        font-size: 1.25rem;
    }
}

:root {
    --student-sidebar-expanded: 280px;
    --student-sidebar-collapsed: 88px;
}

@media (min-width: 1024px) {
    #student-shell {
        grid-template-columns: var(--student-sidebar-expanded) minmax(0, 1fr);
    }

    #student-shell.student-sidebar-collapsed {
        grid-template-columns: var(--student-sidebar-collapsed) minmax(0, 1fr);
    }

    #student-shell.student-sidebar-collapsed .student-brand-copy,
    #student-shell.student-sidebar-collapsed .student-user-copy,
    #student-shell.student-sidebar-collapsed .student-nav-text,
    #student-shell.student-sidebar-collapsed .student-sidebar-note,
    #student-shell.student-sidebar-collapsed .student-sidebar-footer {
        display: none !important;
    }

    #student-shell.student-sidebar-collapsed .student-nav-link,
    #student-shell.student-sidebar-collapsed .student-sidebar-action {
        justify-content: center;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }

    #student-shell.student-sidebar-collapsed .student-nav-icon,
    #student-shell.student-sidebar-collapsed .student-sidebar-action i {
        margin-right: 0 !important;
    }

    #student-shell.student-sidebar-collapsed .student-user-panel {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
        justify-content: center;
    }
}

.dashboard-wrapper {
    display: flex;
    min-height: 100vh;
    background-color: #f8fafc;
}

.dashboard-sidebar {
    width: 260px;
    flex-shrink: 0;
    background-color: #ffffff;
    border-right: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
}

.dashboard-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.dashboard-header {
    height: 64px;
    background-color: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
}

.dashboard-content {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    background-color: #f8fafc;
}

.sidebar-logo {
    height: 64px;
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 700;
    font-size: 1.25rem;
    color: #1e293b;
}

.sidebar-nav {
    padding: 1rem 0;
    flex: 1;
    overflow-y: auto;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: #64748b;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.nav-item:hover {
    background-color: #f1f5f9;
    color: #2563eb;
}

.nav-item.active {
    background-color: #eff6ff;
    color: #2563eb;
    border-right: 3px solid #2563eb;
}

.nav-icon {
    width: 24px;
    text-align: center;
    margin-right: 0.75rem;
    font-size: 1.1rem;
}

.content-card-wrapper {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 2rem;
    border: 1px solid #f1f5f9;
}

@media (max-width: 768px) {
    .dashboard-sidebar {
        position: fixed;
        z-index: 50;
        height: 100vh;
        transform: translateX(-100%);
    }

    .sidebar-open .dashboard-sidebar {
        transform: translateX(0);
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 40;
    }

    .sidebar-open .sidebar-overlay {
        display: block;
    }
}

/* ============================================================
   Animations
   ============================================================ */

@keyframes blob {
    0%   { transform: translate(0px, 0px) scale(1); }
    33%  { transform: translate(30px, -50px) scale(1.1); }
    66%  { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.ripple {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple-animation 0.6s ease-out;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

.btn, .nav-link, .card, .stat-card {
    position: relative;
    overflow: hidden;
}

@keyframes fade-up {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

.fade-up {
    animation: fade-up 0.8s ease-out forwards;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}

/* ============================================================
   Pagination
   ============================================================ */

.pagination {
    display: flex;
    gap: 0.5rem;
}

.pagination a,
.pagination span {
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    font-weight: 600;
    text-decoration: none;
}

.pagination a {
    color: #2563eb;
    background: #ffffff;
}

.pagination a:hover {
    border-color: #2563eb;
    background: #eff6ff;
    transform: translateY(-2px);
}

.pagination .active {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border-color: #2563eb;
}

.pagination .disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ============================================================
   Misc
   ============================================================ */

.contact-map-iframe {
    border: 0;
}
```

---

## Bước 2 — Xóa dòng `asset('css/app.css')` khỏi 5 layout files

Tìm và xóa **chính xác dòng sau** trong mỗi file (xóa cả dòng, không để lại dòng trống thừa):

```html
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
```

Các file cần xóa:
- `resources/views/bo_cuc/quan_tri.blade.php`
- `resources/views/bo_cuc/giao_vien.blade.php`
- `resources/views/bo_cuc/hoc_vien.blade.php`
- `resources/views/bo_cuc/bang_dieu_khien.blade.php`
- `resources/views/bo_cuc/ung_dung.blade.php`

Sau khi xóa, mỗi layout chỉ còn một dòng load CSS:

```html
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

---

## Bước 3 — Thêm `public/css/` vào `.gitignore`

Mở file `.gitignore` ở root project, thêm vào cuối:

```
# Custom CSS đã được merge vào resources/css/app.css
public/css/
```

---

## Bước 4 — Xóa `public/css/` khỏi git tracking

Chạy lệnh sau để untrack các file tĩnh cũ (giữ file trên disk, chỉ bỏ khỏi git):

```bash
git rm --cached public/css/app.css public/css/button.css public/css/form.css public/css/layout.css public/css/table.css
```

> Nếu một số file không tồn tại thì bỏ qua, không phải lỗi.

---

## Kiểm tra sau khi xong

```bash
# 1. Không còn asset('css/app.css') nào trong views
grep -rn "asset('css/app.css')" resources/views/
# → Phải trả về rỗng

# 2. Chỉ còn một dòng CSS trong mỗi layout
grep -n "stylesheet\|vite\|css" resources/views/bo_cuc/quan_tri.blade.php
# → Chỉ thấy @vite(...) và Google Fonts, không có asset('css/...')

# 3. app.css có đủ nội dung
wc -l resources/css/app.css
# → Phải >= 300 dòng

# 4. Build thử (nếu môi trường có Node)
npm run build
# → Không lỗi
```

---

## Ràng buộc

- **KHÔNG** thay đổi bất cứ thứ gì khác ngoài 4 bước trên
- **KHÔNG** sửa Blade views, controllers, routes, hay logic PHP
- **KHÔNG** đổi nội dung CSS (copy y chang từ bước 1, không thêm bớt)
- **KHÔNG** xóa thư mục `public/css/` trên disk — chỉ untrack khỏi git
