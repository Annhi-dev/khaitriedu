@extends('layouts.app')
@section('title','Quản lý đăng ký')
@section('content')
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div>
      <h1 class="text-2xl font-bold">Quản lý đăng ký khóa học</h1>
      @php $newCount = $enrollments->where('status', 'pending')->where('is_submitted', true)->count(); @endphp
      @if($newCount > 0)
        <p class="text-sm text-gray-600 mt-1">🔴 <strong>{{ $newCount }} yêu cầu mới</strong> cần xây lựa</p>
      @endif
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn border px-3 py-2 rounded">Quay lại</a>
  </div>
  
  @if(session('status'))<div class="bg-green-100 text-green-800 p-3 rounded mb-3">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ session('error') }}</div>@endif
  
  <div class="overflow-x-auto">
    <table class="w-full border-collapse text-sm">
      <thead>
        <tr class="bg-gray-100">
          <th class="border p-2">ID</th>
          <th class="border p-2">Học viên</th>
          <th class="border p-2">Khóa học</th>
          <th class="border p-2">Giờ học</th>
          <th class="border p-2">Các thứ</th>
          <th class="border p-2">Giảng viên</th>
          <th class="border p-2">Trạng thái</th>
          <th class="border p-2">Xử lý</th>
        </tr>
      </thead>
      <tbody>
        @foreach($enrollments as $enrollment)
        @php
          $isNew = $enrollment->status === 'pending' && $enrollment->is_submitted;
          $selectedDays = $enrollment->preferred_days ? json_decode($enrollment->preferred_days, true) : [];
          $dayLabels = [
            'Monday' => 'T2', 'Tuesday' => 'T3', 'Wednesday' => 'T4', 
            'Thursday' => 'T5', 'Friday' => 'T6', 'Saturday' => 'T7', 'Sunday' => 'CN'
          ];
        @endphp
        <tr class="odd:bg-white even:bg-gray-50 {{ $isNew ? 'bg-yellow-50 border-l-4 border-l-red-500' : '' }}">
          <td class="border p-2">
            {{ $enrollment->id }}
            @if($isNew)
              <span class="ml-1 inline-block w-2 h-2 bg-red-500 rounded-full" title="Yêu cầu mới"></span>
            @endif
          </td>
          <td class="border p-2">{{ $enrollment->user?->name }}</td>
          <td class="border p-2">{{ $enrollment->course?->title }}</td>
          <td class="border p-2">
            @if($enrollment->start_time)
              {{ $enrollment->start_time }} - {{ $enrollment->end_time }}
            @else
              {{ $enrollment->preferred_schedule ?? 'N/A' }}
            @endif
          </td>
          <td class="border p-2">
            @if($selectedDays)
              {{ implode(', ', array_map(fn($d) => $dayLabels[$d] ?? $d, $selectedDays)) }}
            @else
              -
            @endif
          </td>
          <td class="border p-2">{{ $enrollment->assignedTeacher?->name ?? 'Chưa gán' }}</td>
          <td class="border p-2">
            <span class="px-2 py-1 rounded text-xs font-semibold
              {{ $enrollment->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
              {{ $enrollment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
              {{ $enrollment->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
            ">
              {{ $enrollment->status }}
            </span>
            @if($enrollment->status === 'rejected' && $enrollment->note)
            <p class="text-xs text-red-700 mt-1 bg-red-50 p-1 rounded">{{ $enrollment->note }}</p>
            @endif
          </td>
          <td class="border p-2">
            <form method="post" action="{{ route('admin.enrollments.update', $enrollment->id) }}" class="space-y-2">
              @csrf
              <div>
                <select name="status" class="border rounded px-2 py-1 text-xs w-full status-select" data-enrollment-id="{{ $enrollment->id }}">
                  <option value="pending" @if($enrollment->status=='pending') selected @endif>pending</option>
                  <option value="confirmed" @if($enrollment->status=='confirmed') selected @endif>confirmed</option>
                  <option value="rejected" @if($enrollment->status=='rejected') selected @endif>rejected</option>
                </select>
              </div>
              
              <div>
                <select name="assigned_teacher_id" class="border rounded px-2 py-1 text-xs w-full">
                  <option value="">Chọn giảng viên</option>
                  @foreach($teachers as $t)
                  <option value="{{ $t->id }}" @if($enrollment->assigned_teacher_id==$t->id) selected @endif>{{ $t->name }}</option>
                  @endforeach
                </select>
              </div>
              
              <div>
                <input name="schedule" value="{{ $enrollment->schedule }}" placeholder="Lịch cụ thể" class="border rounded px-2 py-1 text-xs w-full" />
              </div>
              
              <div class="rejection-reason-container" style="{{ $enrollment->status !== 'rejected' ? 'display:none;' : '' }}">
                <textarea name="note" placeholder="Lý do từ chối (sẽ gửi cho học viên)" 
                  class="border rounded px-2 py-1 text-xs w-full" rows="2">{{ $enrollment->note ?? '' }}</textarea>
                <p class="text-gray-500 text-xs mt-1">Học viên sẽ nhận được thông báo này và có thể sửa lại</p>
              </div>
              
              <button class="btn bg-primary text-white px-2 py-1 rounded text-xs w-full">Cập nhật</button>
            </form>
            
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                const statusSelect = document.querySelector('[data-enrollment-id="{{ $enrollment->id }}"]');
                const reasonContainer = statusSelect.closest('form').querySelector('.rejection-reason-container');
                
                statusSelect.addEventListener('change', function() {
                  reasonContainer.style.display = this.value === 'rejected' ? '' : 'none';
                });
              });
            </script>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection