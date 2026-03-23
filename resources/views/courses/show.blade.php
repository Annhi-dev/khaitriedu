@extends('layouts.app')
@section('title',$course->title)
@section('content')
<div class="max-w-4xl mx-auto">
  <div class="mb-4"><a href="{{ route('courses.index') }}" class="text-primary hover:underline">← Quay lại danh sách</a></div>
  <div class="card p-4 rounded-xl shadow-sm">
    <h1 class="text-2xl font-bold">{{ $course->title }}</h1>
    <div class="text-gray-600 mb-2">Môn: {{ $course->subject->name ?? 'N/A' }}</div>
    <div class="text-gray-600 mb-2">Giảng viên: {{ $course->teacher?->name ?? 'Chưa gán' }}</div>
    <div class="text-gray-600 mb-2">Lịch: {{ $course->schedule ?? 'Chưa có' }}</div>
    <p class="text-sm text-gray-700 mb-4">{{ $course->description ?? 'Không có mô tả' }}</p>
    <h3 class="font-semibold mb-2">Module khóa học</h3>
    @if($course->modules->isEmpty())<div class="text-gray-500">Chưa có module.</div>@else
      <ul class="space-y-2">
      @foreach($course->modules as $m)
        <li class="border rounded p-2"><div class="font-medium">{{ $m->position ?? $loop->iteration }}. {{ $m->title }}</div><p class="text-sm text-gray-600">{{ $m->content ?? 'Không có nội dung' }}</p></li>
      @endforeach
      </ul>
    @endif
    @if($user && $user->role === 'hoc_vien')
    <div class="mt-4 border-t pt-3">
      <h4 class="font-semibold mb-3">📋 Đăng ký khóa học</h4>
      @php
        $userEnrollment = \App\Models\Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->first();
        $selectedDays = $userEnrollment ? json_decode($userEnrollment->preferred_days, true) ?? [] : [];
        $weekdays = ['Monday' => 'Thứ 2', 'Tuesday' => 'Thứ 3', 'Wednesday' => 'Thứ 4', 'Thursday' => 'Thứ 5', 'Friday' => 'Thứ 6', 'Saturday' => 'Thứ 7', 'Sunday' => 'Chủ nhật'];
      @endphp
      
      @if($userEnrollment)
        <div class="bg-blue-50 border-l-4 border-l-blue-500 p-3 rounded mb-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-blue-900">Trạng thái yêu cầu:</p>
              <div class="mt-1">
                @if($userEnrollment->status === 'pending')
                  <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded text-sm font-medium">⏳ Đang chờ duyệt</span>
                @elseif($userEnrollment->status === 'confirmed')
                  <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded text-sm font-medium">✓ Đã được duyệt</span>
                @else
                  <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded text-sm font-medium">✗ Bị từ chối</span>
                @endif
              </div>
            </div>
            <div class="text-right text-sm text-gray-600">
              @if($userEnrollment->submitted_at)
                <p>Gửi: {{ \Carbon\Carbon::parse($userEnrollment->submitted_at)->format('d/m/Y H:i') }}</p>
              @endif
              @if($userEnrollment->assignedTeacher)
                <p class="mt-1">Giáo viên: <strong>{{ $userEnrollment->assignedTeacher->name }}</strong></p>
              @endif
            </div>
          </div>
          
          @if($userEnrollment->status === 'rejected' && $userEnrollment->note)
          <div class="mt-3 pt-3 border-t border-red-200 bg-red-50 p-2 rounded">
            <p class="text-sm text-red-800"><strong>💬 Lý do từ chối:</strong></p>
            <p class="text-sm text-red-700 mt-1">{{ $userEnrollment->note }}</p>
          </div>
          @endif
          
          @if($userEnrollment->start_time)
            <div class="mt-3 pt-3 border-t border-blue-200">
              <p class="text-sm text-gray-700">
                <strong>Giờ học:</strong> {{ $userEnrollment->start_time }} - {{ $userEnrollment->end_time }}<br>
                <strong>Các thứ:</strong> {{ implode(', ', array_map(fn($d) => $weekdays[$d] ?? $d, $selectedDays)) }}
              </p>
            </div>
          @endif
        </div>
        
        @if($userEnrollment->status === 'rejected')
        <div class="bg-orange-50 border border-orange-200 p-3 rounded mb-3">
          <p class="text-sm text-orange-800">
            <strong>⚠️ Yêu cầu của bạn bị từ chối</strong><br>
            Vui lòng kiểm tra lý do trên và cập nhật thông tin dưới đây, sau đó gửi lại.
          </p>
        </div>
        @elseif($userEnrollment->status !== 'confirmed')
        <p class="text-sm text-gray-600 mb-3 bg-gray-50 p-2 rounded">
          ✏️ Bạn có thể cập nhật lịch học yêu cầu dưới đây. Khi cập nhật, admin sẽ xem xét lại yêu cầu.
        </p>
        @else
        <p class="text-sm text-gray-600 mb-3 bg-gray-50 p-2 rounded">
          ✓ Yêu cầu của bạn đã được duyệt. Bạn có thể cập nhật lịch nếu cần thiết.
        </p>
        @endif
      @endif
      
      @if(session('status'))<div class="bg-green-100 text-green-800 p-2 rounded mb-3">{{ session('status') }}</div>@endif
      
      <form method="post" action="{{ route('courses.enroll', $course->id) }}" class="space-y-4">
        @csrf
        
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">⏱️ Giờ bắt đầu:</label>
            <input type="time" name="start_time" required value="{{ old('start_time', $userEnrollment->start_time ?? '') }}" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent" />
            @error('start_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">⏱️ Giờ kết thúc:</label>
            <input type="time" name="end_time" required value="{{ old('end_time', $userEnrollment->end_time ?? '') }}" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent" />
            @error('end_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Các thứ có thể học trong tuần:</label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($weekdays as $day => $label)
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="preferred_days[]" value="{{ $day }}" 
                {{ in_array($day, $selectedDays) || (old('preferred_days') && in_array($day, old('preferred_days'))) ? 'checked' : '' }} 
                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
              <span class="text-sm text-gray-700 font-medium">{{ $label }}</span>
            </label>
            @endforeach
          </div>
          @error('preferred_days')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
        </div>
        
        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 rounded-lg transition">
          @if($userEnrollment)
            @if($userEnrollment->status === 'rejected')
              🔄 Gửi lại yêu cầu
            @elseif($userEnrollment->status === 'confirmed')
              ✏️ Cập nhật lịch
            @else
              ✏️ Cập nhật yêu cầu
            @endif
          @else
            📤 Gửi đăng ký
          @endif
        </button>
      </form>
    </div>
    @else
      <div class="mt-4 p-3 bg-yellow-50 rounded">ℹ️ Bạn cần đăng nhập bằng tài khoản học viên để đăng ký khóa học.</div>
    @endif

    @php
      $enrollment = $user ? \App\Models\Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->where('status', 'confirmed')->first() : null;
      $review = $enrollment ? \App\Models\Review::where('user_id', $user->id)->where('course_id', $course->id)->first() : null;
    @endphp

    @if($enrollment)
    <div class="mt-4 border-t pt-3">
      <h4 class="font-semibold mb-2">Đánh giá khóa học</h4>
      @if($review)
        <div class="bg-gray-50 p-3 rounded">
          <p><strong>Đánh giá của bạn:</strong> {{ str_repeat('⭐', $review->rating) }} ({{ $review->rating }}/5)</p>
          @if($review->comment)<p><strong>Nhận xét:</strong> {{ $review->comment }}</p>@endif
        </div>
      @else
        <form method="post" action="{{ route('courses.review', $course->id) }}" class="space-y-3">
          @csrf
          <div>
            <label class="block text-sm font-medium mb-1">Đánh giá (1-5 sao):</label>
            <select name="rating" required class="border rounded px-2 py-2 w-full">
              <option value="">Chọn số sao</option>
              <option value="5">⭐⭐⭐⭐⭐ (5 sao)</option>
              <option value="4">⭐⭐⭐⭐ (4 sao)</option>
              <option value="3">⭐⭐⭐ (3 sao)</option>
              <option value="2">⭐⭐ (2 sao)</option>
              <option value="1">⭐ (1 sao)</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Nhận xét (tùy chọn):</label>
            <textarea name="comment" rows="3" class="border rounded px-2 py-2 w-full" placeholder="Chia sẻ trải nghiệm học tập của bạn..."></textarea>
          </div>
          <button type="submit" class="btn bg-primary text-white rounded px-3 py-2">Gửi đánh giá</button>
        </form>
      @endif
    </div>
    @endif

    <div class="mt-4 border-t pt-3">
      <h4 class="font-semibold mb-2">Đánh giá từ học viên khác</h4>
      @php $reviews = \App\Models\Review::where('course_id', $course->id)->with('user')->get(); @endphp
      @if($reviews->isEmpty())
        <p class="text-gray-500">Chưa có đánh giá nào.</p>
      @else
        <div class="space-y-3">
          @foreach($reviews as $r)
            <div class="border rounded p-3">
              <div class="flex justify-between items-start">
                <div>
                  <strong>{{ $r->user->name }}</strong>
                  <div class="text-yellow-500">{{ str_repeat('⭐', $r->rating) }}</div>
                </div>
                <small class="text-gray-500">{{ $r->created_at->format('d/m/Y') }}</small>
              </div>
              @if($r->comment)<p class="mt-1">{{ $r->comment }}</p>@endif
            </div>
          @endforeach
        </div>
        <div class="mt-3 p-2 bg-gray-50 rounded">
          <strong>Đánh giá trung bình: {{ number_format($course->averageRating(), 1) }}/5 ⭐</strong>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection