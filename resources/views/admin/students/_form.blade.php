@php
    $statusValue = old('status', $student->status ?? \App\Models\User::STATUS_ACTIVE);
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Họ và tên</label>
        <input name="name" value="{{ old('name', $student->name ?? '') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Tên đăng nhập</label>
        <input name="username" value="{{ old('username', $student->username ?? '') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
        <input name="email" type="email" value="{{ old('email', $student->email ?? '') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Số điện thoại</label>
        <input name="phone" value="{{ old('phone', $student->phone ?? '') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Trạng thái tài khoản</label>
        <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            <option value="{{ \App\Models\User::STATUS_ACTIVE }}" @selected($statusValue === \App\Models\User::STATUS_ACTIVE)>Hoạt động</option>
            <option value="{{ \App\Models\User::STATUS_INACTIVE }}" @selected($statusValue === \App\Models\User::STATUS_INACTIVE)>Tạm dừng</option>
        </select>
    </div>
    <div class="rounded-3xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
        <p class="font-semibold">Ghi chú quản trị</p>
        <p class="mt-2 leading-6">Role của tài khoản luôn được giữ là học viên. Trạng thái khóa chỉ đổi bằng nút khóa hoặc mở khóa ở màn quản lý.</p>
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">{{ isset($student) ? 'Mật khẩu mới' : 'Mật khẩu' }}</label>
        <input name="password" type="password" {{ isset($student) ? '' : 'required' }} class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
        <p class="mt-2 text-xs text-slate-500">{{ isset($student) ? 'Để trống nếu không đổi mật khẩu.' : 'Tối thiểu 6 ký tự.' }}</p>
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Xác nhận mật khẩu</label>
        <input name="password_confirmation" type="password" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
</div>

<div class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
    <a href="{{ route('admin.students.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Quay lại danh sách</a>
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">{{ $submitLabel }}</button>
</div>
