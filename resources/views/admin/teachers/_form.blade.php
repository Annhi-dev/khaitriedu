@php
    $statusValue = old('status', $teacher->status ?? \App\Models\User::STATUS_ACTIVE);
    $departmentValue = old('department_id', $teacher->department_id ?? '');
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Họ và tên</label>
        <input name="name" value="{{ old('name', $teacher->name ?? '') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Tên đăng nhập</label>
        <input name="username" value="{{ old('username', $teacher->username ?? '') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
        <input name="email" type="email" value="{{ old('email', $teacher->email ?? '') }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Số điện thoại</label>
        <input name="phone" value="{{ old('phone', $teacher->phone ?? '') }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Phòng ban</label>
        <select name="department_id" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            <option value="">Chọn phòng ban</option>
            @foreach(($departments ?? collect()) as $department)
                <option value="{{ $department->id }}" @selected((string) $departmentValue === (string) $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        @if(($departments ?? collect())->isEmpty())
            <p class="mt-2 text-xs text-amber-600">Chưa có phòng ban hoạt động.</p>
        @endif
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Trạng thái tài khoản</label>
        <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            <option value="{{ \App\Models\User::STATUS_ACTIVE }}" @selected($statusValue === \App\Models\User::STATUS_ACTIVE)>Hoạt động</option>
            <option value="{{ \App\Models\User::STATUS_INACTIVE }}" @selected($statusValue === \App\Models\User::STATUS_INACTIVE)>Tạm dừng</option>
        </select>
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">{{ isset($teacher) ? 'Mật khẩu mới' : 'Mật khẩu' }}</label>
        <input name="password" type="password" {{ isset($teacher) ? '' : 'required' }} class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Xác nhận mật khẩu</label>
        <input name="password_confirmation" type="password" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
    </div>
</div>

<div class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
    <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Quay lại danh sách</a>
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">{{ $submitLabel }}</button>
</div>
