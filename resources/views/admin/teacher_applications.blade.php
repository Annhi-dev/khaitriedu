@extends('layouts.app')
@section('title', 'Quản lý ứng tuyển giảng viên')
@section('content')
<div class="bg-white p-6 rounded-2xl shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-primary-dark">Quản lý ứng tuyển giảng viên</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn border border-gray-300 px-3 py-2 rounded-lg hover:bg-gray-100 transition">Quay lại</a>
    </div>

    @if(session('status'))
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4">{{ session('status') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Tên</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Email</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">SĐT</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Ngày</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-500">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($applications as $app)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $app->id }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $app->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $app->email }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $app->phone ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm font-semibold {{ $app->status=='pending' ? 'text-yellow-600' : ($app->status=='approved' ? 'text-green-600' : 'text-red-600') }}">{{ ucfirst($app->status) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $app->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm text-center">
                        <a href="{{ route('admin.teacher-applications.show', $app->id) }}" class="px-2 py-1 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-xs">Chi tiết</a>
                        @if($app->status == 'pending')
                        <form action="{{ route('admin.teacher-applications.review', $app->id) }}" method="POST" class="inline-block ml-1">
                            @csrf
                            <input type="hidden" name="action" value="approved">
                            <button type="submit" class="px-2 py-1 bg-green-500 text-white rounded-lg hover:bg-green-600 text-xs">Duyệt</button>
                        </form>
                        <form action="{{ route('admin.teacher-applications.review', $app->id) }}" method="POST" class="inline-block ml-1">
                            @csrf
                            <input type="hidden" name="action" value="rejected">
                            <button type="submit" class="px-2 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 text-xs">Từ chối</button>
                        </form>
                        @else
                            <span class="text-xs text-gray-500">Hoàn tất</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">Chưa có hồ sơ ứng tuyển</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection