<?php $__env->startSection('title', 'Kiem tra xung dot lich'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $candidate = $candidate ?? [];
    $teacherConflicts = $teacherConflicts ?? collect();
    $roomConflicts = $roomConflicts ?? collect();
    $studentConflicts = $studentConflicts ?? collect();
    $canCheck = (bool) ($candidate['ready'] ?? false);
    $hasConflicts = (bool) ($hasConflicts ?? false);
    $selectedDays = $candidate['days'] ?? [];
    $selectedCourseId = (int) ($filters['course_id'] ?? ($candidate['course']?->id ?? 0));
    $selectedClassRoomId = (int) ($filters['class_room_id'] ?? ($candidate['classRoom']?->id ?? 0));
    $selectedTeacherId = (int) ($candidate['teacher_id'] ?? ($filters['teacher_id'] ?? 0));
    $selectedRoomId = (int) ($candidate['room_id'] ?? ($filters['room_id'] ?? 0));
    $previewCourse = $candidate['previewCourse'] ?? new \App\Models\Course();
    $sourceLabel = $candidate['source_label'] ?? 'Nhập tay';
    $scheduleLabel = $candidate['schedule_label'] ?? $previewCourse->formattedSchedule();
    $teacherName = $candidate['teacher']?->name ?? 'Chưa chọn';
    $roomName = $candidate['room']?->name ?? 'Chưa chọn';
?>
<?php
    $showCleanupReport = $selectedCourseId === 0 && $selectedClassRoomId === 0;
?>

<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginal901bc0ef5589060c637099a6fd69b6fa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal901bc0ef5589060c637099a6fd69b6fa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.quan_tri.tieu_de_trang','data' => ['title' => 'Kiem tra xung dot lich']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('quan_tri.tieu_de_trang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Kiem tra xung dot lich']); ?>
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('admin.schedules.index')); ?>" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Quay lại lịch</a>
            <a href="<?php echo e(route('admin.schedules.queue')); ?>" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700 transition">Hàng chờ xếp lịch</a>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal901bc0ef5589060c637099a6fd69b6fa)): ?>
<?php $attributes = $__attributesOriginal901bc0ef5589060c637099a6fd69b6fa; ?>
<?php unset($__attributesOriginal901bc0ef5589060c637099a6fd69b6fa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal901bc0ef5589060c637099a6fd69b6fa)): ?>
<?php $component = $__componentOriginal901bc0ef5589060c637099a6fd69b6fa; ?>
<?php unset($__componentOriginal901bc0ef5589060c637099a6fd69b6fa); ?>
<?php endif; ?>

    <form method="get" action="<?php echo e(route('admin.schedules.conflicts')); ?>" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-5 lg:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Khóa học nguồn</label>
                <select name="course_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">-- Chọn khóa học --</option>
                    <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($course->id); ?>" <?php if($selectedCourseId === $course->id): echo 'selected'; endif; ?>>
                            <?php echo e($course->title); ?><?php echo e($course->formattedSchedule() ? ' — ' . $course->formattedSchedule() : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Lớp học hiện hành</label>
                <select name="class_room_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">-- Chọn lớp học --</option>
                    <?php $__currentLoopData = $classRooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classRoom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($classRoom->id); ?>" <?php if($selectedClassRoomId === $classRoom->id): echo 'selected'; endif; ?>>
                            <?php echo e($classRoom->displayName()); ?><?php echo e($classRoom->scheduleSummary() ? ' — ' . $classRoom->scheduleSummary() : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Giảng viên</label>
                <select name="teacher_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">-- Chọn giảng viên --</option>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($teacher->id); ?>" <?php if($selectedTeacherId === $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->displayName()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Phòng học</label>
                <select name="room_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">-- Chọn phòng học --</option>
                    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($room->id); ?>" <?php if($selectedRoomId === $room->id): echo 'selected'; endif; ?>>
                            <?php echo e($room->name); ?> (<?php echo e($room->code); ?>) — <?php echo e($room->capacity); ?> chỗ
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Ngày học</label>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
                    <?php $__currentLoopData = $dayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayValue => $dayLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50">
                            <input type="checkbox" name="day_of_week[]" value="<?php echo e($dayValue); ?>" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" <?php if(in_array($dayValue, $selectedDays, true)): echo 'checked'; endif; ?>>
                            <span><?php echo e($dayLabel); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Ngày bắt đầu</label>
                <input type="date" name="start_date" value="<?php echo e($candidate['start_date'] ?? ($filters['start_date'] ?? '')); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Ngày kết thúc</label>
                <input type="date" name="end_date" value="<?php echo e($candidate['end_date'] ?? ($filters['end_date'] ?? '')); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Giờ bắt đầu</label>
                <input type="time" name="start_time" value="<?php echo e($candidate['start_time'] ?? ($filters['start_time'] ?? '')); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Giờ kết thúc</label>
                <input type="time" name="end_time" value="<?php echo e($candidate['end_time'] ?? ($filters['end_time'] ?? '')); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5">
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.schedules.conflicts')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Đặt lại</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Kiểm tra</button>
            </div>
        </div>
    </form>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Nguồn dữ liệu</p>
            <p class="mt-2 text-lg font-semibold text-slate-900"><?php echo e($sourceLabel); ?></p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Lịch kiểm tra</p>
            <p class="mt-2 text-lg font-semibold text-slate-900"><?php echo e($scheduleLabel); ?></p>
            <p class="mt-2 text-sm text-slate-500"><?php echo e($teacherName); ?> • <?php echo e($roomName); ?></p>
        </div>
        <div class="rounded-3xl border <?php echo e($canCheck ? ($hasConflicts ? 'border-rose-200 bg-rose-50' : 'border-emerald-200 bg-emerald-50') : 'border-slate-200 bg-slate-50'); ?> p-5 shadow-sm">
            <p class="text-xs uppercase tracking-[0.2em] <?php echo e($canCheck ? ($hasConflicts ? 'text-rose-500' : 'text-emerald-600') : 'text-slate-400'); ?>">Kết luận</p>
            <p class="mt-2 text-lg font-semibold <?php echo e($canCheck ? ($hasConflicts ? 'text-rose-900' : 'text-emerald-900') : 'text-slate-900'); ?>">
                <?php if(! $canCheck): ?>
                    Chưa đủ dữ liệu
                <?php elseif($hasConflicts): ?>
                    Có xung đột
                <?php else: ?>
                    Không phát hiện xung đột
                <?php endif; ?>
            </p>
            <p class="mt-2 text-sm <?php echo e($canCheck ? ($hasConflicts ? 'text-rose-700' : 'text-emerald-700') : 'text-slate-500'); ?>">
                <?php if(! $canCheck): ?>
                    Cần ít nhất một giảng viên hoặc phòng học, ngày học, giờ học và khoảng ngày để chạy kiểm tra.
                <?php elseif($hasConflicts): ?>
                    Hệ thống đã tìm thấy lịch chồng khung giờ với dữ liệu đang chọn.
                <?php else: ?>
                    Cấu hình hiện tại chưa ghi nhận trùng giảng viên hoặc phòng học.
                <?php endif; ?>
            </p>
        </div>
    </section>

    <?php if(empty($filters['course_id']) && empty($filters['class_room_id']) && ($teacherConflicts->isNotEmpty() || $roomConflicts->isNotEmpty() || $studentConflicts->isNotEmpty())): ?>
        <section class="rounded-3xl border border-cyan-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-cyan-700">Ô sửa nhanh</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Mở thẳng màn chỉnh lịch</h2>
                    <p class="mt-2 text-sm text-slate-500">Bấm vào lớp đang trùng để vào đúng màn chỉnh thông tin và lịch học.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-rose-100 bg-rose-50/60 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-slate-900">Trùng giảng viên</h3>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-rose-700"><?php echo e($teacherConflicts->count()); ?> lớp</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $teacherConflicts->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conflictCourse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-rose-100">
                                <p class="text-sm font-semibold text-slate-900"><?php echo e($conflictCourse->title); ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?php echo e($conflictCourse->formattedSchedule()); ?></p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="<?php echo e(route('admin.course.show', $conflictCourse)); ?>" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">
                                        Sửa nhanh
                                    </a>
                                    <a href="<?php echo e(route('admin.schedules.courses.show', $conflictCourse)); ?>" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Mở chi tiết
                                    </a>
                                    <?php if($conflictClassRoom && $conflictClassRoom->enrolledCount() === 0): ?>
                                        <form method="POST" action="<?php echo e(route('admin.classes.delete', $conflictClassRoom)); ?>" onsubmit="return confirm('Xóa lớp này?')">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                Xóa lớp
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-slate-500">Không có lớp trùng giảng viên.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-slate-900">Trùng phòng hoặc trùng học viên</h3>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700"><?php echo e($roomConflicts->count() + $studentConflicts->count()); ?> nhóm</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $roomConflicts->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classRoom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-amber-100">
                                <p class="text-sm font-semibold text-slate-900"><?php echo e($classRoom->displayName()); ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?php echo e($classRoom->scheduleSummary()); ?></p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="<?php echo e($classRoom->course ? route('admin.course.show', $classRoom->course) : route('admin.classes.show', $classRoom)); ?>" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                                        Sửa nhanh
                                    </a>
                                    <a href="<?php echo e(route('admin.classes.show', $classRoom)); ?>" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Mở lớp
                                    </a>
                                    <?php if($classRoom->enrolledCount() === 0): ?>
                                        <form method="POST" action="<?php echo e(route('admin.classes.delete', $classRoom)); ?>" onsubmit="return confirm('Xóa lớp này?')">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                Xóa lớp
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php endif; ?>

                        <?php $__empty_1 = true; $__currentLoopData = $studentConflicts->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $studentConflict): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $__currentLoopData = collect($studentConflict['conflicts'] ?? [])->take(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-amber-100">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">
                                                <?php echo e($pair['first']['title']); ?> ↔ <?php echo e($pair['second']['title']); ?>

                                            </p>
                                            <p class="mt-1 text-xs text-slate-500"><?php echo e($pair['note']); ?></p>
                                        </div>
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">
                                            <?php echo e($studentConflict['student_count'] ?? 0); ?> học viên
                                        </span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <?php $__currentLoopData = collect($studentConflict['students'] ?? [])->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                                                <?php echo e($student['student_name']); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(($studentConflict['student_count'] ?? 0) > 3): ?>
                                            <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                                                +<?php echo e(($studentConflict['student_count'] ?? 0) - 3); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a href="<?php echo e($pair['first']['edit_url'] ?? $pair['first']['url']); ?>" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                                            Sửa nhanh
                                        </a>
                                        <a href="<?php echo e($pair['first']['url']); ?>" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Mở lớp
                                        </a>
                                        <?php if(!empty($pair['first']['delete_url'])): ?>
                                            <form method="POST" action="<?php echo e($pair['first']['delete_url']); ?>" onsubmit="return confirm('Xóa lớp này?')">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                    Xóa lớp
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <?php if($roomConflicts->isEmpty()): ?>
                                <p class="text-sm text-slate-500">Không có lớp trùng phòng hoặc dữ liệu học viên trùng lịch.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if($canCheck): ?>
        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Xung đột giảng viên</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Các lớp trùng người dạy</h2>
                    </div>
                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700"><?php echo e($teacherConflicts->count()); ?> lớp</span>
                </div>

                <?php if($teacherConflicts->isNotEmpty()): ?>
                    <div class="mt-6 space-y-4">
                        <?php $__currentLoopData = $teacherConflicts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conflictCourse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $conflictClassRoom = $conflictCourse->currentClassRoom();
                            ?>
                            <div class="rounded-2xl border border-rose-100 bg-rose-50/70 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-rose-500"><?php echo e($conflictCourse->subject?->category?->name ?? 'Chưa phân nhóm'); ?></p>
                                        <h3 class="mt-1 text-lg font-semibold text-slate-900"><?php echo e($conflictCourse->title); ?></h3>
                                        <p class="mt-1 text-sm text-slate-600"><?php echo e($conflictCourse->formattedSchedule()); ?></p>
                                    </div>
                                    <?php if (isset($component)) { $__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.quan_tri.huy_hieu','data' => ['type' => $conflictCourse->isPendingOpen() ? 'warning' : 'info','text' => $conflictCourse->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('quan_tri.huy_hieu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($conflictCourse->isPendingOpen() ? 'warning' : 'info'),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($conflictCourse->statusLabel())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb)): ?>
<?php $attributes = $__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb; ?>
<?php unset($__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb)): ?>
<?php $component = $__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb; ?>
<?php unset($__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb); ?>
<?php endif; ?>
                                </div>

                                <div class="mt-4 grid gap-3 md:grid-cols-2">
                                    <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700">
                                        <span class="text-slate-400">Giảng viên:</span> <?php echo e($conflictCourse->teacher?->displayName() ?? 'Chưa phân công'); ?>

                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700">
                                        <span class="text-slate-400">Lớp hiện hành:</span> <?php echo e($conflictClassRoom?->displayName() ?? 'Chưa có lớp'); ?>

                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="<?php echo e(route('admin.schedules.courses.show', $conflictCourse)); ?>" class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">Xem chi tiết</a>
                                    <a href="<?php echo e(route('admin.course.show', $conflictCourse)); ?>" class="inline-flex items-center justify-center rounded-full bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">Sửa nhanh</a>
                                    <?php if($conflictClassRoom): ?>
                                    <a href="<?php echo e(route('admin.classes.show', $conflictClassRoom)); ?>" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Mở lớp</a>
                                    <?php if($conflictClassRoom && $conflictClassRoom->enrolledCount() === 0): ?>
                                        <form method="POST" action="<?php echo e(route('admin.classes.delete', $conflictClassRoom)); ?>" onsubmit="return confirm('Xóa lớp này?')">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">Xóa lớp</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="mt-6 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-10 text-center text-sm text-emerald-700">
                        Không tìm thấy lớp nào trùng giảng viên theo khung đã chọn.
                    </div>
                <?php endif; ?>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Xung đột phòng học</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Các lớp trùng phòng</h2>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700"><?php echo e($roomConflicts->count()); ?> lớp</span>
                </div>

                <?php if($roomConflicts->isNotEmpty()): ?>
                    <div class="mt-6 space-y-4">
                        <?php $__currentLoopData = $roomConflicts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classRoom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-amber-600"><?php echo e($classRoom->subject?->category?->name ?? 'Chưa phân nhóm'); ?></p>
                                        <h3 class="mt-1 text-lg font-semibold text-slate-900"><?php echo e($classRoom->displayName()); ?></h3>
                                        <p class="mt-1 text-sm text-slate-600"><?php echo e($classRoom->scheduleSummary()); ?></p>
                                    </div>
                                    <?php if (isset($component)) { $__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.quan_tri.huy_hieu','data' => ['type' => match($classRoom->status) {
                                        \App\Models\ClassRoom::STATUS_OPEN => 'success',
                                        \App\Models\ClassRoom::STATUS_FULL => 'warning',
                                        \App\Models\ClassRoom::STATUS_COMPLETED => 'info',
                                        default => 'default',
                                    },'text' => $classRoom->statusLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('quan_tri.huy_hieu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($classRoom->status) {
                                        \App\Models\ClassRoom::STATUS_OPEN => 'success',
                                        \App\Models\ClassRoom::STATUS_FULL => 'warning',
                                        \App\Models\ClassRoom::STATUS_COMPLETED => 'info',
                                        default => 'default',
                                    }),'text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($classRoom->statusLabel())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb)): ?>
<?php $attributes = $__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb; ?>
<?php unset($__attributesOriginale49b452f9ea4b3d0a64b743d8b9520cb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb)): ?>
<?php $component = $__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb; ?>
<?php unset($__componentOriginale49b452f9ea4b3d0a64b743d8b9520cb); ?>
<?php endif; ?>
                                </div>

                                <div class="mt-4 grid gap-3 md:grid-cols-2">
                                    <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700">
                                        <span class="text-slate-400">Giảng viên:</span> <?php echo e($classRoom->teacher?->displayName() ?? 'Chưa phân công'); ?>

                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700">
                                        <span class="text-slate-400">Phòng:</span> <?php echo e($classRoom->room?->name ?? 'Chưa phân phòng'); ?>

                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="<?php echo e($classRoom->course ? route('admin.course.show', $classRoom->course) : route('admin.classes.show', $classRoom)); ?>" class="inline-flex items-center justify-center rounded-full bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">Sửa nhanh</a>
                                    <a href="<?php echo e(route('admin.classes.show', $classRoom)); ?>" class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">Mở lớp</a>
                                    <?php if($classRoom->enrolledCount() === 0): ?>
                                        <form method="POST" action="<?php echo e(route('admin.classes.delete', $classRoom)); ?>" onsubmit="return confirm('Xóa lớp này?')">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">Xóa lớp</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if($classRoom->course): ?>
                                        <a href="<?php echo e(route('admin.schedules.courses.show', $classRoom->course)); ?>" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Xem lịch khóa</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="mt-6 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-10 text-center text-sm text-emerald-700">
                        Không tìm thấy lớp nào trùng phòng theo khung đã chọn.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if($showCleanupReport): ?>
    <details class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <summary class="flex cursor-pointer list-none flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-xs uppercase tracking-[0.2em] text-cyan-700">Xung đột học viên toàn hệ thống</p>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Tự động rà soát</span>
                </div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Danh sách học viên cần rà soát</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Danh sách này được tổng hợp từ toàn bộ dữ liệu hiện có để hỗ trợ admin xem nhanh các trường hợp cần xử lý.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="rounded-2xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                    <?php echo e(number_format($studentConflictStudentCount ?? 0)); ?> học viên • <?php echo e(number_format($studentConflictPairCount ?? 0)); ?> cặp cần rà soát
                </div>
                <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">Bấm để mở</span>
            </div>
        </summary>

        <div class="mt-6">
            <?php if($studentConflicts->isNotEmpty()): ?>
                <div class="space-y-5">
                    <?php $__currentLoopData = $studentConflicts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $studentConflict): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="rounded-3xl border border-rose-100 bg-rose-50/60 p-5">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900"><?php echo e($studentConflict['student_summary'] ?? $studentConflict['student_name']); ?></h3>
                                    <p class="mt-1 text-sm text-slate-600"><?php echo e($studentConflict['student_count'] ?? 0); ?> học viên đang bị ảnh hưởng</p>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3 text-sm text-slate-700">
                                    <p><span class="text-slate-400">Số lớp liên quan:</span> <?php echo e($studentConflict['class_count'] ?? 0); ?></p>
                                    <p><span class="text-slate-400">Số học viên:</span> <?php echo e($studentConflict['student_count'] ?? 0); ?></p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                <?php $__currentLoopData = $studentConflict['conflicts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="rounded-2xl border border-rose-200 bg-white p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-500"><?php echo e($pair['day_label']); ?></p>
                                        <p class="mt-1 text-sm text-rose-700"><?php echo e($pair['note']); ?></p>

                                        <div class="mt-4 grid gap-3 lg:grid-cols-2">
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Lớp 1</p>
                                                <h4 class="mt-1 font-semibold text-slate-900"><?php echo e($pair['first']['title']); ?></h4>
                                                <p class="mt-1 text-sm text-slate-600"><?php echo e($pair['first']['schedule']); ?></p>
                                                <p class="mt-1 text-xs font-medium text-slate-500"><?php echo e($pair['first']['status']); ?></p>
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <a href="<?php echo e($pair['first']['url']); ?>" class="inline-flex rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700 hover:bg-cyan-100">
                                                        Xem lớp
                                                    </a>
                                                    <?php if(!empty($pair['first']['edit_url'])): ?>
                                                        <a href="<?php echo e($pair['first']['edit_url']); ?>" class="inline-flex rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                                            Sửa nhanh
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Lớp 2</p>
                                                <h4 class="mt-1 font-semibold text-slate-900"><?php echo e($pair['second']['title']); ?></h4>
                                                <p class="mt-1 text-sm text-slate-600"><?php echo e($pair['second']['schedule']); ?></p>
                                                <p class="mt-1 text-xs font-medium text-slate-500"><?php echo e($pair['second']['status']); ?></p>
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <a href="<?php echo e($pair['second']['url']); ?>" class="inline-flex rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700 hover:bg-cyan-100">
                                                        Xem lớp
                                                    </a>
                                                    <?php if(!empty($pair['second']['edit_url'])): ?>
                                                        <a href="<?php echo e($pair['second']['edit_url']); ?>" class="inline-flex rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                                            Sửa nhanh
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <?php $__currentLoopData = collect($studentConflict['students'] ?? [])->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                                                    <?php echo e($student['student_name']); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(($studentConflict['student_count'] ?? 0) > 4): ?>
                                                <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                                                    +<?php echo e(($studentConflict['student_count'] ?? 0) - 4); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="mt-6 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-10 text-center text-sm text-emerald-700">
                    Hiện chưa phát hiện học viên nào có lớp bị trùng lịch.
                </div>
            <?php endif; ?>
        </div>
    </details>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('bo_cuc.quan_tri', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu-main\resources\views/quan_tri/lich_hoc/xung_dot.blade.php ENDPATH**/ ?>