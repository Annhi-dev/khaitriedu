@extends('bo_cuc.quan_tri')
@section('title', 'Quản lý nhóm học')
@section('content')
<div class="space-y-6">
    <x-quan_tri.tieu_de_trang title="Quản lý nhóm học" subtitle="Danh sách này bám theo bảng danh mục và đếm đúng số khóa học thực tế đang nằm trong từng nhóm.">
        <a href="{{ route('admin.categories.create-page') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-plus mr-1"></i> Thêm nhóm học
        </a>
    </x-quan_tri.tieu_de_trang>

    <x-quan_tri.thanh_loc route="{{ route('admin.categories') }}" searchPlaceholder="Tên, slug, mô tả..." :statuses="['active' => 'Hoạt động', 'inactive' => 'Ngừng hoạt động']" />

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Nhóm học</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Chương trình</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Khóa học trong nhóm</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                    @php
                        $createCourseUrl = $category->defaultSubject
                            ? route('admin.courses', ['subject_id' => $category->defaultSubject->id, 'return_to_category_id' => $category->id])
                            : route('admin.courses', ['return_to_category_id' => $category->id]);
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $category->name }}</div>
                            <div class="text-xs text-slate-500">/{{ $category->slug }}</div>
                            <p class="text-sm text-slate-600 mt-2 max-w-md">{{ Str::limit($category->description, 80) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div>{{ $category->program ?: 'Chưa cấu hình' }}</div>
                            <div class="text-xs text-slate-500">Cấp độ: {{ $category->level ?: 'Chưa' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <x-quan_tri.huy_hieu :type="$category->status === 'active' ? 'success' : 'warning'" :text="$category->statusLabel()" />
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $category->courses_count }} khóa học</div>
                            <div class="mt-1 text-xs text-slate-500">
                                {{ $category->courses_count > 0 ? 'Đã có khóa học thực tế trong nhóm này.' : 'Chưa có khóa học nào trong nhóm này.' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ $createCourseUrl }}" class="text-emerald-600 hover:text-emerald-800" title="Tạo khóa học trong nhóm"><i class="fas fa-plus-circle"></i></a>
                            <a href="{{ route('admin.categories.show', $category) }}" class="text-cyan-600 hover:text-cyan-800" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-slate-600 hover:text-slate-800" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                            @if($category->status === 'active')
                                <form class="inline" method="post" action="{{ route('admin.categories.deactivate', $category) }}" onsubmit="return confirm('Ngừng hoạt động nhóm học này?')">
                                    @csrf
                                    <button type="submit" class="text-amber-600 hover:text-amber-800" title="Ngừng hoạt động"><i class="fas fa-pause-circle"></i></button>
                                </form>
                            @else
                                <form class="inline" method="post" action="{{ route('admin.categories.activate', $category) }}">
                                    @csrf
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800" title="Kích hoạt lại"><i class="fas fa-play-circle"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Chưa có nhóm học nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
