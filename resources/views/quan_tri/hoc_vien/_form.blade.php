@php
    $statusValue = old('status', $student->status ?? \App\Models\NguoiDung::STATUS_ACTIVE);
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Họ và tên <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $student->name ?? '') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            @error('name')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tên đăng nhập <span class="text-rose-500">*</span></label>
            <input type="text" name="username" value="{{ old('username', $student->username ?? '') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            @error('username')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-rose-500">*</span></label>
            <input type="email" name="email" value="{{ old('email', $student->email ?? '') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            @error('email')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Số điện thoại</label>
            <input type="text" name="phone" value="{{ old('phone', $student->phone ?? '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            @error('phone')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
            <select name="status" class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:ring-cyan-500 focus:border-cyan-500">
                <option value="{{ \App\Models\NguoiDung::STATUS_ACTIVE }}" @selected($statusValue === \App\Models\NguoiDung::STATUS_ACTIVE)>Hoạt động</option>
                <option value="{{ \App\Models\NguoiDung::STATUS_INACTIVE }}" @selected($statusValue === \App\Models\NguoiDung::STATUS_INACTIVE)>Tạm dừng</option>
            </select>
            @error('status')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ isset($student) ? 'Mật khẩu mới' : 'Mật khẩu' }}</label>
            <input type="password" name="password" {{ isset($student) ? '' : 'required' }} class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:ring-cyan-500 focus:border-cyan-500">
            @error('password')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Xác nhận mật khẩu</label>
            <input type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-300 px-4 py-2 focus:ring-cyan-500 focus:border-cyan-500">
        </div>
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
        <a href="{{ route('admin.students.index') }}" class="border border-slate-300 hover:bg-slate-50 px-5 py-2 rounded-xl text-sm font-medium transition">Hủy</a>
        <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-5 py-2 rounded-xl text-sm font-semibold transition">{{ $submitLabel }}</button>
    </div>
</div>
